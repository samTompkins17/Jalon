@props(['values' => [], 'height' => 120])
{{-- <x-anim.bars :values="[40, 65, 30, 90, 55]" /> --}}
@php $max = max($values ?: [1]); @endphp
<div data-bars style="display:flex;align-items:flex-end;gap:8px;height:{{ $height }}px" {{ $attributes }}>
  @foreach ($values as $v)
    <div class="bar" style="flex:1;height:{{ round($v / $max * 100) }}%;background:var(--anim-accent);border-radius:6px 6px 2px 2px"></div>
  @endforeach
</div>
