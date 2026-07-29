<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function render(string $markdown): string
    {
        // Normalize line endings
        $markdown = str_replace("\r\n", "\n", $markdown);
        $markdown = str_replace("\r", "\n", $markdown);
        
        $lines = explode("\n", $markdown);
        $result = [];

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);
            
            // Skip empty lines but keep them for spacing
            if ($trimmed === '') {
                $result[] = '';
                continue;
            }
            
            // Remove lines that are ONLY * or ** (Obsidian closing artifacts)
            if ($trimmed === '*' || $trimmed === '**') {
                continue;
            }
            
            // Skip list items (start with * or - followed by space)
            if (preg_match('/^[\*\-]\s+/', $trimmed)) {
                $result[] = $line;
                continue;
            }

            // Auto-close bold **
            $boldCount = substr_count($line, '**');
            if ($boldCount % 2 !== 0) {
                $line .= '**';
            }

            // Auto-close italic * (after removing ** to count accurately)
            $withoutBold = str_replace('**', '', $line);
            $italicCount = substr_count($withoutBold, '*');
            if ($italicCount % 2 !== 0) {
                // Find all continuation lines (non-empty, no leading *)
                $j = $i + 1;
                while ($j < $count) {
                    $nextTrimmed = trim($lines[$j]);
                    if ($nextTrimmed === '' || $nextTrimmed === '*' || $nextTrimmed === '**' || preg_match('/^\*/', $nextTrimmed)) {
                        break;
                    }
                    $j++;
                }
                
                // Close * on the last line of the block
                if ($j > $i + 1) {
                    // There are continuation lines - close on the last one
                    $result[] = $line;
                    for ($k = $i + 1; $k < $j; $k++) {
                        if ($k === $j - 1) {
                            $result[] = $lines[$k] . '*';
                        } else {
                            $result[] = $lines[$k];
                        }
                    }
                    $i = $j - 1; // Skip processed lines
                    continue;
                } else {
                    $line .= '*';
                }
            }

            $result[] = $line;
        }

        $processed = implode("\n", $result);
        
        // Process [[wikilinks]] before markdown rendering
        $processed = self::processWikilinks($processed);
        
        \Log::debug('MarkdownHelper processed', ['has_wikilinks' => str_contains($processed, '[[')]);
        
        // Render markdown
        $html = \Illuminate\Support\Str::markdown($processed);
        
        // Preserve line breaks
        return nl2br($html);
    }

    protected static function processWikilinks(string $text): string
    {
        try {
            return preg_replace_callback('/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/', function ($matches) {
                $term = trim($matches[1]);
                $displayText = isset($matches[2]) ? trim($matches[2]) : $term;

                $escape = fn (?string $value) => $value !== null ? e($value) : null;

                try {
                    $target = \App\Models\Wikilink::resolveTarget($term);
                    
                    if ($target && isset($target['url'])) {
                        $url = $escape($target['url']);
                        $titleAttr = $escape($target['title'] ?? $displayText);
                        $teaserAttr = $target['teaser'] ? $escape(strip_tags($target['teaser'])) : null;
                        $displayEscaped = $escape($displayText);

                        $dataAttrs = ' data-wikilink-term="' . ($titleAttr ?? '') . '"';
                        if ($teaserAttr) {
                            $dataAttrs .= ' data-wikilink-teaser="' . $teaserAttr . '"';
                        }

                        return sprintf(
                            '<a href="%s" class="wikilink-resolved"%s>%s</a>',
                            $url,
                            $dataAttrs,
                            $displayEscaped
                        );
                    }
                } catch (\Throwable $e) {
                }
                
                return $escape($displayText);
            }, $text);
        } catch (\Exception $e) {
            return preg_replace('/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/', '$1', $text);
        }
    }
}
