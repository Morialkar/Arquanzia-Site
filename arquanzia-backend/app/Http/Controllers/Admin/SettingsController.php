<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingsController extends Controller
{
    public function index(): View
    {
        $logoPath = SiteSetting::getLogo();
        $siteName = SiteSetting::getSiteName();

        return view('admin.settings.index', [
            'logoPath' => $logoPath,
            'siteName' => $siteName,
        ]);
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => 'required|file|mimes:svg,png,jpg,jpeg,webp|max:2048',
        ]);

        $oldLogo = SiteSetting::getLogo();
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        $file = $request->file('logo');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if ($extension === 'svg') {
            $filename = 'logos/logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.svg';
            
            if (!is_dir(storage_path('app/public/logos'))) {
                mkdir(storage_path('app/public/logos'), 0755, true);
            }
            
            $file->move(storage_path('app/public/logos'), basename($filename));
            $path = $filename;
        } else {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->trim();
            
            $filename = 'logos/logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $fullPath = storage_path('app/public/' . $filename);
            
            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            
            $image->save($fullPath);
            $path = $filename;
        }
        
        SiteSetting::set('site_logo', $path);

        return back()->with('success', 'Logo mis à jour');
    }

    public function deleteLogo(): RedirectResponse
    {
        $logoPath = SiteSetting::getLogo();
        if ($logoPath) {
            Storage::disk('public')->delete($logoPath);
            SiteSetting::set('site_logo', null);
        }

        return back()->with('success', 'Logo supprimé');
    }

    public function updateSiteName(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name' => 'required|string|max:100',
        ]);

        SiteSetting::set('site_name', $request->input('site_name'));

        return back()->with('success', 'Nom du site mis à jour');
    }

    private function trimSvgViewBox(string $svg): string
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($svg);
        
        $svgElement = $dom->getElementsByTagName('svg')->item(0);
        if (!$svgElement) {
            return $svg;
        }

        $minX = PHP_FLOAT_MAX;
        $minY = PHP_FLOAT_MAX;
        $maxX = PHP_FLOAT_MIN;
        $maxY = PHP_FLOAT_MIN;
        $found = false;

        $paths = $dom->getElementsByTagName('path');
        foreach ($paths as $path) {
            if ($path->hasAttribute('d')) {
                $bounds = $this->getPathBounds($path->getAttribute('d'));
                if ($bounds) {
                    $found = true;
                    $minX = min($minX, $bounds['minX']);
                    $minY = min($minY, $bounds['minY']);
                    $maxX = max($maxX, $bounds['maxX']);
                    $maxY = max($maxY, $bounds['maxY']);
                }
            }
        }

        $rects = $dom->getElementsByTagName('rect');
        foreach ($rects as $rect) {
            $found = true;
            $x = (float) ($rect->getAttribute('x') ?: 0);
            $y = (float) ($rect->getAttribute('y') ?: 0);
            $w = (float) $rect->getAttribute('width');
            $h = (float) $rect->getAttribute('height');
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x + $w);
            $maxY = max($maxY, $y + $h);
        }

        $circles = $dom->getElementsByTagName('circle');
        foreach ($circles as $circle) {
            $found = true;
            $cx = (float) $circle->getAttribute('cx');
            $cy = (float) $circle->getAttribute('cy');
            $r = (float) $circle->getAttribute('r');
            $minX = min($minX, $cx - $r);
            $minY = min($minY, $cy - $r);
            $maxX = max($maxX, $cx + $r);
            $maxY = max($maxY, $cy + $r);
        }

        $ellipses = $dom->getElementsByTagName('ellipse');
        foreach ($ellipses as $ellipse) {
            $found = true;
            $cx = (float) $ellipse->getAttribute('cx');
            $cy = (float) $ellipse->getAttribute('cy');
            $rx = (float) $ellipse->getAttribute('rx');
            $ry = (float) $ellipse->getAttribute('ry');
            $minX = min($minX, $cx - $rx);
            $minY = min($minY, $cy - $ry);
            $maxX = max($maxX, $cx + $rx);
            $maxY = max($maxY, $cy + $ry);
        }

        $polygons = $dom->getElementsByTagName('polygon');
        foreach ($polygons as $polygon) {
            if ($polygon->hasAttribute('points')) {
                $bounds = $this->getPointsBounds($polygon->getAttribute('points'));
                if ($bounds) {
                    $found = true;
                    $minX = min($minX, $bounds['minX']);
                    $minY = min($minY, $bounds['minY']);
                    $maxX = max($maxX, $bounds['maxX']);
                    $maxY = max($maxY, $bounds['maxY']);
                }
            }
        }

        $polylines = $dom->getElementsByTagName('polyline');
        foreach ($polylines as $polyline) {
            if ($polyline->hasAttribute('points')) {
                $bounds = $this->getPointsBounds($polyline->getAttribute('points'));
                if ($bounds) {
                    $found = true;
                    $minX = min($minX, $bounds['minX']);
                    $minY = min($minY, $bounds['minY']);
                    $maxX = max($maxX, $bounds['maxX']);
                    $maxY = max($maxY, $bounds['maxY']);
                }
            }
        }

        if (!$found) {
            return $svg;
        }

        $padding = 2;
        $newViewBox = sprintf('%.2f %.2f %.2f %.2f',
            $minX - $padding,
            $minY - $padding,
            ($maxX - $minX) + ($padding * 2),
            ($maxY - $minY) + ($padding * 2)
        );

        $svgElement->setAttribute('viewBox', $newViewBox);
        $svgElement->removeAttribute('width');
        $svgElement->removeAttribute('height');

        return $dom->saveXML();
    }

    private function getPathBounds(string $d): ?array
    {
        $minX = PHP_FLOAT_MAX;
        $minY = PHP_FLOAT_MAX;
        $maxX = PHP_FLOAT_MIN;
        $maxY = PHP_FLOAT_MIN;
        $found = false;

        $currentX = 0;
        $currentY = 0;

        preg_match_all('/([MmLlHhVvCcSsQqTtAaZz])([^MmLlHhVvCcSsQqTtAaZz]*)/s', $d, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $cmd = $match[1];
            $params = trim($match[2]);
            $numbers = [];
            
            if ($params !== '') {
                preg_match_all('/[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?/', $params, $numMatches);
                $numbers = array_map('floatval', $numMatches[0]);
            }

            $isRelative = ctype_lower($cmd);
            $cmdUpper = strtoupper($cmd);

            switch ($cmdUpper) {
                case 'M':
                case 'L':
                case 'T':
                    for ($i = 0; $i < count($numbers); $i += 2) {
                        if (isset($numbers[$i + 1])) {
                            $x = $isRelative ? $currentX + $numbers[$i] : $numbers[$i];
                            $y = $isRelative ? $currentY + $numbers[$i + 1] : $numbers[$i + 1];
                            $minX = min($minX, $x);
                            $minY = min($minY, $y);
                            $maxX = max($maxX, $x);
                            $maxY = max($maxY, $y);
                            $currentX = $x;
                            $currentY = $y;
                            $found = true;
                        }
                    }
                    break;

                case 'H':
                    foreach ($numbers as $num) {
                        $x = $isRelative ? $currentX + $num : $num;
                        $minX = min($minX, $x);
                        $maxX = max($maxX, $x);
                        $currentX = $x;
                        $found = true;
                    }
                    break;

                case 'V':
                    foreach ($numbers as $num) {
                        $y = $isRelative ? $currentY + $num : $num;
                        $minY = min($minY, $y);
                        $maxY = max($maxY, $y);
                        $currentY = $y;
                        $found = true;
                    }
                    break;

                case 'C':
                    for ($i = 0; $i < count($numbers); $i += 6) {
                        if (isset($numbers[$i + 5])) {
                            for ($j = 0; $j < 6; $j += 2) {
                                $x = $isRelative ? $currentX + $numbers[$i + $j] : $numbers[$i + $j];
                                $y = $isRelative ? $currentY + $numbers[$i + $j + 1] : $numbers[$i + $j + 1];
                                $minX = min($minX, $x);
                                $minY = min($minY, $y);
                                $maxX = max($maxX, $x);
                                $maxY = max($maxY, $y);
                            }
                            $currentX = $isRelative ? $currentX + $numbers[$i + 4] : $numbers[$i + 4];
                            $currentY = $isRelative ? $currentY + $numbers[$i + 5] : $numbers[$i + 5];
                            $found = true;
                        }
                    }
                    break;

                case 'S':
                case 'Q':
                    for ($i = 0; $i < count($numbers); $i += 4) {
                        if (isset($numbers[$i + 3])) {
                            for ($j = 0; $j < 4; $j += 2) {
                                $x = $isRelative ? $currentX + $numbers[$i + $j] : $numbers[$i + $j];
                                $y = $isRelative ? $currentY + $numbers[$i + $j + 1] : $numbers[$i + $j + 1];
                                $minX = min($minX, $x);
                                $minY = min($minY, $y);
                                $maxX = max($maxX, $x);
                                $maxY = max($maxY, $y);
                            }
                            $currentX = $isRelative ? $currentX + $numbers[$i + 2] : $numbers[$i + 2];
                            $currentY = $isRelative ? $currentY + $numbers[$i + 3] : $numbers[$i + 3];
                            $found = true;
                        }
                    }
                    break;

                case 'A':
                    for ($i = 0; $i < count($numbers); $i += 7) {
                        if (isset($numbers[$i + 6])) {
                            $x = $isRelative ? $currentX + $numbers[$i + 5] : $numbers[$i + 5];
                            $y = $isRelative ? $currentY + $numbers[$i + 6] : $numbers[$i + 6];
                            $minX = min($minX, $x);
                            $minY = min($minY, $y);
                            $maxX = max($maxX, $x);
                            $maxY = max($maxY, $y);
                            $currentX = $x;
                            $currentY = $y;
                            $found = true;
                        }
                    }
                    break;
            }
        }

        if (!$found) {
            return null;
        }

        return ['minX' => $minX, 'minY' => $minY, 'maxX' => $maxX, 'maxY' => $maxY];
    }

    private function getPointsBounds(string $points): ?array
    {
        $minX = PHP_FLOAT_MAX;
        $minY = PHP_FLOAT_MAX;
        $maxX = PHP_FLOAT_MIN;
        $maxY = PHP_FLOAT_MIN;
        $found = false;

        preg_match_all('/[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?/', $points, $matches);
        $numbers = array_map('floatval', $matches[0]);

        for ($i = 0; $i < count($numbers); $i += 2) {
            if (isset($numbers[$i + 1])) {
                $found = true;
                $minX = min($minX, $numbers[$i]);
                $minY = min($minY, $numbers[$i + 1]);
                $maxX = max($maxX, $numbers[$i]);
                $maxY = max($maxY, $numbers[$i + 1]);
            }
        }

        if (!$found) {
            return null;
        }

        return ['minX' => $minX, 'minY' => $minY, 'maxX' => $maxX, 'maxY' => $maxY];
    }
}
