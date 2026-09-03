<div class="d-flex gap-1">
    @if ($downloadUrl)
        <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm" title="Download PDF">
            <i class="fa-solid fa-download"></i>
        </a>
    @else
        <span class="text-muted small align-self-center">No file</span>
    @endif
    <button type="button" class="btn btn-danger btn-sm delete-saved-search" title="Delete"
        data-url="{{ route('admin.saved-searches.destroy', $savedSearch->id) }}">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
