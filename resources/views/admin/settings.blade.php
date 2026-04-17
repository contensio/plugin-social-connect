{{--
 | Social Connect — admin settings page.
 | Rendered under the CMS admin layout.
 | https://contensio.com
--}}

@extends('contensio::admin.layout')

@section('title', 'Social Connect')

@section('breadcrumb')
<a href="{{ route('contensio.account.settings.index') }}" class="text-gray-400 hover:text-gray-700">Configuration</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Social Connect</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Social Connect</h1>
    <p class="text-sm text-gray-500 mb-5">Let users sign in with their Google, GitHub, Facebook, or Microsoft accounts — no new password to remember.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-5 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
        <p class="text-blue-900 font-medium mb-1">How Social Connect works</p>
        <ul class="text-blue-800/90 text-xs space-y-1 leading-relaxed">
            <li>• Users must already exist in Contensio. Social sign-in <strong>links</strong> an existing account — it doesn't register new users.</li>
            <li>• Matching is by <strong>email address</strong>. If Google's email matches a Contensio user, the link is created automatically.</li>
            <li>• Users with 2FA enabled are still challenged after social sign-in.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('socialconnect.settings.save') }}">
        @csrf

        <div class="space-y-4">
            @foreach($providers as $key => $provider)
            @php $cfg = $provider['config']; @endphp
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shrink-0"
                         style="background: {{ $provider['color'] }}">
                        <i class="bi {{ $provider['icon'] }}"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-gray-900">{{ $provider['label'] }}</h2>
                        @if(! empty($provider['docs']))
                        <p class="text-xs text-gray-500">
                            <a href="{{ $provider['docs'] }}" target="_blank" rel="noopener" class="hover:text-blue-600 inline-flex items-center gap-1">
                                Open provider's developer console
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </p>
                        @endif
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="providers[{{ $key }}][enabled]" value="0">
                        <input type="checkbox" name="providers[{{ $key }}][enabled]" value="1"
                               {{ ! empty($cfg['enabled']) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Enabled</span>
                    </label>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Redirect URL (copy into your {{ $provider['label'] }} OAuth app)</label>
                        <div class="flex items-center gap-2" x-data="{ copied: false }">
                            <code class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono break-all text-gray-700">{{ $provider['redirect'] }}</code>
                            <button type="button"
                                    @click="navigator.clipboard.writeText(@js($provider['redirect'])); copied = true; setTimeout(()=>copied=false, 1500)"
                                    class="shrink-0 text-xs font-medium text-blue-600 hover:text-blue-700 px-2">
                                <span x-show="!copied">Copy</span>
                                <span x-show="copied" x-cloak>Copied ✓</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                            <input type="text" name="providers[{{ $key }}][client_id]"
                                   value="{{ old('providers.' . $key . '.client_id', $cfg['client_id'] ?? '') }}"
                                   autocomplete="off"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client secret</label>
                            <input type="password" name="providers[{{ $key }}][client_secret]"
                                   value="{{ old('providers.' . $key . '.client_secret', $cfg['client_secret'] ?? '') }}"
                                   autocomplete="new-password"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="{{ ! empty($cfg['client_secret']) ? '••••••••••••••••' : '' }}">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end mt-5">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </form>

    <style>[x-cloak] { display: none !important; }</style>

</div>

@endsection
