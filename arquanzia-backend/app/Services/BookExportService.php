<?php

namespace App\Services;

use App\Helpers\MarkdownHelper;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookExportService
{
    protected string $exportPath = 'exports/books';

    /**
     * Export a book or chapter to PDF/EPUB.
     *
     * @param  Book|Chapter  $target  Book or Chapter to export
     * @param  string  $format  'pdf' or 'epub'
     * @param  array  $options  Font/size options: ['font' => 'standard'|'dyslexic', 'size' => 14-26]
     * @return array{filename: string, mime: string, content: string}
     */
    public function export(Book|Chapter $target, string $format, array $options = []): array
    {
        $format = strtolower($format);
        if (! in_array($format, ['pdf', 'epub', 'edition'], true)) {
            throw new \InvalidArgumentException('Format invalide: '.$format);
        }

        $options = $this->normalizeOptions($options);

        if ($target instanceof Chapter) {
            $book = $target->book;
            $chapters = collect([$target]);
            $isSingleChapter = true;
        } else {
            $book = $target;
            $chapters = Chapter::where('book_id', $book->id)
                ->where('is_published', 1)
                ->orderBy('order_index')
                ->get();
            $isSingleChapter = false;

            if ($chapters->isEmpty()) {
                throw new \Exception('Aucun chapitre publié à exporter.');
            }
        }

        if ($format === 'edition') {
            $content = $this->buildEditionPdf($book, $chapters, $options);
            $filename = $this->buildFilename($book, null, 'pdf', 'edition');
            $mime = 'application/pdf';
        } elseif ($format === 'pdf') {
            $content = $this->buildPdf($book, $chapters, $options, $isSingleChapter);
            $filename = $this->buildFilename($book, $isSingleChapter ? $chapters->first() : null, 'pdf');
            $mime = 'application/pdf';
        } else {
            $content = $this->buildEpub($book, $chapters, $options, $isSingleChapter);
            $filename = $this->buildFilename($book, $isSingleChapter ? $chapters->first() : null, 'epub');
            $mime = 'application/epub+zip';
        }

        return [
            'filename' => $filename,
            'mime' => $mime,
            'content' => $content,
        ];
    }

    /**
     * Legacy method for generating book EPUB (for backward compatibility).
     */
    public function generateEpub(Book $book): string
    {
        $result = $this->export($book, 'epub');
        $filepath = $this->exportPath.'/'.$book->id.'/'.$result['filename'];
        Storage::disk('local')->put($filepath, $result['content']);

        return $filepath;
    }

    /**
     * Legacy method for generating book PDF (for backward compatibility).
     */
    public function generatePdf(Book $book): string
    {
        $result = $this->export($book, 'pdf');
        $filepath = $this->exportPath.'/'.$book->id.'/'.$result['filename'];
        Storage::disk('local')->put($filepath, $result['content']);

        return $filepath;
    }

    public function getExportPath(Book $book, string $format): ?string
    {
        $filename = Str::slug($book->title).'.'.$format;
        $filepath = $this->exportPath.'/'.$book->id.'/'.$filename;

        if (Storage::disk('local')->exists($filepath)) {
            return $filepath;
        }

        return null;
    }

    public function exportExists(Book $book, string $format): bool
    {
        return $this->getExportPath($book, $format) !== null;
    }

    protected function normalizeOptions(array $options): array
    {
        $font = in_array($options['font'] ?? 'standard', ['standard', 'dyslexic'], true)
            ? $options['font']
            : 'standard';

        $size = (int) ($options['size'] ?? 18);
        $size = max(14, min(26, $size));

        return [
            'font' => $font,
            'size' => $size,
        ];
    }

    protected function buildFilename(Book $book, ?Chapter $chapter, string $format, string $suffix = ''): string
    {
        if ($chapter) {
            return Str::slug($book->title).'-'.Str::slug($chapter->title).'.'.$format;
        }
        $base = Str::slug($book->title);

        return $suffix ? "{$base}-{$suffix}.{$format}" : "{$base}.{$format}";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF Generation
    // ─────────────────────────────────────────────────────────────────────────

    protected function buildPdf(Book $book, Collection $chapters, array $options, bool $isSingleChapter): string
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            throw new \RuntimeException('DomPDF non installé. Exécutez composer require dompdf/dompdf');
        }

        $html = $isSingleChapter
            ? $this->buildSingleChapterPdfHtml($book, $chapters->first(), $options)
            : $this->buildFullBookPdfHtml($book, $chapters, $options);

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function buildSingleChapterPdfHtml(Book $book, Chapter $chapter, array $options): string
    {
        [$fontFaceCss, $fontAlias] = $this->buildPdfFontFaceCss($options['font']);
        $fontFamily = $this->buildFontFamilyCss($options['font'], $fontAlias);
        $fontSize = $options['size'];
        $fontSizeH1 = $this->calcFontSize($fontSize, 8);
        $fontSizeH2 = $this->calcFontSize($fontSize, 2);
        $fontSizeSmall = $this->calcFontSize($fontSize, -4);

        $bookTitle = e($book->title);
        $chapterTitle = e($chapter->title);
        $author = e($book->author ?? 'Créations Sortilege');
        $contentHtml = $this->getChapterContentHtml($chapter);

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{$bookTitle} - {$chapterTitle}</title>
    <style>
        @page { margin: 2cm; }
        {$fontFaceCss}
        body {
            font-family: {$fontFamily};
            font-size: {$fontSize}px;
            line-height: 1.7;
            color: #111111;
        }
        .header {
            text-align: center;
            margin-bottom: 1.5em;
            padding-bottom: 1em;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .header, .header * { font-family: inherit !important; }
        .header h1 { font-size: {$fontSizeH1}px; margin: 0 0 0.3em 0; font-weight: 700; }
        .header h2 { font-size: {$fontSizeH2}px; margin: 0 0 0.2em 0; color: #555; font-weight: 600; }
        .header p { margin: 0; font-size: {$fontSizeSmall}px; color: #777; font-weight: 400; }
        .content h1, .content h2, .content h3, .content h4,
        .content p, .content ul, .content ol,
        .content em, .content strong, .content span { font-family: inherit !important; }
        .content p { margin: 0 0 0.4em 0; text-indent: 1.5em; }
        .content p:first-child { text-indent: 0; }
        .content ul, .content ol { margin: 0 0 0.6em 1.2em; }
        .content br { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$bookTitle}</h1>
        <h2>{$chapterTitle}</h2>
        <p>{$author}</p>
    </div>
    <div class="content">{$contentHtml}</div>
</body>
</html>
HTML;
    }

    protected function buildFullBookPdfHtml(Book $book, Collection $chapters, array $options): string
    {
        [$fontFaceCss, $fontAlias] = $this->buildPdfFontFaceCss($options['font']);
        $fontFamily = $this->buildFontFamilyCss($options['font'], $fontAlias);
        $fontSize = $options['size'];

        $title = e($book->title);
        $author = e($book->author ?? 'Créations Sortilege');
        $year = now()->year;

        $coverHtml = $this->buildCoverImageHtml($book);

        $tocItems = $chapters->map(fn ($c) => '<li>'.e($c->title).'</li>')->implode("\n            ");

        $chaptersHtml = '';
        foreach ($chapters as $chapter) {
            $chapterTitle = e($chapter->title);
            $chapterContent = $this->getChapterContentHtml($chapter);
            $chaptersHtml .= <<<HTML
            <div class="chapter" style="page-break-before: always;">
                <h2>{$chapterTitle}</h2>
                <div class="content">{$chapterContent}</div>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        @page { margin: 2cm; }
        {$fontFaceCss}
        body {
            font-family: {$fontFamily};
            font-size: {$fontSize}px;
            line-height: 1.7;
            color: #1a1a1a;
        }
        .cover {
            text-align: center;
            padding-top: 10%;
            position: relative;
            min-height: 90%;
        }
        .cover h1 { font-size: 28pt; margin-bottom: 1em; }
        .cover .author { font-size: 14pt; color: #666; }
        .cover .copyright {
            position: absolute;
            bottom: 2cm;
            left: 0;
            right: 0;
            font-size: 10pt;
            color: #999;
        }
        .toc { page-break-before: always; }
        .toc h2 { font-size: 18pt; margin-bottom: 1em; }
        .toc ul { list-style: none; padding: 0; }
        .toc li { margin: 0.5em 0; }
        .chapter h2 { font-size: 20pt; margin-bottom: 1em; color: #0E3B2E; }
        .content p { text-align: justify; margin: 0 0 0.4em 0; text-indent: 1.5em; }
        .content p:first-child { text-indent: 0; }
        .content br { display: none; }
        .content h3 { font-size: 14pt; margin-top: 1.5em; margin-bottom: 0.5em; }
    </style>
</head>
<body>
    <div class="cover">
        {$coverHtml}
        <h1>{$title}</h1>
        <p class="author">{$author}</p>
        <p class="copyright">© Créations Sortilege {$year}</p>
    </div>

    <div class="toc">
        <h2>Chapitres</h2>
        <ul>
            {$tocItems}
        </ul>
    </div>

    {$chaptersHtml}
</body>
</html>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Edition PDF (half-letter booklet for printing)
    // ─────────────────────────────────────────────────────────────────────────

    protected function buildEditionPdf(Book $book, Collection $chapters, array $options): string
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            throw new \RuntimeException('DomPDF non installé.');
        }
        if (! class_exists(\setasign\Fpdi\Fpdi::class)) {
            throw new \RuntimeException('FPDI non installé. Exécutez composer require setasign/fpdi setasign/fpdf');
        }

        // Step 1: Generate half-letter content pages with DomPDF
        $contentPdf = $this->buildEditionContentPages($book, $chapters);

        // Step 2: Impose pages as booklet on 8.5x11 landscape sheets
        return $this->imposeBooklet($contentPdf);
    }

    protected function buildEditionContentPages(Book $book, Collection $chapters): string
    {
        $title = e($book->title);
        $author = e($book->author ?? 'Créations Sortilege');
        $year = now()->year;
        $coverHtml = $this->buildCoverImageHtml($book);

        $tocItems = $chapters->map(fn ($c) => '<li>'.e($c->title).'</li>')->implode("\n");

        $chaptersHtml = '';
        foreach ($chapters as $chapter) {
            $chapterTitle = e($chapter->title);
            $chapterContent = $this->getChapterContentHtml($chapter);
            $chaptersHtml .= <<<HTML
            <div class="chapter" style="page-break-before: always;">
                <h2>{$chapterTitle}</h2>
                <div class="content">{$chapterContent}</div>
            </div>
HTML;
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{$title} - Édition</title>
    <style>
        @page {
            size: 5.5in 8.5in;
            margin: 1.5cm 1.5cm 2cm 1.5cm;
        }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
        }
        .cover {
            text-align: center;
            padding-top: 20%;
            position: relative;
            min-height: 85%;
        }
        .cover img { max-width: 60%; max-height: 40%; object-fit: contain; margin-bottom: 1.5em; }
        .cover h1 { font-size: 20pt; margin-bottom: 0.5em; }
        .cover .author { font-size: 11pt; color: #444; }
        .cover .copyright {
            position: absolute;
            bottom: 1cm;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #999;
        }
        .toc { page-break-before: always; }
        .toc h2 { font-size: 14pt; margin-bottom: 0.8em; border-bottom: 0.5pt solid #ccc; padding-bottom: 0.3em; }
        .toc ul { list-style: none; padding: 0; }
        .toc li { margin: 0.3em 0; font-size: 10pt; }
        .chapter h2 {
            font-size: 16pt;
            margin-bottom: 0.8em;
            color: #0E3B2E;
            border-bottom: 0.5pt solid #0E3B2E;
            padding-bottom: 0.3em;
        }
        .content p { text-align: justify; margin: 0 0 0.3em 0; text-indent: 1.2em; font-size: 10pt; }
        .content p:first-child { text-indent: 0; }
        .content br { display: none; }
        .content h3 { font-size: 12pt; margin-top: 1em; margin-bottom: 0.4em; }
    </style>
</head>
<body>
    <div class="cover">
        {$coverHtml}
        <h1>{$title}</h1>
        <p class="author">{$author}</p>
        <p class="copyright">© Créations Sortilege {$year}</p>
    </div>

    <div class="toc">
        <h2>Table des matières</h2>
        <ul>
            {$tocItems}
        </ul>
    </div>

    {$chaptersHtml}
</body>
</html>
HTML;

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 396, 612], 'portrait'); // 5.5" x 8.5" in points
        $dompdf->render();

        return $dompdf->output();
    }

    protected function imposeBooklet(string $contentPdf): string
    {
        // Write content PDF to temp file for FPDI to read
        $srcFile = tempnam(sys_get_temp_dir(), 'booklet_src');
        file_put_contents($srcFile, $contentPdf);

        try {
            // Count pages in the source PDF
            $counter = new \setasign\Fpdi\Fpdi;
            $totalPages = $counter->setSourceFile($srcFile);

            // Pad to multiple of 4
            $padded = $totalPages;
            if ($padded % 4 !== 0) {
                $padded += 4 - ($padded % 4);
            }

            // Build booklet page order
            // Each physical sheet has a front and back, each with 2 book pages
            // When folded, pages read in order from front to back
            $sheets = [];
            for ($i = 0; $i < $padded / 4; $i++) {
                // Front of sheet: [last - 2*i, first + 2*i + 1] (left, right)
                // Back of sheet:  [first + 2*i + 2, last - 2*i - 1] (left, right)
                $sheets[] = [$padded - (2 * $i), 1 + (2 * $i)];           // front
                $sheets[] = [2 + (2 * $i), $padded - 1 - (2 * $i)];       // back
            }

            $booklet = new \setasign\Fpdi\Fpdi;
            $booklet->setSourceFile($srcFile);

            foreach ($sheets as [$leftPage, $rightPage]) {
                // Add a landscape 8.5x11 page (279.4mm x 215.9mm)
                $booklet->AddPage('L', [215.9, 279.4]);

                // Place left page (5.5" x 8.5" = 139.7mm x 215.9mm)
                if ($leftPage >= 1 && $leftPage <= $totalPages) {
                    $tplLeft = $booklet->importPage($leftPage);
                    $booklet->useTemplate($tplLeft, 0, 0, 139.7, 215.9);
                }

                // Place right page
                if ($rightPage >= 1 && $rightPage <= $totalPages) {
                    $tplRight = $booklet->importPage($rightPage);
                    $booklet->useTemplate($tplRight, 139.7, 0, 139.7, 215.9);
                }
            }

            return $booklet->Output('S');
        } finally {
            @unlink($srcFile);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EPUB Generation
    // ─────────────────────────────────────────────────────────────────────────

    protected function buildEpub(Book $book, Collection $chapters, array $options, bool $isSingleChapter): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'epub');
        $zip = new \ZipArchive;

        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer le fichier EPUB.');
        }

        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->addFromString('META-INF/container.xml', $this->buildEpubContainer());

        // Add fonts if dyslexic
        $fontFiles = $options['font'] === 'dyslexic' ? $this->getDyslexicFontFiles() : [];
        foreach ($fontFiles as $filename => $path) {
            $zip->addFile($path, 'OEBPS/fonts/'.$filename);
        }

        // Add styles
        $styles = $this->buildEpubStyles($options, array_keys($fontFiles));
        $zip->addFromString('OEBPS/styles.css', $styles);

        // Build manifest and spine items
        $manifestItems = [
            '<item id="style" href="styles.css" media-type="text/css"/>',
        ];
        $spineItems = [];

        foreach (array_keys($fontFiles) as $filename) {
            $manifestItems[] = sprintf(
                '<item id="%s" href="fonts/%s" media-type="application/vnd.ms-opentype"/>',
                Str::slug($filename, '_'),
                $filename
            );
        }

        // Add chapters
        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter'.($index + 1);
            $chapterXhtml = $this->buildEpubChapterXhtml($book->title, $chapter->title, $chapter->content_md ?? '');
            $zip->addFromString('OEBPS/'.$chapterId.'.xhtml', $chapterXhtml);

            $manifestItems[] = '<item id="'.$chapterId.'" href="'.$chapterId.'.xhtml" media-type="application/xhtml+xml"/>';
            $spineItems[] = '<itemref idref="'.$chapterId.'"/>';
        }

        // Add TOC for multi-chapter books
        if (! $isSingleChapter) {
            $navXhtml = $this->buildEpubNav($chapters);
            $zip->addFromString('OEBPS/nav.xhtml', $navXhtml);
            $manifestItems[] = '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>';
            array_unshift($spineItems, '<itemref idref="nav"/>');
        }

        // Build content.opf
        $uuid = $isSingleChapter ? $chapters->first()->id : $book->id;
        $title = $isSingleChapter
            ? e($book->title).' - '.e($chapters->first()->title)
            : e($book->title);
        $contentOpf = $this->buildEpubContentOpf($uuid, $title, $book->author, $manifestItems, $spineItems);
        $zip->addFromString('OEBPS/content.opf', $contentOpf);

        $zip->close();

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    protected function buildEpubContainer(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
    <rootfiles>
        <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
    </rootfiles>
</container>
XML;
    }

    protected function buildEpubContentOpf(string $uuid, string $title, ?string $author, array $manifestItems, array $spineItems): string
    {
        $author = htmlspecialchars($author ?? 'Créations Sortilege', ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $title = htmlspecialchars($title, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $date = now()->format('Y-m-d\TH:i:s\Z');
        $manifest = implode("\n        ", $manifestItems);
        $spine = implode("\n        ", $spineItems);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookId">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:identifier id="BookId">urn:uuid:{$uuid}</dc:identifier>
        <dc:title>{$title}</dc:title>
        <dc:creator>{$author}</dc:creator>
        <dc:language>fr</dc:language>
        <meta property="dcterms:modified">{$date}</meta>
    </metadata>
    <manifest>
        {$manifest}
    </manifest>
    <spine>
        {$spine}
    </spine>
</package>
XML;
    }

    protected function buildEpubNav(Collection $chapters): string
    {
        $tocItems = '';
        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter'.($index + 1);
            $title = htmlspecialchars($chapter->title, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $tocItems .= "<li><a href=\"{$chapterId}.xhtml\">{$title}</a></li>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
    <title>Chapitres</title>
    <link rel="stylesheet" type="text/css" href="styles.css"/>
</head>
<body>
    <nav epub:type="toc">
        <h1>Chapitres</h1>
        <ol>
            {$tocItems}
        </ol>
    </nav>
</body>
</html>
XML;
    }

    protected function buildEpubChapterXhtml(string $bookTitle, string $chapterTitle, string $markdown): string
    {
        $contentHtml = MarkdownHelper::render($markdown);
        $contentHtml = $this->cleanContentForExport($contentHtml);
        $contentHtml = $this->toXhtml($contentHtml);

        $bookTitle = htmlspecialchars($bookTitle, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $chapterTitle = htmlspecialchars($chapterTitle, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
    <meta charset="UTF-8"/>
    <title>{$bookTitle} - {$chapterTitle}</title>
    <link rel="stylesheet" type="text/css" href="styles.css"/>
</head>
<body>
    <h1>{$chapterTitle}</h1>
    {$contentHtml}
</body>
</html>
XML;
    }

    protected function buildEpubStyles(array $options, array $fontFilenames): string
    {
        $fontFamily = $options['font'] === 'dyslexic'
            ? "'OpenDyslexic','Inter',sans-serif"
            : "'Inter','Georgia','serif'";

        $css = "@namespace epub 'http://www.idpf.org/2007/ops';\n";

        foreach ($fontFilenames as $filename) {
            $style = str_contains(strtolower($filename), 'italic') ? 'italic' : 'normal';
            $css .= sprintf(
                "@font-face { font-family: 'OpenDyslexic'; font-style: %s; font-weight: 400; src: url('fonts/%s'); }\n",
                $style,
                $filename
            );
        }

        $css .= sprintf(
            "body { font-family: %s; font-size: %dpx; line-height: 1.7; color: #1a1a1a; margin: 0; padding: 1em; }\n",
            $fontFamily,
            $options['size']
        );
        $css .= "p { margin: 0 0 0.4em 0; text-indent: 1.5em; }\n";
        $css .= "p:first-child { text-indent: 0; }\n";
        $css .= "h1, h2, h3 { margin: 1.2em 0 0.4em 0; }\n";

        return $css;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function getChapterContentHtml(Chapter $chapter): string
    {
        $html = '';

        if ($chapter->content_html) {
            $html = $chapter->content_html;
        } elseif ($chapter->content_md) {
            $html = MarkdownHelper::render($chapter->content_md);
        }

        return $this->cleanContentForExport($html);
    }

    protected function cleanContentForExport(string $html): string
    {
        if (! $html) {
            return '';
        }

        // Strip wikilink <a> tags, keep display text only
        $html = preg_replace('/<a[^>]*class="wikilink-resolved"[^>]*>(.*?)<\/a>/s', '$1', $html);

        // Remove any remaining [[wikilinks]] or [[term|display]]
        $html = preg_replace('/\[\[([^\]|]+)\|([^\]]+)\]\]/', '$2', $html);
        $html = preg_replace('/\[\[([^\]]+)\]\]/', '$1', $html);

        // Collapse excessive <br> tags into paragraph breaks
        $html = preg_replace('/(<br\s*\/?>[\s]*){3,}/', '</p><p>', $html);
        $html = preg_replace('/(<br\s*\/?>[\s]*){2}/', '</p><p>', $html);

        // Remove empty paragraphs
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);

        // Wrap loose text in paragraphs if not already
        if (! str_contains($html, '<p>') && ! str_contains($html, '<h')) {
            $html = '<p>'.$html.'</p>';
        }

        return trim($html);
    }

    protected function toXhtml(string $html): string
    {
        $html = str_replace('<br>', '<br/>', $html);
        $html = str_replace('<hr>', '<hr/>', $html);
        $html = preg_replace('/<img([^>]*)(?<!\/)>(?!\s*<\/img>)/i', '<img$1/>', $html);

        return $html;
    }

    protected function buildCoverImageHtml(Book $book): string
    {
        if (! $book->cover) {
            return '';
        }

        $coverPath = storage_path('app/media/original/'.$book->cover->filename);
        if (! file_exists($coverPath)) {
            return '';
        }

        $coverData = base64_encode(file_get_contents($coverPath));
        $coverMime = $book->cover->mime_type ?? 'image/jpeg';

        return '<img src="data:'.$coverMime.';base64,'.$coverData.'" style="max-width: 100%; max-height: 70%; object-fit: contain; margin-bottom: 2em;">';
    }

    protected function calcFontSize(int $base, int $delta): int
    {
        return max(12, $base + $delta);
    }

    protected function buildFontFamilyCss(string $font, string $fontAlias): string
    {
        return $font === 'dyslexic'
            ? sprintf("'%s', sans-serif", $fontAlias)
            : sprintf("'%s','Georgia','serif'", $fontAlias);
    }

    protected function buildPdfFontFaceCss(string $font): array
    {
        $isDyslexic = $font === 'dyslexic';
        $family = $isDyslexic ? 'OpenDyslexicPDF' : 'InterPDF';
        $faces = $isDyslexic ? $this->getDyslexicFontFaces() : $this->getInterFontFaces();
        $css = '';

        foreach ($faces as $face) {
            if (! is_file($face['path'])) {
                continue;
            }

            $data = base64_encode(file_get_contents($face['path']));
            $css .= sprintf(
                "@font-face { font-family: '%s'; font-style: %s; font-weight: %d; src: url('data:font/%s;base64,%s') format('%s'); }\n",
                $family,
                $face['style'],
                $face['weight'],
                $face['extension'],
                $data,
                $face['format']
            );
        }

        return [$css, $family];
    }

    protected function getDyslexicFontFaces(): array
    {
        $basePath = public_path('fonts/opendyslexic');

        return array_filter([
            [
                'path' => $basePath.'/OpenDyslexic-Regular.otf',
                'style' => 'normal',
                'weight' => 400,
                'format' => 'opentype',
                'extension' => 'otf',
            ],
            [
                'path' => $basePath.'/OpenDyslexic-Bold.otf',
                'style' => 'normal',
                'weight' => 700,
                'format' => 'opentype',
                'extension' => 'otf',
            ],
            [
                'path' => $basePath.'/OpenDyslexic-Italic.otf',
                'style' => 'italic',
                'weight' => 400,
                'format' => 'opentype',
                'extension' => 'otf',
            ],
            [
                'path' => $basePath.'/OpenDyslexic-BoldItalic.otf',
                'style' => 'italic',
                'weight' => 700,
                'format' => 'opentype',
                'extension' => 'otf',
            ],
        ], fn ($face) => is_file($face['path']));
    }

    protected function getDyslexicFontFiles(): array
    {
        $basePath = public_path('fonts/opendyslexic');

        return array_filter([
            'OpenDyslexic-Regular.ttf' => $basePath.'/OpenDyslexic-Regular.ttf',
            'OpenDyslexic-Bold.ttf' => $basePath.'/OpenDyslexic-Bold.ttf',
            'OpenDyslexic-Italic.ttf' => $basePath.'/OpenDyslexic-Italic.ttf',
            'OpenDyslexic-BoldItalic.ttf' => $basePath.'/OpenDyslexic-BoldItalic.ttf',
        ], fn ($path) => is_file($path));
    }

    protected function getInterFontFaces(): array
    {
        $basePath = public_path('fonts/inter');

        return array_filter([
            [
                'path' => $basePath.'/Inter-Regular.ttf',
                'style' => 'normal',
                'weight' => 400,
                'format' => 'truetype',
                'extension' => 'ttf',
            ],
            [
                'path' => $basePath.'/Inter-Bold.ttf',
                'style' => 'normal',
                'weight' => 700,
                'format' => 'truetype',
                'extension' => 'ttf',
            ],
            [
                'path' => $basePath.'/Inter-Italic.ttf',
                'style' => 'italic',
                'weight' => 400,
                'format' => 'truetype',
                'extension' => 'ttf',
            ],
        ], fn ($face) => is_file($face['path']));
    }
}
