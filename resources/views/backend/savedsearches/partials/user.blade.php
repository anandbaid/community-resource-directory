@php
    $user = $savedSearch->user;
@endphp
<div class="d-flex flex-column">
    <span class="fw-semibold">{{ $user->name ?? 'Unknown User' }}</span>
    @if (!empty($user?->email))
        <span class="text-muted small">{{ $user->email }}</span>
    @endif
</div>
