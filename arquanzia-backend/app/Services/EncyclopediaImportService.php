<?php

namespace App\Services;

use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class EncyclopediaImportService
{
    protected array $conflicts = [];

    protected array $imported = [];

    protected array $created = [];

    public function analyzeZip(string $zipPath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Impossible d\'ouvrir le fichier ZIP.');
        }

        $structure = [];
        $conflicts = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (str_starts_with($filename, '__MACOSX') || str_starts_with(basename($filename), '.')) {
                continue;
            }

            if (substr($filename, -1) === '/') {
                $path = rtrim($filename, '/');
                $structure[] = [
                    'type' => 'category',
                    'path' => $path,
                    'name' => basename($path),
                ];

                $existing = $this->findNodeByPath($path);
                if ($existing) {
                    $conflicts[] = [
                        'type' => 'category',
                        'path' => $path,
                        'existing_id' => $existing->id,
                    ];
                }
            } elseif (str_ends_with(strtolower($filename), '.md')) {
                $structure[] = [
                    'type' => 'article',
                    'path' => $filename,
                    'name' => pathinfo($filename, PATHINFO_FILENAME),
                ];

                $nodePath = $this->mdPathToNodePath($filename);
                $existing = $this->findNodeByPath($nodePath);
                if ($existing) {
                    $conflicts[] = [
                        'type' => 'article',
                        'path' => $filename,
                        'node_path' => $nodePath,
                        'existing_id' => $existing->id,
                    ];
                }
            }
        }

        $zip->close();

        return [
            'structure' => $structure,
            'conflicts' => $conflicts,
            'total_categories' => count(array_filter($structure, fn ($s) => $s['type'] === 'category')),
            'total_articles' => count(array_filter($structure, fn ($s) => $s['type'] === 'article')),
        ];
    }

    public function import(string $zipPath, string $conflictMode = 'overwrite', array $skipPaths = []): array
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Impossible d\'ouvrir le fichier ZIP.');
        }

        $this->conflicts = [];
        $this->imported = [];
        $this->created = [];

        DB::beginTransaction();

        try {
            $directories = [];
            $files = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                if (str_starts_with($filename, '__MACOSX') || str_starts_with(basename($filename), '.')) {
                    continue;
                }

                if (substr($filename, -1) === '/') {
                    $directories[] = rtrim($filename, '/');
                } elseif (str_ends_with(strtolower($filename), '.md')) {
                    $files[] = $filename;
                }
            }

            sort($directories);

            foreach ($directories as $dirPath) {
                $this->createCategoryFromPath($dirPath);
            }

            foreach ($files as $filePath) {
                if (in_array($filePath, $skipPaths)) {
                    continue;
                }

                $content = $zip->getFromName($filePath);
                $nodePath = $this->mdPathToNodePath($filePath);
                $existing = $this->findNodeByPath($nodePath);

                if ($existing) {
                    if ($conflictMode === 'skip') {
                        $this->conflicts[] = ['path' => $filePath, 'action' => 'skipped'];

                        continue;
                    }

                    $this->updateArticle($existing, $content);
                    $this->imported[] = ['path' => $filePath, 'action' => 'updated', 'node_id' => $existing->id];
                } else {
                    $node = $this->createArticleFromPath($filePath, $content);
                    $this->created[] = ['path' => $filePath, 'node_id' => $node->id];
                }
            }

            DB::commit();
            $zip->close();

            // L'index des mentions se reconstruit après coup, jamais pendant : un nœud qui en
            // cite un autre créé plus tard dans le même import ne pouvait pas le résoudre, et
            // l'index sortait incomplet sans que rien ne le signale.
            app(MentionIndexer::class)->rebuild();

            return [
                'success' => true,
                'imported' => $this->imported,
                'created' => $this->created,
                'conflicts' => $this->conflicts,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $zip->close();
            throw $e;
        }
    }

    protected function createCategoryFromPath(string $path): EncyclopediaNode
    {
        $parts = explode('/', $path);
        $parentId = null;
        $node = null;

        foreach ($parts as $index => $part) {
            $slug = Str::slug($part);
            $currentPath = implode('/', array_slice($parts, 0, $index + 1));

            $node = EncyclopediaNode::where('parent_id', $parentId)
                ->where('slug', $slug)
                ->first();

            if (! $node) {
                $maxOrder = EncyclopediaNode::where('parent_id', $parentId)->max('order_index') ?? 0;

                $node = EncyclopediaNode::create([
                    'parent_id' => $parentId,
                    'type' => 'category',
                    'slug' => $slug,
                    'title' => $this->slugToTitle($part),
                    'is_published' => true,
                    'order_index' => $maxOrder + 1,
                ]);

                $this->created[] = ['type' => 'category', 'path' => $currentPath, 'node_id' => $node->id];
            }

            $parentId = $node->id;
        }

        return $node;
    }

    protected function createArticleFromPath(string $filePath, string $content): EncyclopediaNode
    {
        $dir = dirname($filePath);
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $slug = Str::slug($filename);

        $parentId = null;
        if ($dir && $dir !== '.') {
            $parentNode = $this->createCategoryFromPath($dir);
            $parentId = $parentNode->id;
        }

        $maxOrder = EncyclopediaNode::where('parent_id', $parentId)->max('order_index') ?? 0;

        $parsed = $this->parseMarkdownContent($content);

        $node = EncyclopediaNode::create([
            'parent_id' => $parentId,
            'type' => 'article',
            'slug' => $slug,
            'title' => $parsed['title'] ?: $this->slugToTitle($filename),
            'is_published' => true,
            'teaser_md' => $parsed['teaser'],
            'order_index' => $maxOrder + 1,
        ]);

        EncyclopediaArticle::create([
            'node_id' => $node->id,
            'content_md' => $parsed['content'],
        ]);

        return $node;
    }

    protected function updateArticle(EncyclopediaNode $node, string $content): void
    {
        $parsed = $this->parseMarkdownContent($content);

        $node->update([
            'title' => $parsed['title'] ?: $node->title,
            'teaser_md' => $parsed['teaser'],
        ]);

        if ($node->article) {
            $node->article->update([
                'content_md' => $parsed['content'],
            ]);
        } else {
            EncyclopediaArticle::create([
                'node_id' => $node->id,
                'content_md' => $parsed['content'],
            ]);
        }
    }

    protected function parseMarkdownContent(string $content): array
    {
        $title = null;
        $teaser = null;
        $body = $content;

        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
            $body = preg_replace('/^#\s+.+\n*/m', '', $content, 1);
        }

        $paragraphs = preg_split('/\n\n+/', trim($body));
        if (! empty($paragraphs[0]) && strlen($paragraphs[0]) < 500) {
            $firstPara = $paragraphs[0];
            if (! str_starts_with($firstPara, '#') && ! str_starts_with($firstPara, '```')) {
                $teaser = $firstPara;
            }
        }

        return [
            'title' => $title,
            'teaser' => $teaser,
            'content' => trim($body),
        ];
    }

    protected function findNodeByPath(string $path): ?EncyclopediaNode
    {
        $parts = explode('/', $path);
        $parentId = null;

        foreach ($parts as $part) {
            $slug = Str::slug($part);
            $node = EncyclopediaNode::where('parent_id', $parentId)
                ->where('slug', $slug)
                ->first();

            if (! $node) {
                return null;
            }

            $parentId = $node->id;
        }

        return $node ?? null;
    }

    protected function mdPathToNodePath(string $mdPath): string
    {
        $path = preg_replace('/\.md$/i', '', $mdPath);

        return $path;
    }

    protected function slugToTitle(string $slug): string
    {
        $title = str_replace(['-', '_'], ' ', $slug);

        return ucfirst($title);
    }
}
