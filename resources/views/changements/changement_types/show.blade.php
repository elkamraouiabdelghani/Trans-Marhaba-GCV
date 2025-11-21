<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ $changementType->name }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('changement-types.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.changement_types_back_to_list') }}
                </a>
            </div>
        </div>

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

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="card-title mb-0">{{ __('messages.changement_types_primary_section_title') }}</h5>
                                <small class="text-muted">{{ __('messages.changement_types_primary_section_subtitle') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#createPrincipaleCretaireModal">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('messages.add') }}
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.sous_cretaires') }}</th>
                                        <th class="text-end">{{ __('messages.changement_types_primary_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($changementType->principaleCretaires as $principale)
                                        <tr>
                                            <td class="fw-semibold">{{ $principale->name }}</td>
                                            <td>
                                                @if ($principale->is_active)
                                                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('messages.active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ __('messages.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $principale->sousCretaires->count() }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('principale-cretaires.show', $principale) }}" class="btn btn-outline-secondary" title="{{ __('messages.view') }}">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            title="{{ __('messages.edit') }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editPrincipaleCretaireModal-{{ $principale->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            title="{{ __('messages.delete') }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deletePrincipaleCretaireModal-{{ $principale->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Edit Principale Cretaire Modal -->
                                        <div class="modal fade" id="editPrincipaleCretaireModal-{{ $principale->id }}" tabindex="-1" aria-labelledby="editPrincipaleCretaireModalLabel-{{ $principale->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editPrincipaleCretaireModalLabel-{{ $principale->id }}">{{ __('messages.principale_cretaires_edit_title') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('principale-cretaires.update', $principale) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="changement_type_id" value="{{ $changementType->id }}">
                                                        <input type="hidden" name="form_context" value="edit_principale_cretaire_show">
                                                        <input type="hidden" name="principale_id" value="{{ $principale->id }}">
                                                        
                                                        <div class="modal-body">
                                                            @if ($errors->any() && old('form_context') === 'edit_principale_cretaire_show' && old('principale_id') == $principale->id)
                                                                <div class="alert alert-danger">
                                                                    <strong>{{ __('messages.form_fix_errors') }}</strong>
                                                                    <ul class="mb-0 mt-2">
                                                                        @foreach ($errors->all() as $error)
                                                                            <li>{{ $error }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif

                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label for="edit_principale_name_{{ $principale->id }}" class="form-label fw-semibold">{{ __('messages.name') }}</label>
                                                                    <input
                                                                        type="text"
                                                                        name="name"
                                                                        id="edit_principale_name_{{ $principale->id }}"
                                                                        class="form-control @error('name') is-invalid @enderror"
                                                                        value="{{ old('name', $principale->name) }}"
                                                                        required
                                                                    >
                                                                    @error('name')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                                <div class="col-12">
                                                                    <label for="edit_principale_description_{{ $principale->id }}" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                                                    <textarea
                                                                        name="description"
                                                                        id="edit_principale_description_{{ $principale->id }}"
                                                                        rows="4"
                                                                        class="form-control @error('description') is-invalid @enderror"
                                                                    >{{ old('description', $principale->description) }}</textarea>
                                                                    @error('description')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                                <div class="col-12">
                                                                    <div class="form-check form-switch">
                                                                        <input
                                                                            type="checkbox"
                                                                            name="is_active"
                                                                            id="edit_principale_is_active_{{ $principale->id }}"
                                                                            class="form-check-input"
                                                                            value="1"
                                                                            {{ old('is_active', $principale->is_active) ? 'checked' : '' }}
                                                                        >
                                                                        <label class="form-check-label fw-semibold" for="edit_principale_is_active_{{ $principale->id }}">
                                                                            {{ __('messages.active') }}
                                                                        </label>
                                                                    </div>
                                                                    @error('is_active')
                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                    @enderror
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

                                        <!-- Delete Principale Cretaire Confirmation Modal -->
                                        <div class="modal fade" id="deletePrincipaleCretaireModal-{{ $principale->id }}" tabindex="-1" aria-labelledby="deletePrincipaleCretaireModalLabel-{{ $principale->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 bg-danger">
                                                        <h5 class="modal-title text-white" id="deletePrincipaleCretaireModalLabel-{{ $principale->id }}">{{ __('messages.principale_cretaires_delete_title') }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-center mb-3">
                                                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                                        </div>
                                                        <p class="text-center mb-0">
                                                            {{ __('messages.principale_cretaires_delete_message', ['name' => $principale->name]) }}
                                                        </p>
                                                        @if($principale->sousCretaires->count() > 0)
                                                            <div class="alert alert-warning mt-3 mb-0">
                                                                <i class="bi bi-info-circle me-2"></i>
                                                                {{ __('messages.principale_cretaires_delete_warning', ['count' => $principale->sousCretaires->count()]) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                                        <form action="{{ route('principale-cretaires.destroy', $principale) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="form_context" value="delete_principale_cretaire_show">
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="bi bi-trash me-1"></i> {{ __('messages.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                {{ __('messages.changement_types_primary_empty') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('messages.quick_actions') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ __('messages.changements_menu') }}
                        </p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editChangementTypeModal">
                                <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                            </button>
                            <hr class="my-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteChangementTypeModal">
                                <i class="bi bi-trash me-1"></i> {{ __('messages.delete') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('messages.changement_types_information') }}</h5>
                        <p class="text-muted">{{ $changementType->description ?: __('messages.no_description') }}</p>

                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('messages.changement_types_show_status') }}</span>
                                <span class="fw-semibold">
                                    @if ($changementType->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success">{{ __('messages.active') }}</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ __('messages.inactive') }}</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('messages.changement_types_created_at') }}</span>
                                <span class="fw-semibold">{{ $changementType->created_at?->format('d/m/Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('messages.changement_types_updated_at') }}</span>
                                <span class="fw-semibold">{{ $changementType->updated_at?->format('d/m/Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Changement Type Modal -->
    <div class="modal fade" id="editChangementTypeModal" tabindex="-1" aria-labelledby="editChangementTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChangementTypeModalLabel">{{ __('messages.changement_types_edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('changement-types.update', $changementType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit_changement_type_show">
                    
                    <div class="modal-body">
                        @if ($errors->any() && old('form_context') === 'edit_changement_type_show')
                            <div class="alert alert-danger">
                                <strong>{{ __('messages.form_fix_errors') }}</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="edit_name" class="form-label fw-semibold">{{ __('messages.name') }}</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="edit_name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $changementType->name) }}"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="edit_description" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                <textarea
                                    name="description"
                                    id="edit_description"
                                    rows="4"
                                    class="form-control @error('description') is-invalid @enderror"
                                >{{ old('description', $changementType->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        id="edit_is_active"
                                        class="form-check-input"
                                        value="1"
                                        {{ old('is_active', $changementType->is_active) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label fw-semibold" for="edit_is_active">
                                        {{ __('messages.active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
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

    <!-- Delete Changement Type Modal -->
    <div class="modal fade" id="deleteChangementTypeModal" tabindex="-1" aria-labelledby="deleteChangementTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 bg-danger">
                    <h5 class="modal-title text-white" id="deleteChangementTypeModalLabel">{{ __('messages.changement_types_delete_title') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center mb-0">
                        {{ __('messages.changement_types_delete_message', ['name' => $changementType->name]) }}
                    </p>
                    @if($changementType->principaleCretaires->count() > 0)
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('messages.changement_types_delete_warning', ['count' => $changementType->principaleCretaires->count()]) }}
                        </div>
                    @endif
                </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <form action="{{ route('changement-types.destroy', $changementType) }}" method="POST" class="d-inline">
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

    <!-- Create Principale Cretaire Modal -->
    <div class="modal fade" id="createPrincipaleCretaireModal" tabindex="-1" aria-labelledby="createPrincipaleCretaireModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPrincipaleCretaireModalLabel">{{ __('messages.principale_cretaires_create_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('principale-cretaires.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="changement_type_id" value="{{ $changementType->id }}">
                    <input type="hidden" name="form_context" value="create_principale_cretaire_show">
                    
                    <div class="modal-body">
                        @if ($errors->any() && old('form_context') === 'create_principale_cretaire_show')
                            <div class="alert alert-danger">
                                <strong>{{ __('messages.form_fix_errors') }}</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create_principale_name" class="form-label fw-semibold">{{ __('messages.name') }}</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="create_principale_name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="create_principale_description" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                <textarea
                                    name="description"
                                    id="create_principale_description"
                                    rows="4"
                                    class="form-control @error('description') is-invalid @enderror"
                                >{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        id="create_principale_is_active"
                                        class="form-check-input"
                                        value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label fw-semibold" for="create_principale_is_active">
                                        {{ __('messages.active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
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

    @if ($errors->any() && old('form_context') === 'edit_changement_type_show')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var editModal = new bootstrap.Modal(document.getElementById('editChangementTypeModal'));
                    editModal.show();
                });
            </script>
        @endpush
    @endif

    @if ($errors->any() && old('form_context') === 'create_principale_cretaire_show')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var createModal = new bootstrap.Modal(document.getElementById('createPrincipaleCretaireModal'));
                    createModal.show();
                });
            </script>
        @endpush
    @endif

    @if ($errors->any() && old('form_context') === 'edit_principale_cretaire_show' && old('principale_id'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var principaleId = @json(old('principale_id'));
                    var editModal = new bootstrap.Modal(document.getElementById('editPrincipaleCretaireModal-' + principaleId));
                    editModal.show();
                });
            </script>
        @endpush
    @endif
</x-app-layout>

