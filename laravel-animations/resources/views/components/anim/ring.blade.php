@props(['percent', 'size' => 96, 'stroke' => 9])
{{-- <x-anim.ring :percent="$disk->usedPercent()" /> --}}
<div class="ring" data-ring="{{ $percent }}" data-size="{{ $size }}" data-stroke="{{ $stroke }}" {{ $attributes }}>{{ $percent }} %</div>
