@props(['title', 'description' => null])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line bg-cream-50 px-6 py-16 text-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-burgundy-500 shadow-sm">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
    </div>
    <h3 class="mt-5 font-display text-xl text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-md text-sm text-muted">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
