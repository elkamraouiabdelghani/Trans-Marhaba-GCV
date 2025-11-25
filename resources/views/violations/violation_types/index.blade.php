@php
    $formContext = old('form_context');
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        @php
            $isCreateContext = $formContext === 'create_violation_type';
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
                    <h1 class="h4 mb-1">{{ __('messages.violation_types') }}</h1>
                    <p class="text-muted mb-0">{{ __('messages.manage_violation_types') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createViolationTypeModal">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('messages.add') }} {{ __('messages.violation_type') }}
                    </button>
                    {{-- violations list page link --}}
                    <a href="{{ route('violations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-1"></i> {{ __('messages.list') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('messages.name') }}</th>
                                <th>{{ __('messages.violations') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.updated_at') }}</th>
                                <th class="text-end pe-4">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($violationTypes as $type)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $type->name }}</div>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($type->description ?? '', 80) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $type->violations_count }}
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
                                            <button type="button"
                                                    class="btn btn-outline-warning"
                                                    title="{{ __('messages.edit') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editViolationTypeModal-{{ $type->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    title="{{ __('messages.delete') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteViolationTypeModal-{{ $type->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_violation_types') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($violationTypes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            {{ __('messages.showing') }} {{ $violationTypes->firstItem() }} {{ __('messages.to') }} {{ $violationTypes->lastItem() }} {{ __('messages.of') }} {{ $violationTypes->total() }}
                        </div>
                        {{ $violationTypes->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createViolationTypeModal" tabindex="-1" aria-labelledby="createViolationTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createViolationTypeModalLabel">{{ __('messages.add') }} {{ __('messages.violation_type') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('violation-types.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_context" value="create_violation_type">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create-name" class="form-label fw-semibold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
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
    @foreach ($violationTypes as $type)
        @php
            $editContext = "edit_violation_type_{$type->id}";
            $isEditContext = $formContext === $editContext;
        @endphp
        <div class="modal fade" id="editViolationTypeModal-{{ $type->id }}" tabindex="-1" aria-labelledby="editViolationTypeModalLabel-{{ $type->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editViolationTypeModalLabel-{{ $type->id }}">{{ __('messages.edit') }} {{ __('messages.violation_type') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('violation-types.update', $type) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" value="edit_violation_type_{{ $type->id }}">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="edit-name-{{ $type->id }}" class="form-label fw-semibold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
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
        <div class="modal fade" id="deleteViolationTypeModal-{{ $type->id }}" tabindex="-1" aria-labelledby="deleteViolationTypeModalLabel-{{ $type->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 bg-danger">
                        <h5 class="modal-title text-white" id="deleteViolationTypeModalLabel-{{ $type->id }}">{{ __('messages.delete') }} {{ __('messages.violation_type') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center mb-0">
                            {{ __('messages.confirm_delete_violation_type', ['name' => $type->name]) }}
                        </p>
                        @if($type->violations_count > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ __('messages.violation_type_delete_error_has_violations') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <form action="{{ route('violation-types.destroy', $type) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" @if($type->violations_count > 0) disabled @endif>
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

        if (context === 'create_violation_type') {
            const modalEl = document.getElementById('createViolationTypeModal');
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        } else if (context.startsWith('edit_violation_type_')) {
            const id = context.replace('edit_violation_type_', '');
            const modalEl = document.getElementById(`editViolationTypeModal-${id}`);
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        }
    });
</script>
@endpush

