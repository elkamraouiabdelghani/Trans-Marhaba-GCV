<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-collection me-2 text-primary"></i>
                        {{ __('messages.formation_categories_title') }}
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="fcSearch" placeholder="{{ __('messages.search_by_name_or_code') }}" onkeyup="filterCategories()">
                        </div>
                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.add_formation_category') }}
                        </button>
                    </div>
                </div>
            </div>

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
                    <table class="table table-hover mb-0 align-middle" id="categoriesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.formation_category_name') }}</th>
                                <th>{{ __('messages.formation_category_code') }}</th>
                                <th class="text-end pe-3">{{ __('messages.formation_category_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="ps-3">
                                        <strong>{{ $category->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $category->code }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="{{ __('messages.edit') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCategoryModal"
                                                    data-id="{{ $category->id }}"
                                                    data-name="{{ $category->name }}"
                                                    data-code="{{ $category->code }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            {{-- <form action="{{ route('formation-categories.destroy', $category) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  id="delete-form-{{ $category->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('messages.delete') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmDeleteModal"
                                                        data-form-id="delete-form-{{ $category->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form> --}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_formation_categories_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel">{{ __('messages.add_formation_category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('formation-categories.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="modal_action" value="create">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="createCategoryName" class="form-label">{{ __('messages.formation_category_name') }}</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="createCategoryName"
                                       name="name"
                                       value="{{ old('modal_action') === 'create' ? old('name') : '' }}"
                                       required>
                                @if(old('modal_action') === 'create')
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="createCategoryCode" class="form-label">{{ __('messages.formation_category_code') }}</label>
                                <input type="text"
                                       class="form-control @error('code') is-invalid @enderror"
                                       id="createCategoryCode"
                                       name="code"
                                       value="{{ old('modal_action') === 'create' ? old('code') : '' }}"
                                       required>
                                @if(old('modal_action') === 'create')
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">{{ __('messages.edit_formation_category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" id="editCategoryForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="modal_action" value="edit">
                        <input type="hidden" name="category_id" id="editCategoryId" value="{{ old('modal_action') === 'edit' ? old('category_id') : '' }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editCategoryName" class="form-label">{{ __('messages.formation_category_name') }}</label>
                                <input type="text"
                                       class="form-control @if(old('modal_action') === 'edit') @error('name') is-invalid @enderror @endif"
                                       id="editCategoryName"
                                       name="name"
                                       value="{{ old('modal_action') === 'edit' ? old('name') : '' }}"
                                       required>
                                @if(old('modal_action') === 'edit')
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="editCategoryCode" class="form-label">{{ __('messages.formation_category_code') }}</label>
                                <input type="text"
                                       class="form-control @if(old('modal_action') === 'edit') @error('code') is-invalid @enderror @endif"
                                       id="editCategoryCode"
                                       name="code"
                                       value="{{ old('modal_action') === 'edit' ? old('code') : '' }}"
                                       required>
                                @if(old('modal_action') === 'edit')
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="confirmDeleteModalLabel">{{ __('messages.confirm_delete') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ __('messages.confirm_delete_category_message') }}
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
            function filterCategories() {
                const input = document.getElementById('fcSearch');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('categoriesTable');
                const rows = table.getElementsByTagName('tr');

                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    if (!cells.length) continue;
                    const name = (cells[0].innerText || '').toLowerCase();
                    const code = (cells[1].innerText || '').toLowerCase();
                    const match = name.includes(filter) || code.includes(filter);
                    rows[i].style.display = match ? '' : 'none';
                }
            }

            (function() {
                const modal = document.getElementById('confirmDeleteModal');
                const confirmBtn = document.getElementById('confirmDeleteBtn');
                let targetFormId = null;

                modal?.addEventListener('show.bs.modal', function (event) {
                    const triggerButton = event.relatedTarget;
                    targetFormId = triggerButton ? triggerButton.getAttribute('data-form-id') : null;
                });

                confirmBtn?.addEventListener('click', function () {
                    if (!targetFormId) return;
                    const form = document.getElementById(targetFormId);
                    if (form) form.submit();
                });

                const editModalEl = document.getElementById('editCategoryModal');
                const editForm = document.getElementById('editCategoryForm');
                const editNameInput = document.getElementById('editCategoryName');
                const editCodeInput = document.getElementById('editCategoryCode');
                const editIdInput = document.getElementById('editCategoryId');
                const updateRouteTemplate = @js(route('formation-categories.update', ':id'));

                if (editModalEl && editForm && editNameInput && editCodeInput && editIdInput) {
                    editModalEl.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        if (!button) {
                            return;
                        }
                        const categoryId = button.getAttribute('data-id');
                        const categoryName = button.getAttribute('data-name');
                        const categoryCode = button.getAttribute('data-code');

                        editForm.action = updateRouteTemplate.replace(':id', categoryId);
                        editNameInput.value = categoryName ?? '';
                        editCodeInput.value = categoryCode ?? '';
                        editIdInput.value = categoryId ?? '';

                        // Clear previous validation state if any
                        editNameInput.classList.remove('is-invalid');
                        editCodeInput.classList.remove('is-invalid');
                        const invalidFeedbacks = editModalEl.querySelectorAll('.invalid-feedback');
                        invalidFeedbacks.forEach(el => el.remove());
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const showModal = @js(session('showCategoryModal') ?? old('modal_action'));
                    const createModalEl = document.getElementById('createCategoryModal');

                    if (showModal === 'create' && createModalEl) {
                        const createModal = bootstrap.Modal.getOrCreateInstance(createModalEl);
                        createModal.show();
                    } else if (showModal === 'edit' && editModalEl && editForm && editNameInput && editCodeInput && editIdInput) {
                        const persistedId = @js(optional(session('editCategory'))['id'] ?? old('category_id'));
                        if (persistedId) {
                            editForm.action = updateRouteTemplate.replace(':id', persistedId);
                        }
                        const persistedName = @js(optional(session('editCategory'))['name'] ?? old('name'));
                        const persistedCode = @js(optional(session('editCategory'))['code'] ?? old('code'));
                        if (persistedName) editNameInput.value = persistedName;
                        if (persistedCode) editCodeInput.value = persistedCode;
                        if (persistedId) editIdInput.value = persistedId;
                        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                        editModal.show();
                    }
                });
            })();
        </script>
        @endpush
    </div>
</x-app-layout>

