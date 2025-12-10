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
                    {{ __('messages.coaching_checklist_categories') ?? 'Coaching Checklist Categories' }}
                </h5>
                <button type="button"
                        class="btn btn-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createChecklistCategoryModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ __('messages.add') ?? 'Add Category' }}
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.name') ?? 'Name' }}</th>
                                <th>{{ __('messages.items') ?? 'Items' }}</th>
                                <th>{{ __('messages.status') ?? 'Status' }}</th>
                                <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $category->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $category->items_count }} {{ __('messages.items') ?? 'items' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge bg-success">{{ __('messages.active') ?? 'Active' }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.inactive') ?? 'Inactive' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $category->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('coaching.checklists.categories.show', $category) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="{{ __('messages.view') ?? 'View' }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button"
                                               class="btn btn-sm btn-outline-warning"
                                               title="{{ __('messages.edit') ?? 'Edit' }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#editChecklistCategoryModal"
                                               data-update-url="{{ route('coaching.checklists.categories.update', $category) }}"
                                               data-name="{{ $category->name }}"
                                               data-is-active="{{ $category->is_active ? 1 : 0 }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button"
                                               class="btn btn-sm btn-outline-danger"
                                               title="{{ __('messages.delete') ?? 'Delete' }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#deleteChecklistCategoryModal"
                                               data-delete-url="{{ route('coaching.checklists.categories.destroy', $category) }}"
                                               data-category-name="{{ $category->name }}"
                                               data-has-items="{{ $category->items_count > 0 ? '1' : '0' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_categories_found') ?? 'No categories found' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                </div>
            @endif
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

    {{-- Create Checklist Category Modal --}}
    <div class="modal fade" id="createChecklistCategoryModal" tabindex="-1" aria-labelledby="createChecklistCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createChecklistCategoryModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.add') ?? 'Add Category' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coaching.checklists.categories.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_category_name" class="form-label fw-semibold">
                                {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="create_category_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="create_category_is_active" name="is_active"
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="create_category_is_active">
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
                    <input type="hidden" name="form_context" value="edit_category_index">
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
                var editModal = document.getElementById('editChecklistCategoryModal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
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

                var deleteCategoryModal = document.getElementById('deleteChecklistCategoryModal');
                var deleteCategoryForm = document.getElementById('deleteChecklistCategoryForm');
                var deleteCategoryMessage = document.getElementById('deleteCategoryMessage');
                var deleteCategoryItemsWarning = document.getElementById('deleteCategoryItemsWarning');
                var confirmDeleteBtn = document.getElementById('confirmDeleteCategoryBtn');

                if (deleteCategoryModal) {
                    deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;

                        var deleteUrl = button.getAttribute('data-delete-url');
                        var categoryName = button.getAttribute('data-category-name') || '';
                        var hasItems = button.getAttribute('data-has-items') === '1';

                        if (deleteCategoryForm && deleteUrl) {
                            deleteCategoryForm.action = deleteUrl;
                        }

                        if (deleteCategoryMessage && categoryName) {
                            deleteCategoryMessage.textContent = '{{ __('messages.confirm_delete_category_name') ?? 'Are you sure you want to delete the category' }} "' + categoryName + '"? {{ __('messages.action_cannot_be_undone') ?? 'This action cannot be undone.' }}';
                        }

                        if (hasItems && deleteCategoryItemsWarning) {
                            deleteCategoryItemsWarning.style.display = 'block';
                        } else if (deleteCategoryItemsWarning) {
                            deleteCategoryItemsWarning.style.display = 'none';
                        }

                        if (confirmDeleteBtn) {
                            confirmDeleteBtn.disabled = false;
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

