{{--
    Pushed into core's `login.after_form` hook by SocialConnectServiceProvider.
    Renders the "Continue with X" buttons below the standard login form.
--}}

@php $providers = $providers ?? \Contensio\SocialConnect\Support\Providers::enabled(); @endphp

@if(! empty($providers))
<div class="relative my-6">
    <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200"></div>
    </div>
    <div class="relative flex justify-center">
        <span class="bg-white px-3 text-xs text-gray-400 uppercase tracking-wider font-medium">Or continue with</span>
    </div>
</div>

<div class="space-y-2">
    @foreach($providers as $provider => $meta)
    <a href="{{ route('contensio-social-connect.redirect', $provider) }}"
       class="w-full flex items-center justify-center gap-2.5 border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium text-sm py-2.5 rounded-lg transition-colors">
        <i class="bi {{ $meta['icon'] }} text-base leading-none" style="color: {{ $meta['color'] }}"></i>
        <span>Continue with {{ $meta['label'] }}</span>
    </a>
    @endforeach
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
@endif
