@php
    $formContext = old('form_context');
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        @php
            $isCreateContext = $formContext === 'create_changement_type';
        @endphp

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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ __('messages.form_fix_errors') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 px-4 py-2">
                <div>
                    <h1 class="h4 mb-1">{{ __('messages.changement_types_index_title') }}</h1>
                    <p class="text-muted mb-0">{{ __('messages.changement_types_index_subtitle') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createChangementTypeModal">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('messages.changement_types_new_button') }}
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('messages.name') }}</th>
                                <th>{{ __('messages.code') }}</th>
                                <th>{{ __('messages.changement_types_table_primary') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.changement_types_table_updated') }}</th>
                                <th class="text-end pe-4">{{ __('messages.changement_types_table_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($changementTypes as $type)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $type->name }}</div>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($type->description ?? '', 80) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $type->code }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $type->principale_cretaires_count }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($type->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success">{{ __('messages.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            {{ $type->updated_at?->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('changement-types.show', $type) }}" class="btn btn-outline-secondary" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-warning"
                                                    title="{{ __('messages.edit') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editChangementTypeModal-{{ $type->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    title="{{ __('messages.delete') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteChangementTypeModal-{{ $type->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.changement_types_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($changementTypes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            {{ __('messages.changement_types_records', ['count' => $changementTypes->total()]) }}
                        </div>
                        {{ $changementTypes->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createChangementTypeModal" tabindex="-1" aria-labelledby="createChangementTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createChangementTypeModalLabel">{{ __('messages.changement_types_create_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('changement-types.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_context" value="create_changement_type">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create-name" class="form-label fw-semibold">{{ __('messages.name') }}</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="create-name"
                                    class="form-control @if($isCreateContext && $errors->has('name')) is-invalid @endif"
                                    value="{{ $isCreateContext ? old('name') : '' }}"
                                    required
                                >
                                @if($isCreateContext)
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="create-code" class="form-label fw-semibold">{{ __('messages.code') }}</label>
                                <input
                                    type="text"
                                    name="code"
                                    id="create-code"
                                    class="form-control @if($isCreateContext && $errors->has('code')) is-invalid @endif"
                                    value="{{ $isCreateContext ? old('code') : '' }}"
                                    required
                                >
                                @if($isCreateContext)
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-12">
                                <label for="create-description" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                <textarea
                                    name="description"
                                    id="create-description"
                                    rows="4"
                                    class="form-control @if($isCreateContext && $errors->has('description')) is-invalid @endif"
                                >{{ $isCreateContext ? old('description') : '' }}</textarea>
                                @if($isCreateContext)
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        id="create-is-active"
                                        class="form-check-input"
                                        value="1"
                                        {{ $isCreateContext ? (old('is_active', true) ? 'checked' : '') : 'checked' }}
                                    >
                                    <label class="form-check-label fw-semibold" for="create-is-active">
                                        {{ __('messages.active') }}
                                    </label>
                                </div>
                                @if($isCreateContext)
                                    @error('is_active')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-save me-1"></i> {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach ($changementTypes as $type)
        @php
            $editContext = "edit_changement_type_{$type->id}";
            $isEditContext = $formContext === $editContext;
        @endphp
        <div class="modal fade" id="editChangementTypeModal-{{ $type->id }}" tabindex="-1" aria-labelledby="editChangementTypeModalLabel-{{ $type->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editChangementTypeModalLabel-{{ $type->id }}">{{ __('messages.changement_types_edit_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('changement-types.update', $type) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" value="edit_changement_type_{{ $type->id }}">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="edit-name-{{ $type->id }}" class="form-label fw-semibold">{{ __('messages.name') }}</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="edit-name-{{ $type->id }}"
                                        class="form-control @if($isEditContext && $errors->has('name')) is-invalid @endif"
                                        value="{{ $isEditContext ? old('name') : $type->name }}"
                                        required
                                    >
                                    @if($isEditContext)
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="edit-code-{{ $type->id }}" class="form-label fw-semibold">{{ __('messages.code') }}</label>
                                    <input
                                        type="text"
                                        name="code"
                                        id="edit-code-{{ $type->id }}"
                                        class="form-control @if($isEditContext && $errors->has('code')) is-invalid @endif"
                                        value="{{ $isEditContext ? old('code') : $type->code }}"
                                        required
                                    >
                                    @if($isEditContext)
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label for="edit-description-{{ $type->id }}" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                    <textarea
                                        name="description"
                                        id="edit-description-{{ $type->id }}"
                                        rows="4"
                                        class="form-control @if($isEditContext && $errors->has('description')) is-invalid @endif"
                                    >{{ $isEditContext ? old('description') : $type->description }}</textarea>
                                    @if($isEditContext)
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            id="edit-is-active-{{ $type->id }}"
                                            class="form-check-input"
                                            value="1"
                                            {{ $isEditContext ? (old('is_active', $type->is_active) ? 'checked' : '') : ($type->is_active ? 'checked' : '') }}
                                        >
                                        <label class="form-check-label fw-semibold" for="edit-is-active-{{ $type->id }}">
                                            {{ __('messages.active') }}
                                        </label>
                                    </div>
                                    @if($isEditContext)
                                        @error('is_active')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i> {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteChangementTypeModal-{{ $type->id }}" tabindex="-1" aria-labelledby="deleteChangementTypeModalLabel-{{ $type->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 bg-danger">
                        <h5 class="modal-title text-white" id="deleteChangementTypeModalLabel-{{ $type->id }}">{{ __('messages.changement_types_delete_title') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center mb-0">
                            {{ __('messages.changement_types_delete_message', ['name' => $type->name]) }}
                        </p>
                        @if($type->principale_cretaires_count > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ __('messages.changement_types_delete_warning', ['count' => $type->principale_cretaires_count]) }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <form action="{{ route('changement-types.destroy', $type) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> {{ __('messages.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const context = @json($formContext);

        if (!context || typeof bootstrap === 'undefined') {
            return;
        }

        if (context === 'create_changement_type') {
            const modalEl = document.getElementById('createChangementTypeModal');
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        } else if (context.startsWith('edit_changement_type_')) {
            const id = context.replace('edit_changement_type_', '');
            const modalEl = document.getElementById(`editChangementTypeModal-${id}`);
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        }
    });
</script>
@endpush

