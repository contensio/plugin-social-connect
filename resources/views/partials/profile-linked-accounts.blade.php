{{--
    Pushed into core's `profile.sections` hook.
    Renders the Linked accounts card on the user's profile page.
    Receives $user from the hook caller.
--}}

@php
    $available  = \Contensio\SocialConnect\Support\Providers::enabled();
    $linked     = \Contensio\SocialConnect\Models\SocialLogin::where('user_id', $user->id)
                    ->get()
                    ->keyBy('provider');
@endphp

@if(! empty($available))
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">
    <div>
        <h2 class="text-base font-bold text-gray-900">Linked accounts</h2>
        <p class="text-xs text-gray-500 mt-0.5">Sign in faster using your existing Google, GitHub, Facebook, or Microsoft account.</p>
    </div>

    <div class="space-y-2">
        @foreach($available as $key => $meta)
        @php $row = $linked[$key] ?? null; @endphp
        <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/40">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background: {{ $meta['color'] }}">
                <i class="bi {{ $meta['icon'] }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ $meta['label'] }}</p>
                @if($row)
                <p class="text-xs text-gray-500">
                    Connected as {{ $row->email ?: $row->name ?: 'account #' . $row->provider_user_id }}
                    @if($row->linked_at) · since {{ $row->linked_at->format('M d, Y') }} @endif
                </p>
                @else
                <p class="text-xs text-gray-500">Not connected</p>
                @endif
            </div>
            @if($row)
                <form method="POST" action="{{ route('socialconnect.unlink', $key) }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 px-3 py-1.5">
                        Unlink
                    </button>
                </form>
            @else
                <a href="{{ route('socialconnect.redirect', [$key, 'connect' => 1]) }}"
                   class="text-xs font-medium text-blue-600 hover:text-blue-700 px-3 py-1.5">
                    Connect
                </a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
