@props(['title', 'badge' => null])

<article {{ $attributes->class(['feature-card card h-100']) }}>
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <h3 class="h5 card-title mb-0">{{ $title }}</h3>
            @if ($badge)
                <span class="badge text-bg-accent">{{ $badge }}</span>
            @endif
        </div>
        <div class="card-text text-secondary">{{ $slot }}</div>
    </div>
</article>
