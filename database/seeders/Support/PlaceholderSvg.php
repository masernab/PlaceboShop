<?php

namespace Database\Seeders\Support;

class PlaceholderSvg
{
    /**
     * Write a deterministic pastel placeholder SVG for the product (if missing)
     * and return its web-relative path.
     */
    public static function generate(string $slug, string $name, int $variant = 0): string
    {
        $relativePath = sprintf('images/products/%s-%d.svg', $slug, $variant + 1);
        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            $directory = dirname($absolutePath);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($absolutePath, self::svg($slug, $name, $variant));
        }

        return $relativePath;
    }

    private static function svg(string $slug, string $name, int $variant): string
    {
        $seed = crc32($slug) + $variant * 47;

        $hue = $seed % 360;
        $hueTo = ($hue + 45) % 360;
        $from = "hsl({$hue}, 75%, 90%)";
        $to = "hsl({$hueTo}, 65%, 78%)";
        $ink = "hsl({$hue}, 35%, 32%)";

        $circleX = 190 + ($seed % 620);
        $circleY = 180 + ($seed % 260);
        $gradientId = "g-{$slug}-{$variant}";

        $lines = explode("\n", wordwrap($name, 16, "\n", true));
        $startY = 520 - (count($lines) - 1) * 36;
        $tspans = '';

        foreach ($lines as $index => $line) {
            $escaped = htmlspecialchars($line, ENT_XML1);
            $y = $startY + $index * 72;
            $tspans .= "<tspan x=\"500\" y=\"{$y}\">{$escaped}</tspan>";
        }

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="1000" height="1000" viewBox="0 0 1000 1000">
              <defs>
                <linearGradient id="{$gradientId}" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0" stop-color="{$from}"/>
                  <stop offset="1" stop-color="{$to}"/>
                </linearGradient>
              </defs>
              <rect width="1000" height="1000" fill="url(#{$gradientId})"/>
              <circle cx="{$circleX}" cy="{$circleY}" r="190" fill="#ffffff" opacity="0.35"/>
              <text text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="58" fill="{$ink}">{$tspans}</text>
              <text x="500" y="920" text-anchor="middle" font-family="system-ui, sans-serif" font-size="26" letter-spacing="6" fill="{$ink}" opacity="0.6">PLACEBOSHOP</text>
            </svg>
            SVG;
    }
}
