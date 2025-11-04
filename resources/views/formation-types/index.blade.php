<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-book me-2 text-primary"></i>
                        {{ __('messages.formation_types_title') }}
                    </h5>
                    <div class="d-flex gap-2 justify-content-end align-items-center">
                        <div class="d-flex justify-content-end align-items-center w-auto" style="width: max-content;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="ftSearch" placeholder="{{ __('messages.search_by_name_or_code') }}" onkeyup="filterFormationTypes()">
                            </div>
                            <span id="ftSearchCount" class="text-muted small mt-2" data-results-text="{{ __('messages.results_count') }}"></span>
                        </div>
                        <a href="{{ route('formation-types.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.add_formation_type') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Toast Container - Top Center -->
            <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
                @if(session('success'))
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
                        <div class="toast-header bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong class="me-auto">{{ __('messages.success') }}</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
        
                @if(session('error'))
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
                        <div class="toast-header bg-danger text-white">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong class="me-auto">{{ __('messages.error') }}</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="formationTypesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.formation_type_name') }}</th>
                                <th>{{ __('messages.formation_type_code') }}</th>
                                <th>{{ __('messages.formation_type_description') }}</th>
                                <th>{{ __('messages.formation_type_status') }}</th>
                                <th>{{ __('messages.obligatoire') }}</th>
                                <th class="text-end pe-3">{{ __('messages.formation_type_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formationTypes as $type)
                                <tr>
                                    <td class="ps-3">
                                        <strong>{{ $type->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $type->code }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ \Illuminate\Support\Str::limit($type->description ?? __('messages.no_description'), 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($type->is_active)
                                            <span class="badge bg-success bg-opacity-25 text-success">{{ __('messages.formation_type_active') }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">{{ __('messages.formation_type_inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($type->obligatoire)
                                            <span class="badge bg-danger bg-opacity-25 text-danger">{{ __('messages.obligatoire') }}</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-25 text-info">{{ __('messages.optional') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('formation-types.edit', $type) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('formation-types.destroy', $type) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  id="delete-form-{{ $type->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('messages.delete') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmDeleteModal"
                                                        data-form-id="delete-form-{{ $type->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_formation_types_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="confirmDeleteModalLabel">{{ __('messages.confirm_delete') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ __('messages.confirm_delete_message') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function filterFormationTypes() {
                const input = document.getElementById('ftSearch');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('formationTypesTable');
                const rows = table.getElementsByTagName('tr');
                let visible = 0;
                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    if (!cells.length) continue;
                    const name = (cells[0].innerText || '').toLowerCase();
                    const code = (cells[1].innerText || '').toLowerCase();
                    const match = name.includes(filter) || code.includes(filter);
                    rows[i].style.display = match ? '' : 'none';
                    if (match) visible++;
                }
                const count = document.getElementById('ftSearchCount');
                if (count) {
                    const resultsText = count.getAttribute('data-results-text') || '{{ __('messages.results_count') }}';
                    count.textContent = visible + ' ' + resultsText;
                }
            }
            // Delete confirmation behaviour
            (function() {
                const modal = document.getElementById('confirmDeleteModal');
                const confirmBtn = document.getElementById('confirmDeleteBtn');
                let targetFormId = null;

                if (modal) {
                    modal.addEventListener('show.bs.modal', function (event) {
                        const triggerButton = event.relatedTarget;
                        targetFormId = triggerButton ? triggerButton.getAttribute('data-form-id') : null;
                    });
                }

                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function () {
                        if (!targetFormId) return;
                        const form = document.getElementById(targetFormId);
                        if (form) form.submit();
                    });
                }
            })();

            // Initialize and show toasts on page load
            document.addEventListener('DOMContentLoaded', function() {
                const successToast = document.getElementById('successToast');
                const errorToast = document.getElementById('errorToast');

                if (successToast && typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(successToast);
                    toast.show();
                }

                if (errorToast && typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(errorToast);
                    toast.show();
                }
            });
        </script>
        @endpush
    </div>
</x-app-layout>
