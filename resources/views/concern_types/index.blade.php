<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    @php
        $isCreateMode = old('form_mode') === 'create';
        $editModeId = old('form_mode') === 'edit' ? (int) old('form_concern_type_id') : null;
        $statusClasses = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'success',
        ];
        $totalConcernTypes = $concernTypes->count();
    @endphp

    <div class="container-fluid py-4 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-list-check me-2 text-primary"></i>
                        {{ __('messages.concern_types') }}
                    </h5>
                    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createConcernTypeModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        {{ __('messages.add_concern_type') }}
                    </button>
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
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 concern-type-column">{{ __('messages.concern_type_name') }}</th>
                                <th>{{ __('messages.concern_type_status') }}</th>
                                <th>{{ __('messages.concerns') }}</th>
                                <th class="text-end pe-3">{{ __('messages.concern_type_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($concernTypes as $concernType)
                                @php
                                    $isCurrentEdit = $editModeId === $concernType->id;
                                    $editHasErrors = $errors->any() && $isCurrentEdit;
                                    $statusValue = $isCurrentEdit ? old('status', $concernType->status) : $concernType->status;
                                @endphp
                                <tr>
                                    <td class="ps-3 concern-type-column">
                                        <strong class="d-block">{{ $isCurrentEdit ? old('name', $concernType->name) : $concernType->name }}</strong>
                                        @php
                                            $description = $isCurrentEdit ? old('description', $concernType->description) : $concernType->description;
                                        @endphp
                                        <div class="text-muted small mt-1 concern-type-description">
                                            {{ $description ? Illuminate\Support\Str::limit($description, 160) : __('messages.no_description') }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = $statusClasses[$concernType->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }}">
                                            {{ $statusOptions[$concernType->status] ?? ucfirst($concernType->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $concernType->driver_concerns_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editConcernTypeModal-{{ $concernType->id }}"
                                                title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('concern-types.destroy', $concernType) }}"
                                                  method="POST"
                                                  class="d-inline-block ms-2"
                                                  onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade"
                                     id="editConcernTypeModal-{{ $concernType->id }}"
                                     tabindex="-1"
                                     aria-labelledby="editConcernTypeModalLabel-{{ $concernType->id }}"
                                     aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editConcernTypeModalLabel-{{ $concernType->id }}">
                                                    {{ __('messages.edit_concern_type') }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('concern-types.update', $concernType) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="form_mode" value="edit">
                                                <input type="hidden" name="form_concern_type_id" value="{{ $concernType->id }}">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="editConcernTypeName-{{ $concernType->id }}" class="form-label">
                                                            {{ __('messages.concern_type_name') }}
                                                        </label>
                                                        <input
                                                            type="text"
                                                            id="editConcernTypeName-{{ $concernType->id }}"
                                                            name="name"
                                                            value="{{ $isCurrentEdit ? old('name', $concernType->name) : $concernType->name }}"
                                                            class="form-control {{ $editHasErrors && $errors->has('name') ? 'is-invalid' : '' }}"
                                                            required
                                                        >
                                                        @if($editHasErrors)
                                                            @error('name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editConcernTypeDescription-{{ $concernType->id }}" class="form-label">
                                                            {{ __('messages.concern_type_description') }}
                                                        </label>
                                                        <textarea
                                                            id="editConcernTypeDescription-{{ $concernType->id }}"
                                                            name="description"
                                                            class="form-control {{ $editHasErrors && $errors->has('description') ? 'is-invalid' : '' }}"
                                                            rows="3"
                                                        >{{ $isCurrentEdit ? old('description', $concernType->description) : $concernType->description }}</textarea>
                                                        @if($editHasErrors)
                                                            @error('description')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editConcernTypeStatus-{{ $concernType->id }}" class="form-label">
                                                            {{ __('messages.concern_type_status') }}
                                                        </label>
                                                        <select
                                                            id="editConcernTypeStatus-{{ $concernType->id }}"
                                                            name="status"
                                                            class="form-select {{ $editHasErrors && $errors->has('status') ? 'is-invalid' : '' }}"
                                                            required
                                                        >
                                                            @foreach($statusOptions as $value => $label)
                                                                <option value="{{ $value }}" @selected($statusValue === $value)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($editHasErrors)
                                                            @error('status')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        {{ __('messages.cancel') }}
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ __('messages.save') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_concern_types_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-0">
                <p class="mb-0 text-muted small">
                    {{ $totalConcernTypes }} {{ Illuminate\Support\Str::plural(__('messages.concern_types'), $totalConcernTypes) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    @php
        $createStatusValue = $isCreateMode ? old('status', 'medium') : 'medium';
        $createHasErrors = $errors->any() && $isCreateMode;
    @endphp
    <div class="modal fade" id="createConcernTypeModal" tabindex="-1" aria-labelledby="createConcernTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createConcernTypeModalLabel">{{ __('messages.add_concern_type') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('concern-types.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_mode" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="createConcernTypeName" class="form-label">{{ __('messages.concern_type_name') }}</label>
                            <input
                                type="text"
                                id="createConcernTypeName"
                                name="name"
                                value="{{ $isCreateMode ? old('name') : '' }}"
                                class="form-control {{ $createHasErrors && $errors->has('name') ? 'is-invalid' : '' }}"
                                required
                            >
                            @if($createHasErrors)
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="createConcernTypeDescription" class="form-label">{{ __('messages.concern_type_description') }}</label>
                            <textarea
                                id="createConcernTypeDescription"
                                name="description"
                                class="form-control {{ $createHasErrors && $errors->has('description') ? 'is-invalid' : '' }}"
                                rows="3"
                            >{{ $isCreateMode ? old('description') : '' }}</textarea>
                            @if($createHasErrors)
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="createConcernTypeStatus" class="form-label">{{ __('messages.concern_type_status') }}</label>
                            <select
                                id="createConcernTypeStatus"
                                name="status"
                                class="form-select {{ $createHasErrors && $errors->has('status') ? 'is-invalid' : '' }}"
                                required
                            >
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($createStatusValue === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($createHasErrors)
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($isCreateMode)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = new bootstrap.Modal(document.getElementById('createConcernTypeModal'));
                modal.show();
            });
        </script>
    @endif

    @if($editModeId)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('editConcernTypeModal-{{ $editModeId }}');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        </script>
    @endif
    <style>
        .concern-type-column {
            min-width: 260px;
            max-width: 360px;
        }

        .concern-type-description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .concern-type-column {
                min-width: 220px;
                max-width: 280px;
            }
        }
    </style>
</x-app-layout>

