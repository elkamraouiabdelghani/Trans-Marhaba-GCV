<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-list-check me-2 text-primary"></i>
                    {{ __('messages.journeys_checklist') ?? 'Journeys Checklist' }}
                </h5>
                <button type="button"
                        class="btn btn-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createJourneyChecklistItemModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ __('messages.add') ?? 'Add Item' }}
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.donnees') ?? 'Données' }}</th>
                                <th>{{ __('messages.cirees_appreciation') ?? 'Cirées appréciation' }}</th>
                                <th class="text-center">{{ __('messages.status') ?? 'Status' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $item->donnees }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $item->cirees_appreciation }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm {{ $item->is_active ? 'btn-success' : 'btn-secondary' }} dropdown-toggle" 
                                                    type="button" 
                                                    id="statusDropdown{{ $item->id }}" 
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                {{ $item->is_active ? __('messages.active') : __('messages.inactive') }}
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="statusDropdown{{ $item->id }}">
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#toggleStatusModal"
                                                       data-item-id="{{ $item->id }}"
                                                       data-current-status="{{ $item->is_active ? '1' : '0' }}"
                                                       data-new-status="{{ $item->is_active ? '0' : '1' }}"
                                                       data-item-name="{{ $item->donnees }}">
                                                        {{ $item->is_active ? __('messages.deactivate') : __('messages.activate') }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>  
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="{{ __('messages.edit') ?? 'Edit' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editJourneyChecklistItemModal"
                                                    data-update-url="{{ route('journeys-checklist.update', $item) }}"
                                                    data-donnees="{{ $item->donnees }}"
                                                    data-cirees="{{ $item->cirees_appreciation }}"
                                                    data-is-active="{{ $item->is_active ? 1 : 0 }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_items_found') ?? 'No items found' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($items->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $items->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Create Journey Checklist Item Modal --}}
    <div class="modal fade" id="createJourneyChecklistItemModal" tabindex="-1" aria-labelledby="createJourneyChecklistItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createJourneyChecklistItemModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.add') ?? 'Add Item' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('journeys-checklist.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_donnees" class="form-label fw-semibold">
                                {{ __('messages.donnees') ?? 'Données' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="donnees"
                                   id="create_donnees"
                                   class="form-control @error('donnees') is-invalid @enderror"
                                   value="{{ old('donnees') }}"
                                   required>
                            @error('donnees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_cirees" class="form-label fw-semibold">
                                {{ __('messages.cirees_appreciation') ?? 'Cirées appréciation' }} <span class="text-danger">*</span>
                            </label>
                            <textarea name="cirees_appreciation"
                                      id="create_cirees"
                                      rows="3"
                                      class="form-control @error('cirees_appreciation') is-invalid @enderror"
                                      required>{{ old('cirees_appreciation') }}</textarea>
                            @error('cirees_appreciation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="create_is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="create_is_active">
                                {{ __('messages.active') ?? 'Active' }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-save me-1"></i>
                            {{ __('messages.create') ?? 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Journey Checklist Item Modal --}}
    <div class="modal fade" id="editJourneyChecklistItemModal" tabindex="-1" aria-labelledby="editJourneyChecklistItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJourneyChecklistItemModalLabel">
                        <i class="bi bi-pencil me-2 text-primary"></i>
                        {{ __('messages.edit') ?? 'Edit Item' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editJourneyChecklistItemForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_donnees" class="form-label fw-semibold">
                                {{ __('messages.donnees') ?? 'Données' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="donnees"
                                   id="edit_donnees"
                                   class="form-control @error('donnees') is-invalid @enderror"
                                   required>
                            @error('donnees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_cirees" class="form-label fw-semibold">
                                {{ __('messages.cirees_appreciation') ?? 'Cirées appréciation' }} <span class="text-danger">*</span>
                            </label>
                            <textarea name="cirees_appreciation"
                                      id="edit_cirees"
                                      rows="3"
                                      class="form-control @error('cirees_appreciation') is-invalid @enderror"
                                      required></textarea>
                            @error('cirees_appreciation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="edit_is_active"
                                   name="is_active"
                                   value="1">
                            <label class="form-check-label" for="edit_is_active">
                                {{ __('messages.active') ?? 'Active' }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-save me-1"></i>
                            {{ __('messages.update') ?? 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toggle Status Confirmation Modal --}}
    <div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleStatusModalLabel">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        {{ __('messages.confirm_status_change') ?? 'Confirm Status Change' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="toggleStatusMessage">
                        {{ __('messages.confirm_status_change_message') ?? 'Are you sure you want to change the status of this checklist item?' }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmToggleStatus">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize when DOM is ready
            function initJourneyChecklist() {
                var editModal = document.getElementById('editJourneyChecklistItemModal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        var updateUrl = button.getAttribute('data-update-url');
                        var donnees = button.getAttribute('data-donnees') || '';
                        var cirees = button.getAttribute('data-cirees') || '';
                        var isActive = button.getAttribute('data-is-active') === '1';

                        var form = document.getElementById('editJourneyChecklistItemForm');
                        if (form && updateUrl) {
                            form.action = updateUrl;
                        }

                        var donneesInput = document.getElementById('edit_donnees');
                        var cireesInput = document.getElementById('edit_cirees');
                        var activeInput = document.getElementById('edit_is_active');

                        if (donneesInput) donneesInput.value = donnees;
                        if (cireesInput) cireesInput.value = cirees;
                        if (activeInput) activeInput.checked = isActive;
                    });
                }

                // Helper function to close modal
                function closeToggleStatusModal() {
                    var modalElement = document.getElementById('toggleStatusModal');
                    if (!modalElement) return;

                    // Try Bootstrap 5 API first
                    if (window.bootstrap && window.bootstrap.Modal) {
                        var modal = window.bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                            return;
                        }
                    }

                    // Fallback: manually close modal
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    modalElement.setAttribute('aria-hidden', 'true');
                    modalElement.removeAttribute('aria-modal');
                    document.body.classList.remove('modal-open');
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }

                // Handle status toggle modal
                var toggleStatusModal = document.getElementById('toggleStatusModal');
                var confirmToggleBtn = document.getElementById('confirmToggleStatus');
                var currentToggleItem = null;

                if (toggleStatusModal) {
                    toggleStatusModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        currentToggleItem = {
                            id: button.getAttribute('data-item-id'),
                            currentStatus: button.getAttribute('data-current-status'),
                            newStatus: button.getAttribute('data-new-status'),
                            name: button.getAttribute('data-item-name')
                        };

                        var action = currentToggleItem.newStatus === '1' 
                            ? '{{ __('messages.activate') ?? 'activate' }}' 
                            : '{{ __('messages.deactivate') ?? 'deactivate' }}';
                        
                        var message = '{{ __('messages.confirm_status_change_message') ?? 'Are you sure you want to' }} ' + action + 
                            ' "{{ __('messages.this_item') ?? 'this item' }}"?';
                        
                        var messageEl = document.getElementById('toggleStatusMessage');
                        if (messageEl) {
                            messageEl.textContent = message;
                        }
                    });
                }

                if (confirmToggleBtn) {
                    confirmToggleBtn.addEventListener('click', function() {
                        if (!currentToggleItem) return;

                        var url = '{{ url('/journeys-checklist') }}/' + currentToggleItem.id + '/toggle-status';
                        var formData = new FormData();
                        formData.append('is_active', currentToggleItem.newStatus);
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Keep modal open; page refresh will clear it
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || '{{ __('messages.error_updating_journey_checklist_item') ?? 'Error updating status' }}');
                            }
                        })
                        .catch(error => {
                            // Keep modal open and show error
                            alert('{{ __('messages.error_updating_journey_checklist_item') ?? 'Error updating status' }}');
                        });
                    });
                }
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initJourneyChecklist);
            } else {
                initJourneyChecklist();
            }
        </script>
    @endpush
</x-app-layout>


