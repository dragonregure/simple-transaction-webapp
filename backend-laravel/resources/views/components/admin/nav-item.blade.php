@props([
    'route',
    'icon',
    'label',
])

@php
    $isActive = request()->routeIs($route);
@endphp

<li class="nav-item">
    <a href="{{ route($route, [], false) }}" @class(['nav-link', 'active' => $isActive])>
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $label }}</p>
    </a>
</li>
