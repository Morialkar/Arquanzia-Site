<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookExportService
{
    protected string $exportPath = 'exports/books';

    public function generateEpub(Book $book): string
    {
        $chapters = $book->publishedChapters()->orderBy('order_index')->get();
        
        if ($chapters->isEmpty()) {
            throw new \Exception('Aucun chapitre publié à exporter.');
        }

        $filename = Str::slug($book->title) . '.epub';
        $filepath = $this->exportPath . '/' . $book->id . '/' . $filename;

        $epub = $this->buildEpubContent($book, $chapters);
        
        Storage::disk('local')->put($filepath, $epub);

        return $filepath;
    }

    public function generatePdf(Book $book): string
    {
        $chapters = $book->publishedChapters()->orderBy('order_index')->get();
        
        if ($chapters->isEmpty()) {
            throw new \Exception('Aucun chapitre publié à exporter.');
        }

        $filename = Str::slug($book->title) . '.pdf';
        $filepath = $this->exportPath . '/' . $book->id . '/' . $filename;

        $html = $this->buildPdfHtml($book, $chapters);
        $pdf = $this->htmlToPdf($html);
        
        Storage::disk('local')->put($filepath, $pdf);

        return $filepath;
    }

    public function generateChapterEpub(Chapter $chapter): string
    {
        $book = $chapter->book;
        $filename = Str::slug($book->title) . '-' . Str::slug($chapter->title) . '.epub';
        $filepath = $this->exportPath . '/chapters/' . $chapter->id . '/' . $filename;

        $epub = $this->buildChapterEpubContent($book, $chapter);
        Storage::disk('local')->put($filepath, $epub);

        return $filepath;
    }

    public function generateChapterPdf(Chapter $chapter): string
    {
        $book = $chapter->book;
        $filename = Str::slug($book->title) . '-' . Str::slug($chapter->title) . '.pdf';
        $filepath = $this->exportPath . '/chapters/' . $chapter->id . '/' . $filename;

        $html = $this->buildChapterPdfHtml($book, $chapter);
        $pdf = $this->htmlToPdf($html);
        Storage::disk('local')->put($filepath, $pdf);

        return $filepath;
    }

    public function getChapterExportPath(Chapter $chapter, string $format): ?string
    {
        $book = $chapter->book;
        $filename = Str::slug($book->title) . '-' . Str::slug($chapter->title) . '.' . $format;
        $filepath = $this->exportPath . '/chapters/' . $chapter->id . '/' . $filename;

        if (Storage::disk('local')->exists($filepath)) {
            return $filepath;
        }

        return $format === 'epub' 
            ? $this->generateChapterEpub($chapter)
            : $this->generateChapterPdf($chapter);
    }

    protected function buildChapterEpubContent(Book $book, Chapter $chapter): string
    {
        $uuid = $chapter->id;
        $title = htmlspecialchars($book->title . ' - ' . $chapter->title);
        $author = htmlspecialchars($book->author ?? 'Créations Sortilege');
        $date = now()->format('Y-m-d');

        $container = '<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
    <rootfiles>
        <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
    </rootfiles>
</container>';

        $chapterContent = $chapter->content_html ?? '';
        $chapterHtml = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
    <title>' . htmlspecialchars($chapter->title) . '</title>
    <style>
        body { font-family: Georgia, serif; line-height: 1.6; padding: 1em; }
        h1 { font-size: 1.5em; margin-bottom: 1em; }
        p { margin: 0; text-indent: 1.5em; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($chapter->title) . '</h1>
    ' . $chapterContent . '
</body>
</html>';

        $manifest = '<item id="chapter" href="chapter.xhtml" media-type="application/xhtml+xml"/>';
        $spine = '<itemref idref="chapter"/>';

        $opf = '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookId">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:identifier id="BookId">urn:uuid:' . $uuid . '</dc:identifier>
        <dc:title>' . $title . '</dc:title>
        <dc:creator>' . $author . '</dc:creator>
        <dc:language>fr</dc:language>
        <meta property="dcterms:modified">' . $date . 'T00:00:00Z</meta>
    </metadata>
    <manifest>
        ' . $manifest . '
    </manifest>
    <spine>
        ' . $spine . '
    </spine>
</package>';

        $tempFile = tempnam(sys_get_temp_dir(), 'epub');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->addFromString('META-INF/container.xml', $container);
        $zip->addFromString('OEBPS/content.opf', $opf);
        $zip->addFromString('OEBPS/chapter.xhtml', $chapterHtml);

        $zip->close();

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    protected function buildChapterPdfHtml(Book $book, Chapter $chapter): string
    {
        $title = htmlspecialchars($book->title);
        $chapterTitle = htmlspecialchars($chapter->title);
        $author = htmlspecialchars($book->author ?? 'Créations Sortilege');
        $chapterContent = $this->cleanHtmlForPdf($chapter->content_html ?? '');

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $title . ' - ' . $chapterTitle . '</title>
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: Georgia, "Times New Roman", serif; 
            font-size: 12pt; 
            line-height: 1.6;
            color: #1a1a1a;
        }
        .header {
            text-align: center;
            margin-bottom: 2em;
            padding-bottom: 1em;
            border-bottom: 1px solid #ccc;
        }
        .header h1 { font-size: 24pt; margin-bottom: 0.5em; }
        .header h2 { font-size: 18pt; color: #666; margin-bottom: 0.5em; }
        .header .author { font-size: 12pt; color: #888; }
        .content p { margin: 0 0 0.5em 0; text-indent: 1.5em; }
        .content p:first-child { text-indent: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . $title . '</h1>
        <h2>' . $chapterTitle . '</h2>
        <p class="author">' . $author . '</p>
    </div>
    <div class="content">' . $chapterContent . '</div>
</body>
</html>';
    }

    protected function buildEpubContent(Book $book, $chapters): string
    {
        $uuid = $book->id;
        $title = htmlspecialchars($book->title);
        $author = htmlspecialchars($book->author ?? 'Créations Sortilege');
        $date = now()->format('Y-m-d');

        $container = '<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
    <rootfiles>
        <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
    </rootfiles>
</container>';

        $chaptersXhtml = [];
        $manifestItems = [];
        $spineItems = [];
        
        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter' . ($index + 1);
            $chapterTitle = htmlspecialchars($chapter->title);
            $chapterContent = $this->markdownToXhtml($chapter->content_md);
            
            $chaptersXhtml[$chapterId] = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>' . $chapterTitle . '</title>
    <style>
        body { font-family: Georgia, serif; line-height: 1.6; padding: 1em; }
        h1 { font-size: 1.5em; margin-bottom: 1em; }
        p { margin-bottom: 0.8em; text-align: justify; }
    </style>
</head>
<body>
    <h1>' . $chapterTitle . '</h1>
    ' . $chapterContent . '
</body>
</html>';
            
            $manifestItems[] = '<item id="' . $chapterId . '" href="' . $chapterId . '.xhtml" media-type="application/xhtml+xml"/>';
            $spineItems[] = '<itemref idref="' . $chapterId . '"/>';
        }

        $tocItems = [];
        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter' . ($index + 1);
            $tocItems[] = '<li><a href="' . $chapterId . '.xhtml">' . htmlspecialchars($chapter->title) . '</a></li>';
        }

        $toc = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
    <title>Chapitres</title>
</head>
<body>
    <nav epub:type="toc">
        <h1>Chapitres</h1>
        <ol>' . implode("\n", $tocItems) . '</ol>
    </nav>
</body>
</html>';

        $contentOpf = '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookId">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:identifier id="BookId">urn:uuid:' . $uuid . '</dc:identifier>
        <dc:title>' . $title . '</dc:title>
        <dc:creator>' . $author . '</dc:creator>
        <dc:language>fr</dc:language>
        <dc:date>' . $date . '</dc:date>
        <meta property="dcterms:modified">' . now()->format('Y-m-d\TH:i:s\Z') . '</meta>
    </metadata>
    <manifest>
        <item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>
        ' . implode("\n        ", $manifestItems) . '
    </manifest>
    <spine>
        <itemref idref="nav"/>
        ' . implode("\n        ", $spineItems) . '
    </spine>
</package>';

        $zip = new \ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'epub');
        
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Impossible de créer le fichier EPUB.');
        }

        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->addFromString('META-INF/container.xml', $container);
        $zip->addFromString('OEBPS/content.opf', $contentOpf);
        $zip->addFromString('OEBPS/nav.xhtml', $toc);
        
        foreach ($chaptersXhtml as $id => $content) {
            $zip->addFromString('OEBPS/' . $id . '.xhtml', $content);
        }

        $zip->close();

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    protected function buildPdfHtml(Book $book, $chapters): string
    {
        $title = htmlspecialchars($book->title);
        $author = htmlspecialchars($book->author ?? 'Créations Sortilege');
        $year = now()->year;
        
        $coverHtml = '';
        if ($book->cover) {
            $coverPath = storage_path('app/media/original/' . $book->cover->filename);
            if (file_exists($coverPath)) {
                $coverData = base64_encode(file_get_contents($coverPath));
                $coverMime = $book->cover->mime_type ?? 'image/jpeg';
                $coverHtml = '<img src="data:' . $coverMime . ';base64,' . $coverData . '" style="max-width: 100%; max-height: 70%; object-fit: contain; margin-bottom: 2em;">';
            }
        }
        
        $chaptersHtml = '';
        foreach ($chapters as $chapter) {
            $chapterTitle = htmlspecialchars($chapter->title);
            $chapterContent = $this->cleanHtmlForPdf($chapter->content_html);
            
            $chaptersHtml .= '
            <div class="chapter" style="page-break-before: always;">
                <h2>' . $chapterTitle . '</h2>
                <div class="content">' . $chapterContent . '</div>
            </div>';
        }

        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . $title . '</title>
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: Georgia, "Times New Roman", serif; 
            font-size: 12pt; 
            line-height: 1.6;
            color: #1a1a1a;
        }
        .cover {
            text-align: center;
            padding-top: 10%;
            position: relative;
            min-height: 90%;
        }
        .cover h1 {
            font-size: 28pt;
            margin-bottom: 1em;
        }
        .cover .author {
            font-size: 14pt;
            color: #666;
        }
        .cover .copyright {
            position: absolute;
            bottom: 2cm;
            left: 0;
            right: 0;
            font-size: 10pt;
            color: #999;
        }
        .toc {
            page-break-before: always;
        }
        .toc h2 {
            font-size: 18pt;
            margin-bottom: 1em;
        }
        .toc ul {
            list-style: none;
            padding: 0;
        }
        .toc li {
            margin: 0.5em 0;
        }
        .chapter h2 {
            font-size: 20pt;
            margin-bottom: 1em;
            color: #0E3B2E;
        }
        .content p {
            text-align: justify;
            margin-bottom: 0;
            text-indent: 1.5em;
        }
        .content p:first-child {
            text-indent: 0;
        }
        .content br + br {
            display: none;
        }
        .content h3 {
            font-size: 14pt;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
    </style>
</head>
<body>
    <div class="cover">
        ' . $coverHtml . '
        <h1>' . $title . '</h1>
        <p class="author">' . $author . '</p>
        <p class="copyright">© Créations Sortilege ' . $year . '</p>
    </div>
    
    <div class="toc">
        <h2>Chapitres</h2>
        <ul>
            ' . collect($chapters)->map(fn($c) => '<li>' . htmlspecialchars($c->title) . '</li>')->implode("\n            ") . '
        </ul>
    </div>
    
    ' . $chaptersHtml . '
</body>
</html>';
    }

    protected function htmlToPdf(string $html): string
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            throw new \Exception('DomPDF non installé. Exécute: composer require dompdf/dompdf');
        }

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'defaultFont' => 'serif',
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

    protected function markdownToXhtml(string $markdown): string
    {
        $html = \App\Helpers\MarkdownHelper::render($markdown);
        $html = str_replace('<br>', '<br/>', $html);
        $html = str_replace('<hr>', '<hr/>', $html);
        $html = preg_replace('/<img([^>]*)(?<!\/)>/', '<img$1/>', $html);
        
        return $html;
    }

    protected function cleanHtmlForPdf(?string $html): string
    {
        if (!$html) {
            return '';
        }
        
        $html = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/', '</p><p>', $html);
        $html = preg_replace('/\n\s*\n/', '</p><p>', $html);
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        
        return $html;
    }

    public function getExportPath(Book $book, string $format): ?string
    {
        $filename = Str::slug($book->title) . '.' . $format;
        $filepath = $this->exportPath . '/' . $book->id . '/' . $filename;
        
        if (Storage::disk('local')->exists($filepath)) {
            return $filepath;
        }
        
        return null;
    }

    public function exportExists(Book $book, string $format): bool
    {
        return $this->getExportPath($book, $format) !== null;
    }
}
