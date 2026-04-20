{{--
    Pushed into core's `settings.hub_cards` hook.
    Renders the Social Connect tile on the Configuration page.
--}}

<a href="{{ route('contensio-social-connect.settings') }}"
   class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition-colors">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-gray-900">Social Connect</h3>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Let users sign in with Google, GitHub, Facebook, Microsoft.</p>
        </div>
    </div>
</a>
