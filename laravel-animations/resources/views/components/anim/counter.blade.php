@props(['value', 'suffix' => ''])
{{-- <x-anim.counter value="27,26" suffix="h" class="text-4xl" /> --}}
<span data-count="{{ $value }}" data-suffix="{{ $suffix }}" {{ $attributes }}>{{ $value }}{{ $suffix }}</span>
