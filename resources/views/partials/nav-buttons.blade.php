{{-- resources/views/partials/nav-buttons.blade.php --}}
<div class="d-flex justify-content-between mb-4">
    @if($showPrev ?? false)
    <button type="button" class="btn btn-secondary prev-step">
        <i class="fas fa-arrow-left me-2"></i> Previous
    </button>
    @else
    <div></div>
    @endif
    
    @if(!($isLast ?? false))
    <button type="button" class="btn btn-primary next-step">
        Next <i class="fas fa-arrow-right ms-2"></i>
    </button>
    @endif
</div>