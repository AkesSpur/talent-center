<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-2.5 gradient-gold border border-transparent rounded-lg font-semibold text-sm text-dark uppercase tracking-widest hover:opacity-90 active:opacity-95 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
