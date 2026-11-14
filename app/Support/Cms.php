<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

/** Helpers that turn CMS-stored Elementor markup into a rendered page. */
class Cms
{
    /** Stylesheets (in original order) for a page set: home | about-me | professional | login */
    public static function css(string $set): array
    {
        static $manifest;
        $manifest ??= json_decode(File::get(database_path('seed/css-manifest.json')), true);

        $links = $manifest[$set] ?? $manifest['about-me'];

        // Skip files that were not mirrored so a missing asset never blocks rendering.
        return array_values(array_filter($links, fn ($l) => File::exists(public_path(ltrim($l, '/')))));
    }

    /** Replace [[menu]] with the live navigation items. */
    public static function render(?string $html): HtmlString
    {
        $html = $html ?? '';

        if (str_contains($html, '[[menu]]')) {
            $html = str_replace('[[menu]]', self::menuItems(), $html);
        }

        return new HtmlString($html);
    }

    public static function part(string $name): HtmlString
    {
        return self::render(Setting::get("part_{$name}", ''));
    }

    private static function menuItems(): string
    {
        $current = '/'.trim(request()->path(), '/');
        $out = '';

        foreach (MenuItem::where('is_visible', true)->orderBy('position')->get() as $item) {
            $active = rtrim($item->url, '/') === rtrim($current, '/') || ($item->url === '/' && $current === '/');
            $out .= '<li class="menu-item'.($active ? ' current-menu-item' : '').'"><a href="'.e($item->url).'"'.($active ? ' aria-current="page"' : '').'>'.e($item->label).'</a></li>'."\n";
        }

        return $out;
    }
}
