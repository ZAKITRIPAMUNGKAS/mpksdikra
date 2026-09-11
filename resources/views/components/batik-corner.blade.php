@props([
    'position' => 'top-right',
    'class'    => 'w-40 h-40 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.30]',
    'fade'     => true,
    'reveal'   => true,
])
@php
    $posClass = match($position) {
        'top-left'     => 'top-0 left-0',
        'top-right'    => 'top-0 right-0',
        'bottom-right' => 'bottom-0 right-0',
        'bottom-left'  => 'bottom-0 left-0',
        default        => 'top-0 right-0',
    };
    $uid = 'kw' . substr(md5(uniqid('', true)), 0, 6);
    $maskId = 'mask-' . $uid;

    // SVG coordinate mirror so motif origin (0,0) is symmetrically anchored at the corner
    $svgFlip = match($position) {
        'top-left'     => '',
        'top-right'    => '-scale-x-100',
        'bottom-right' => '-scale-x-100 -scale-y-100',
        'bottom-left'  => '-scale-y-100',
        default        => '',
    };

    $initialShift = match($position) {
        'top-left'     => '-translate-x-10 -translate-y-10 sm:-translate-x-14 sm:-translate-y-14',
        'top-right'    => 'translate-x-10 -translate-y-10 sm:translate-x-14 sm:-translate-y-14',
        'bottom-right' => 'translate-x-10 translate-y-10 sm:translate-x-14 sm:translate-y-14',
        'bottom-left'  => '-translate-x-10 translate-y-10 sm:-translate-x-14 sm:translate-y-14',
        default        => 'translate-x-10 -translate-y-10 sm:translate-x-14 sm:-translate-y-14',
    };
@endphp
<div {{ $attributes->merge(['class' => "absolute {$posClass} pointer-events-none select-none z-0 {$class}"]) }} aria-hidden="true">
@if($reveal)
  <div 
    x-data="{ revealed: false }" 
    x-init="
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                revealed = true;
                observer.disconnect();
            }
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        observer.observe($el);
    "
    :class="revealed ? 'opacity-100 scale-100 translate-x-0 translate-y-0' : 'opacity-0 scale-[0.72] {{ $initialShift }}'"
    class="w-full h-full transform-gpu transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] will-change-transform"
  >
