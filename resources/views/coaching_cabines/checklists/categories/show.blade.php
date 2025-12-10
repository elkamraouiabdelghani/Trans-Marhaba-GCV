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

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <h4 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-list-check me-2 text-primary"></i>
                    {{ $category->name }}
                </h4>
                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $category->is_active ? (__('messages.active') ?? 'Active') : (__('messages.inactive') ?? 'Inactive') }}
                </span>
            </div>
            <a href="{{ route('coaching.checklists.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back_to_list') ?? 'Back to list' }}
            </a>
        </div>

        <div class="row">
            {{-- Main Section: Items Table --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-list-ul me-2 text-primary"></i>
                            {{ __('messages.items') ?? 'Items' }}
                        </h6>
                        <button type="button" 
                                class="btn btn-dark btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#createChecklistItemModal">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.add') ?? 'Add Item' }}
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ __('messages.description') ?? 'Description' }}</th>
                                        <th class="text-center">{{ __('messages.score') ?? 'Score' }}</th>
                                        <th>{{ __('messages.status') ?? 'Status' }}</th>
                                        <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($category->items as $item)
                                        <tr>
                                            <td class="ps-3">{{ $item->label }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $item->score ?? 1 }}</span>
                                            </td>
                                            <td>
                                                @if($item->is_active)
                                                    <span class="badge bg-success">{{ __('messages.active') ?? 'Active' }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('messages.inactive') ?? 'Inactive' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editChecklistItemModal"
                                                            data-update-url="{{ route('coaching.checklists.items.update', $item) }}"
                                                            data-label="{{ $item->label }}"
                                                            data-score="{{ $item->score ?? 1 }}"
                                                            data-is-active="{{ $item->is_active ? '1' : '0' }}"
                                                            data-item-id="{{ $item->id }}"
                                                            title="{{ __('messages.edit') ?? 'Edit' }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteChecklistItemModal"
                                                            data-delete-url="{{ route('coaching.checklists.items.destroy', $item) }}"
                                                            data-item-label="{{ $item->label }}"
                                                            title="{{ __('messages.delete') ?? 'Delete' }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                {{ __('messages.no_items_found') ?? 'No items found for this category' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Section --}}
            <div class="col-lg-4">
                {{-- Quick Actions --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-lightning-charge me-2 text-primary"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" 
                                    class="btn btn-warning btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editChecklistCategoryModal"
                                    data-update-url="{{ route('coaching.checklists.categories.update', $category) }}"
                                    data-name="{{ $category->name }}"
                                    data-is-active="{{ $category->is_active ? '1' : '0' }}">
                                <i class="bi bi-pencil me-1"></i>
                                {{ __('messages.edit') ?? 'Edit Category' }}
                            </button>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="toggleCategoryStatus" 
                                   {{ $category->is_active ? 'checked' : '' }}
                                   data-bs-toggle="modal"
                                   data-bs-target="#toggleCategoryStatusModal"
                                   data-category-id="{{ $category->id }}">
                            <label class="form-check-label" for="toggleCategoryStatus">
                                @if($category->is_active)
                                    <span class="text-success">{{ __('messages.active') ?? 'Active' }}</span>
                                @else
                                    <span class="text-secondary">{{ __('messages.inactive') ?? 'Inactive' }}</span>
                                @endif
                            </label>
                        </div>
                        <hr class="my-2">
                        <button type="button" 
                                class="btn btn-outline-danger btn-sm w-100" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteChecklistCategoryModal"
                                data-category-id="{{ $category->id }}"
                                data-category-name="{{ $category->name }}"
                                data-has-items="{{ $category->items->count() > 0 ? '1' : '0' }}">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete Category' }}
                        </button>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
                            {{ __('messages.timeline') ?? 'Timeline' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline-item mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-plus-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ __('messages.created_at') ?? 'Created At' }}</div>
                                    <div class="text-muted small">
                                        {{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-pencil-fill text-primary me-2 mt-1"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ __('messages.updated_at') ?? 'Updated At' }}</div>
                                    <div class="text-muted small">
                                        {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Checklist Item Modal --}}
    <div class="modal fade" id="createChecklistItemModal" tabindex="-1" aria-labelledby="createChecklistItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createChecklistItemModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.add') ?? 'Add Item' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coaching.checklists.items.store') }}">
                    @csrf
                    <input type="hidden" name="coaching_checklist_category_id" value="{{ $category->id }}">
                    <input type="hidden" name="form_context" value="create_item_category_show">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_item_label" class="form-label fw-semibold">
                                {{ __('messages.description') ?? 'Description' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="label"
                                   id="create_item_label"
                                   class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label') }}"
                                   required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_item_score" class="form-label fw-semibold">
                                {{ __('messages.score') ?? 'Score' }} <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="score"
                                   id="create_item_score"
                                   class="form-control @error('score') is-invalid @enderror"
                                   value="{{ old('score', 1) }}"
                                   min="1"
                                   required>
                            @error('score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.score_range_hint') ?? 'Score must be at least 1' }}</small>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="create_item_is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="create_item_is_active">
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

    {{-- Edit Checklist Item Modal --}}
    <div class="modal fade" id="editChecklistItemModal" tabindex="-1" aria-labelledby="editChecklistItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChecklistItemModalLabel">
                        <i class="bi bi-pencil me-2 text-primary"></i>
                        {{ __('messages.edit') ?? 'Edit Item' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editChecklistItemForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="coaching_checklist_category_id" value="{{ $category->id }}">
                    <input type="hidden" name="form_context" value="edit_item_category_show">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_item_label" class="form-label fw-semibold">
                                {{ __('messages.description') ?? 'Description' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="label"
                                   id="edit_item_label"
                                   class="form-control @error('label') is-invalid @enderror"
                                   required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_item_score" class="form-label fw-semibold">
                                {{ __('messages.score') ?? 'Score' }} <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="score"
                                   id="edit_item_score"
                                   class="form-control @error('score') is-invalid @enderror"
                                   min="1"
                                   required>
                            @error('score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.score_range_hint') ?? 'Score must be at least 1' }}</small>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="edit_item_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_item_is_active">
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

    {{-- Delete Checklist Item Modal --}}
    <div class="modal fade" id="deleteChecklistItemModal" tabindex="-1" aria-labelledby="deleteChecklistItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteChecklistItemModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.confirm_delete') ?? 'Confirm Deletion' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="deleteChecklistItemForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>{{ __('messages.warning') ?? 'Warning' }}!</strong>
                            <p class="mb-0 mt-2" id="deleteItemMessage">
                                {{ __('messages.confirm_delete_item') ?? 'Are you sure you want to delete this item? This action cannot be undone.' }}
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteItemBtn">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toggle Category Status Confirmation Modal --}}
    <div class="modal fade" id="toggleCategoryStatusModal" tabindex="-1" aria-labelledby="toggleCategoryStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleCategoryStatusModalLabel">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        {{ __('messages.confirm_status_change') ?? 'Confirm Status Change' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="toggleCategoryStatusMessage">
                        {{ __('messages.confirm_toggle_category_status') ?? 'Are you sure you want to change the status of this category?' }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmToggleCategoryStatus">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Checklist Category Confirmation Modal --}}
    <div class="modal fade" id="deleteChecklistCategoryModal" tabindex="-1" aria-labelledby="deleteChecklistCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteChecklistCategoryModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.confirm_delete') ?? 'Confirm Deletion' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="deleteChecklistCategoryForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>{{ __('messages.warning') ?? 'Warning' }}!</strong>
                            <p class="mb-0 mt-2" id="deleteCategoryMessage">
                                {{ __('messages.confirm_delete_category') ?? 'Are you sure you want to delete this category? This action cannot be undone.' }}
                            </p>
                            <p class="mb-0 mt-2" id="deleteCategoryItemsWarning" style="display: none;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>{{ __('messages.note') ?? 'Note' }}:</strong> 
                                {{ __('messages.all_items_will_be_deleted') ?? 'All items in this category will also be deleted.' }}
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteCategoryBtn">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Checklist Category Modal --}}
    <div class="modal fade" id="editChecklistCategoryModal" tabindex="-1" aria-labelledby="editChecklistCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChecklistCategoryModalLabel">
                        <i class="bi bi-pencil me-2 text-primary"></i>
                        {{ __('messages.edit') ?? 'Edit Category' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editChecklistCategoryForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit_category_show">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_category_name" class="form-label fw-semibold">
                                {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="edit_category_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="edit_category_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_category_is_active">
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Edit Category Modal
                var editCategoryModal = document.getElementById('editChecklistCategoryModal');
                if (editCategoryModal) {
                    editCategoryModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        var updateUrl = button.getAttribute('data-update-url');
                        var name = button.getAttribute('data-name') || '';
                        var isActive = button.getAttribute('data-is-active') === '1';

                        var form = document.getElementById('editChecklistCategoryForm');
                        if (form && updateUrl) {
                            form.action = updateUrl;
                        }

                        var nameInput = document.getElementById('edit_category_name');
                        var activeInput = document.getElementById('edit_category_is_active');

                        if (nameInput) {
                            nameInput.value = name;
                        }

                        if (activeInput) {
                            activeInput.checked = isActive;
                        }
                    });
                }

                // Edit Item Modal
                var editItemModal = document.getElementById('editChecklistItemModal');
                if (editItemModal) {
                    editItemModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        var updateUrl = button.getAttribute('data-update-url');
                        var label = button.getAttribute('data-label') || '';
                        var score = button.getAttribute('data-score') || '1';
                        var isActive = button.getAttribute('data-is-active') === '1';

                        var form = document.getElementById('editChecklistItemForm');
                        if (form && updateUrl) {
                            form.action = updateUrl;
                        }

                        var labelInput = document.getElementById('edit_item_label');
                        var scoreInput = document.getElementById('edit_item_score');
                        var activeInput = document.getElementById('edit_item_is_active');

                        if (labelInput) {
                            labelInput.value = label;
                        }

                        if (scoreInput) {
                            scoreInput.value = score;
                        }

                        if (activeInput) {
                            activeInput.checked = isActive;
                        }
                    });
                }
            });

            // Toggle Category Status Modal
            var toggleCategoryStatusModal = document.getElementById('toggleCategoryStatusModal');
            var toggleCategoryStatusSwitch = document.getElementById('toggleCategoryStatus');
            var confirmToggleBtn = document.getElementById('confirmToggleCategoryStatus');
            var currentCategoryId = null;
            var currentIsActive = null;

            if (toggleCategoryStatusModal && toggleCategoryStatusSwitch) {
                toggleCategoryStatusModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    currentCategoryId = button.getAttribute('data-category-id');
                    currentIsActive = toggleCategoryStatusSwitch.checked;
                    
                    // Update message based on current state
                    var messageEl = document.getElementById('toggleCategoryStatusMessage');
                    if (messageEl) {
                        if (currentIsActive) {
                            messageEl.textContent = '{{ __('messages.confirm_deactivate_category') ?? 'Are you sure you want to deactivate this category?' }}';
                        } else {
                            messageEl.textContent = '{{ __('messages.confirm_activate_category') ?? 'Are you sure you want to activate this category?' }}';
                        }
                    }
                });

                // Reset switch if modal is dismissed
                toggleCategoryStatusModal.addEventListener('hidden.bs.modal', function () {
                    toggleCategoryStatusSwitch.checked = !currentIsActive;
                });

                // Handle confirmation
                if (confirmToggleBtn) {
                    confirmToggleBtn.addEventListener('click', function () {
                        if (currentCategoryId !== null) {
                            var newStatus = toggleCategoryStatusSwitch.checked;
                            performToggleCategoryStatus(currentCategoryId, newStatus);
                        }
                    });
                }
            }

            // Delete Item Modal
            var deleteItemModal = document.getElementById('deleteChecklistItemModal');
            var deleteItemForm = document.getElementById('deleteChecklistItemForm');
            var deleteItemMessage = document.getElementById('deleteItemMessage');

            if (deleteItemModal) {
                deleteItemModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var deleteUrl = button.getAttribute('data-delete-url');
                    var itemLabel = button.getAttribute('data-item-label') || '';

                    if (deleteItemForm && deleteUrl) {
                        deleteItemForm.action = deleteUrl;
                    }

                    if (deleteItemMessage && itemLabel) {
                        deleteItemMessage.textContent = '{{ __('messages.confirm_delete_item_name') ?? 'Are you sure you want to delete the item' }} \"' + itemLabel + '\"? {{ __('messages.action_cannot_be_undone') ?? 'This action cannot be undone.' }}';
                    }
                });
            }

            // Delete Category Modal
            var deleteCategoryModal = document.getElementById('deleteChecklistCategoryModal');
            var deleteCategoryForm = document.getElementById('deleteChecklistCategoryForm');
            var deleteCategoryMessage = document.getElementById('deleteCategoryMessage');
            var deleteCategoryItemsWarning = document.getElementById('deleteCategoryItemsWarning');
            var confirmDeleteBtn = document.getElementById('confirmDeleteCategoryBtn');

            if (deleteCategoryModal) {
                deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var categoryId = button.getAttribute('data-category-id');
                    var categoryName = button.getAttribute('data-category-name') || '';
                    var hasItems = button.getAttribute('data-has-items') === '1';

                    // Set form action
                    if (deleteCategoryForm && categoryId) {
                        deleteCategoryForm.action = `/coaching-checklist-categories/${categoryId}`;
                    }

                    // Update message with category name
                    if (deleteCategoryMessage && categoryName) {
                        deleteCategoryMessage.textContent = '{{ __('messages.confirm_delete_category_name') ?? 'Are you sure you want to delete the category' }} "' + categoryName + '"? {{ __('messages.action_cannot_be_undone') ?? 'This action cannot be undone.' }}';
                    }

                    // Show warning if category has items
                    if (hasItems && deleteCategoryItemsWarning) {
                        deleteCategoryItemsWarning.style.display = 'block';
                    } else if (deleteCategoryItemsWarning) {
                        deleteCategoryItemsWarning.style.display = 'none';
                    }

                    // Always enable delete button (items will be deleted automatically)
                    if (confirmDeleteBtn) {
                        confirmDeleteBtn.disabled = false;
                    }
                });
            }

            // Helper function to close modal
            function closeModal(modalId) {
                var modalElement = document.getElementById(modalId);
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
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            function performToggleCategoryStatus(categoryId, isActive) {
                fetch(`/coaching-checklist-categories/${categoryId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_active: isActive
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Error updating category status');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        closeModal('toggleCategoryStatusModal');
                        location.reload();
                    } else {
                        alert(data.message || '{{ __('messages.error_updating_checklist_category') ?? 'Error updating category status' }}');
                        toggleCategoryStatusSwitch.checked = !isActive;
                        closeModal('toggleCategoryStatusModal');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || '{{ __('messages.error_updating_checklist_category') ?? 'Error updating category status' }}');
                    toggleCategoryStatusSwitch.checked = !isActive;
                    closeModal('toggleCategoryStatusModal');
                });
            }
        </script>
    @endpush
</x-app-layout>

