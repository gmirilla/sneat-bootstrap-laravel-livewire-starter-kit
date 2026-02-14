<div class="card mb-4">
    @if(isset($title))
        <div class="card-header fw-bold">
            {{ $title }}
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>
