@extends('admin.layout', ['title' => 'Settings'])

@php use App\Models\Setting; @endphp
@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="card">
            <h2>General</h2>
            <div class="grid2">
                <div><label for="site_name">Site name</label><input id="site_name" type="text" name="site_name" value="{{ old('site_name', Setting::get('site_name', 'Aamir Sheeraz')) }}" required></div>
                <div><label for="contact_email">Contact email</label><input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', Setting::get('contact_email')) }}"></div>
            </div>
        </div>

        <div class="card">
            <h2>Community</h2>
            <label class="check"><input type="hidden" name="member_posts_enabled" value="0"><input type="checkbox" name="member_posts_enabled" value="1" @checked(Setting::bool('member_posts_enabled'))> Allow approved members to create their own posts</label>
            <label class="check"><input type="hidden" name="member_posts_moderated" value="0"><input type="checkbox" name="member_posts_moderated" value="1" @checked(Setting::bool('member_posts_moderated', true))> Hold member posts for moderator approval</label>
            <label class="check"><input type="hidden" name="send_rejection_email" value="0"><input type="checkbox" name="send_rejection_email" value="1" @checked(Setting::bool('send_rejection_email'))> Send a courtesy email when an application is rejected</label>
            <label for="community_rules">Community rules (shown to members)</label>
            <textarea id="community_rules" name="community_rules">{{ old('community_rules', Setting::get('community_rules')) }}</textarea>
        </div>

        <div class="card">
            <h2>Website navigation menu</h2>
            <table>
                <thead><tr><th>Label</th><th>URL</th><th>Order</th><th>Visible</th></tr></thead>
                <tbody>
                @foreach($menu->concat([new \App\Models\MenuItem(['label' => '', 'url' => '', 'position' => $menu->count(), 'is_visible' => true])]) as $i => $m)
                    <tr>
                        <td><input type="hidden" name="menu[{{ $i }}][id]" value="{{ $m->id }}"><input type="text" name="menu[{{ $i }}][label]" value="{{ $m->label }}" {{ $m->exists ? 'required' : '' }}></td>
                        <td><input type="text" name="menu[{{ $i }}][url]" value="{{ $m->url }}" placeholder="/about-me" {{ $m->exists ? 'required' : '' }}></td>
                        <td style="width:90px"><input type="number" name="menu[{{ $i }}][position]" value="{{ $m->position }}"></td>
                        <td style="width:80px"><input type="hidden" name="menu[{{ $i }}][is_visible]" value="0"><input type="checkbox" name="menu[{{ $i }}][is_visible]" value="1" @checked($m->is_visible) style="width:auto"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted">Clear a row's label and URL to remove it. The last empty row adds a new item.</p>
        </div>

        <button class="btn">Save settings</button>
    </form>
@endsection
