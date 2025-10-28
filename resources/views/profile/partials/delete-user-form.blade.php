<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
    <i class="fas fa-trash me-2"></i>{{ __('Delete Account') }}
</button>

@if($errors->userDeletion->isNotEmpty())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('confirm-user-deletion');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    </script>
    @endpush
@endif
