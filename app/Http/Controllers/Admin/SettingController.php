<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['menu' => MenuItem::orderBy('position')->get()]);
    }

    public function update(Request $request)
    {
        // Rows with no label and no URL are "blank" (the spare add-row or a removed item).
        $rows = collect($request->input('menu', []))
            ->filter(fn ($r) => filled($r['label'] ?? null) || filled($r['url'] ?? null))
            ->values();
        $request->merge(['menu' => $rows->all()]);

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email'],
            'community_rules' => ['nullable', 'string', 'max:5000'],
            'menu.*.label' => ['required', 'string', 'max:60'],
            'menu.*.url' => ['required', 'string', 'max:255'],
            'menu.*.position' => ['nullable', 'integer'],
        ]);

        Setting::put('site_name', $data['site_name']);
        Setting::put('contact_email', $data['contact_email'] ?? null);
        Setting::put('community_rules', $data['community_rules'] ?? null);

        foreach (['member_posts_enabled', 'member_posts_moderated', 'send_rejection_email'] as $flag) {
            Setting::put($flag, $request->boolean($flag) ? '1' : '0');
        }

        $ids = [];
        foreach ($rows as $i => $row) {
            $item = MenuItem::updateOrCreate(
                ['id' => $row['id'] ?? null ?: null],
                ['label' => $row['label'], 'url' => $row['url'], 'position' => $row['position'] ?? $i, 'is_visible' => (bool) ($row['is_visible'] ?? false)]
            );
            $ids[] = $item->id;
        }
        MenuItem::whereNotIn('id', $ids)->delete();

        AuditLog::record('settings.updated');

        return back()->with('status', 'Settings saved.');
    }
}