@endif
<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" class="w-full h-full origin-center {{ $svgFlip }}">
  <defs>
    <pattern id="{{ $uid }}" x="0" y="0" width="160.0" height="160.0" patternUnits="userSpaceOnUse">
    <circle cx="40" cy="40" r="40.0" fill="none" stroke="currentColor" stroke-opacity="0.65" stroke-width="1.0" />
    <path d="M 40,0 A 40.0,40.0 0 0,0 0,40 A 40.0,40.0 0 0,0 40,0 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="24.76" cy="24.76" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="21.86" cy="21.86" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="18.7" cy="18.7" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="15.8" cy="15.8" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="22.33" cy="18.23" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="18.23" cy="22.33" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 40,80 A 40.0,40.0 0 0,0 80,40 A 40.0,40.0 0 0,0 40,80 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="55.24" cy="55.24" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="58.14" cy="58.14" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="61.3" cy="61.3" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="64.2" cy="64.2" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="57.67" cy="61.77" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="61.77" cy="57.67" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 80,40 A 40.0,40.0 0 0,0 40,0 A 40.0,40.0 0 0,0 80,40 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="55.24" cy="24.76" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="58.14" cy="21.86" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="61.3" cy="18.7" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="64.2" cy="15.8" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="61.77" cy="22.33" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="57.67" cy="18.23" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 0,40 A 40.0,40.0 0 0,0 40,80 A 40.0,40.0 0 0,0 0,40 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="24.76" cy="55.24" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="21.86" cy="58.14" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="18.7" cy="61.3" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="15.8" cy="64.2" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="18.23" cy="57.67" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="22.33" cy="61.77" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <ellipse cx="40" cy="37.2" rx="1.5" ry="4.2" transform="rotate(0 40 37.2)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="42.66" cy="39.13" rx="1.5" ry="4.2" transform="rotate(72 42.66 39.13)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="41.65" cy="42.27" rx="1.5" ry="4.2" transform="rotate(144 41.65 42.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="38.35" cy="42.27" rx="1.5" ry="4.2" transform="rotate(216 38.35 42.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="37.34" cy="39.13" rx="1.5" ry="4.2" transform="rotate(288 37.34 39.13)" fill="currentColor" fill-opacity="0.95"/>
      <circle cx="40" cy="40" r="0.85" fill="white" fill-opacity="0.95"/>
    <circle cx="120" cy="40" r="40.0" fill="none" stroke="currentColor" stroke-opacity="0.65" stroke-width="1.0" />
    <path d="M 120,0 A 40.0,40.0 0 0,0 80,40 A 40.0,40.0 0 0,0 120,0 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="104.76" cy="24.76" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="101.86" cy="21.86" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="98.7" cy="18.7" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="95.8" cy="15.8" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="102.33" cy="18.23" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="98.23" cy="22.33" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 120,80 A 40.0,40.0 0 0,0 160,40 A 40.0,40.0 0 0,0 120,80 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="135.24" cy="55.24" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="138.14" cy="58.14" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="141.3" cy="61.3" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="144.2" cy="64.2" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="137.67" cy="61.77" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="141.77" cy="57.67" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 160,40 A 40.0,40.0 0 0,0 120,0 A 40.0,40.0 0 0,0 160,40 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="135.24" cy="24.76" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="138.14" cy="21.86" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="141.3" cy="18.7" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="144.2" cy="15.8" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="141.77" cy="22.33" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="137.67" cy="18.23" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 80,40 A 40.0,40.0 0 0,0 120,80 A 40.0,40.0 0 0,0 80,40 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="104.76" cy="55.24" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="101.86" cy="58.14" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="98.7" cy="61.3" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="95.8" cy="64.2" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="98.23" cy="57.67" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="102.33" cy="61.77" r="1.35" fill="white" fill-opacity="0.95"/>
    <ellipse cx="120" cy="37.2" rx="1.5" ry="4.2" transform="rotate(0 120 37.2)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="122.66" cy="39.13" rx="1.5" ry="4.2" transform="rotate(72 122.66 39.13)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="121.65" cy="42.27" rx="1.5" ry="4.2" transform="rotate(144 121.65 42.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="118.35" cy="42.27" rx="1.5" ry="4.2" transform="rotate(216 118.35 42.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="117.34" cy="39.13" rx="1.5" ry="4.2" transform="rotate(288 117.34 39.13)" fill="currentColor" fill-opacity="0.95"/>
      <circle cx="120" cy="40" r="0.85" fill="white" fill-opacity="0.95"/>
    <circle cx="40" cy="120" r="40.0" fill="none" stroke="currentColor" stroke-opacity="0.65" stroke-width="1.0" />
    <path d="M 40,80 A 40.0,40.0 0 0,0 0,120 A 40.0,40.0 0 0,0 40,80 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="24.76" cy="104.76" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="21.86" cy="101.86" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="18.7" cy="98.7" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="15.8" cy="95.8" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="22.33" cy="98.23" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="18.23" cy="102.33" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 40,160 A 40.0,40.0 0 0,0 80,120 A 40.0,40.0 0 0,0 40,160 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="55.24" cy="135.24" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="58.14" cy="138.14" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="61.3" cy="141.3" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="64.2" cy="144.2" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="57.67" cy="141.77" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="61.77" cy="137.67" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 80,120 A 40.0,40.0 0 0,0 40,80 A 40.0,40.0 0 0,0 80,120 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="55.24" cy="104.76" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="58.14" cy="101.86" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="61.3" cy="98.7" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="64.2" cy="95.8" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="61.77" cy="102.33" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="57.67" cy="98.23" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 0,120 A 40.0,40.0 0 0,0 40,160 A 40.0,40.0 0 0,0 0,120 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="24.76" cy="135.24" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="21.86" cy="138.14" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="18.7" cy="141.3" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="15.8" cy="144.2" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="18.23" cy="137.67" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="22.33" cy="141.77" r="1.35" fill="white" fill-opacity="0.95"/>
    <ellipse cx="40" cy="117.2" rx="1.5" ry="4.2" transform="rotate(0 40 117.2)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="42.66" cy="119.13" rx="1.5" ry="4.2" transform="rotate(72 42.66 119.13)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="41.65" cy="122.27" rx="1.5" ry="4.2" transform="rotate(144 41.65 122.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="38.35" cy="122.27" rx="1.5" ry="4.2" transform="rotate(216 38.35 122.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="37.34" cy="119.13" rx="1.5" ry="4.2" transform="rotate(288 37.34 119.13)" fill="currentColor" fill-opacity="0.95"/>
      <circle cx="40" cy="120" r="0.85" fill="white" fill-opacity="0.95"/>
    <circle cx="120" cy="120" r="40.0" fill="none" stroke="currentColor" stroke-opacity="0.65" stroke-width="1.0" />
    <path d="M 120,80 A 40.0,40.0 0 0,0 80,120 A 40.0,40.0 0 0,0 120,80 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="104.76" cy="104.76" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="101.86" cy="101.86" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="98.7" cy="98.7" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="95.8" cy="95.8" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="102.33" cy="98.23" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="98.23" cy="102.33" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 120,160 A 40.0,40.0 0 0,0 160,120 A 40.0,40.0 0 0,0 120,160 Z" fill="currentColor" fill-opacity="0.82" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.0" />
      <circle cx="135.24" cy="135.24" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="138.14" cy="138.14" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="141.3" cy="141.3" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="144.2" cy="144.2" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="137.67" cy="141.77" r="1.35" fill="white" fill-opacity="0.95"/>
      <circle cx="141.77" cy="137.67" r="1.35" fill="white" fill-opacity="0.95"/>
    <path d="M 160,120 A 40.0,40.0 0 0,0 120,80 A 40.0,40.0 0 0,0 160,120 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="135.24" cy="104.76" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="138.14" cy="101.86" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="141.3" cy="98.7" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="144.2" cy="95.8" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="141.77" cy="102.33" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="137.67" cy="98.23" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <path d="M 80,120 A 40.0,40.0 0 0,0 120,160 A 40.0,40.0 0 0,0 80,120 Z" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-opacity="0.75" stroke-width="1.0" />
      <circle cx="104.76" cy="135.24" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="101.86" cy="138.14" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="98.7" cy="141.3" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="95.8" cy="144.2" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="98.23" cy="137.67" r="1.35" fill="currentColor" fill-opacity="0.80"/>
      <circle cx="102.33" cy="141.77" r="1.35" fill="currentColor" fill-opacity="0.80"/>
    <ellipse cx="120" cy="117.2" rx="1.5" ry="4.2" transform="rotate(0 120 117.2)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="122.66" cy="119.13" rx="1.5" ry="4.2" transform="rotate(72 122.66 119.13)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="121.65" cy="122.27" rx="1.5" ry="4.2" transform="rotate(144 121.65 122.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="118.35" cy="122.27" rx="1.5" ry="4.2" transform="rotate(216 118.35 122.27)" fill="currentColor" fill-opacity="0.95"/>
      <ellipse cx="117.34" cy="119.13" rx="1.5" ry="4.2" transform="rotate(288 117.34 119.13)" fill="currentColor" fill-opacity="0.95"/>
      <circle cx="120" cy="120" r="0.85" fill="white" fill-opacity="0.95"/>
    </pattern>

    @if($fade)
    <radialGradient id="{{ $maskId }}" cx="0%" cy="0%" r="100%">
      <stop offset="0%" stop-color="#fff" stop-opacity="1" />
      <stop offset="35%" stop-color="#fff" stop-opacity="0.95" />
      <stop offset="60%" stop-color="#fff" stop-opacity="0.75" />
      <stop offset="80%" stop-color="#fff" stop-opacity="0.40" />
      <stop offset="92%" stop-color="#fff" stop-opacity="0.12" />
      <stop offset="100%" stop-color="#fff" stop-opacity="0" />
    </radialGradient>
    <mask id="m-{{ $uid }}">
      <rect width="100%" height="100%" fill="url(#{{ $maskId }})" />
    </mask>
    @endif
  </defs>
  <rect width="100%" height="100%" fill="url(#{{ $uid }})" @if($fade) mask="url(#m-{{ $uid }})" @endif />
</svg>
@if($reveal)
  </div>
@endif
</div>