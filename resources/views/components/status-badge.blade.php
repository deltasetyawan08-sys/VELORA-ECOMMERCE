@props(['label', 'tone' => 'badge-neutral'])

<span {{ $attributes->merge(['class' => 'badge '.$tone]) }}>{{ $label }}</span>
