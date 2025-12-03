@php
    use Illuminate\Support\Str;
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                {{ __('messages.organigram') ?? 'Organigramme' }}
                            </h5>
                            <small class="text-muted">{{ __('messages.manage_organigram_description') ?? 'Manage company structure members.' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('organigram.download') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download me-1"></i>
                                {{ __('messages.download_organigram') ?? 'Download PDF' }}
                            </a>
                            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createMemberModal">
                                <i class="bi bi-plus-circle me-1"></i>
                                {{ __('messages.add_member') ?? 'Add Member' }}
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any() && old('form_action') === 'create')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ __('messages.error') ?? 'Error' }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">{{ __('messages.position') ?? 'Position' }}</th>
                                        <th class="border-0">{{ __('messages.name') ?? 'Name' }}</th>
                                        <th class="border-0">{{ __('messages.last_update_date') ?? 'Last Update Date' }}</th>
                                        <th class="border-0 text-end">{{ __('messages.action') ?? 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($members as $member)
                                        <tr>
                                            <td class="py-3">
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                                    {{ Str::of($member->position)->replace('_', ' ')->title() }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-dark fw-semibold">
                                                {{ $member->name }}
                                            </td>
                                            <td class="py-3">
                                                {{ $member->updated_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-3 text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button
                                                        class="btn btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editMemberModal"
                                                        data-action="{{ route('organigram.update', $member) }}"
                                                        data-position="{{ $member->position }}"
                                                        data-name="{{ $member->name }}"
                                                        data-member-id="{{ $member->id }}"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-5 text-center text-muted">
                                                <i class="bi bi-people display-5 d-block mb-3"></i>
                                                {{ __('messages.no_organigram_members') ?? 'No members found.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createMemberModal" tabindex="-1" aria-labelledby="createMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createMemberModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.add_member') ?? 'Add Member' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('organigram.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_action" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_position" class="form-label">{{ __('messages.position') ?? 'Position' }}</label>
                            @if(empty($availablePositions))
                                <select id="create_position" class="form-select" disabled>
                                    <option value="">{{ __('messages.no_available_positions') ?? 'All positions are already assigned.' }}</option>
                                </select>
                            @else
                                <select name="position" id="create_position" class="form-select @if(old('form_action') === 'create') @error('position') is-invalid @enderror @endif" required>
                                    <option value="">{{ __('messages.select_option') ?? 'Select an option' }}</option>
                                    @foreach($availablePositions as $position)
                                        <option value="{{ $position }}" {{ old('form_action') === 'create' && old('position') === $position ? 'selected' : '' }}>
                                            {{ Str::of($position)->replace('_', ' ')->title() }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @if(old('form_action') === 'create')
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="create_name" class="form-label">{{ __('messages.name') ?? 'Name' }}</label>
                            <input type="text" name="name" id="create_name" class="form-control @if(old('form_action') === 'create') @error('name') is-invalid @enderror @endif" value="{{ old('form_action') === 'create' ? old('name') : '' }}" required>
                            @if(old('form_action') === 'create')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') ?? 'Cancel' }}</button>
                        <button type="submit" class="btn btn-dark" {{ empty($availablePositions) ? 'disabled' : '' }}>{{ __('messages.save') ?? 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        {{ __('messages.edit_member') ?? 'Edit Member' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editMemberForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_action" value="update">
                    <input type="hidden" name="edit_action" id="edit_action" value="">
                    <input type="hidden" name="member_id" id="edit_member_id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.position') ?? 'Position' }}</label>
                            <input type="text" class="form-control" id="edit_position_display" readonly disabled>
                            <input type="hidden" name="position" id="edit_position" value="">
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">{{ __('messages.name') ?? 'Name' }}</label>
                            <input type="text" name="name" id="edit_name" class="form-control {{ (old('form_action') === 'update' && $errors->has('name')) ? 'is-invalid' : '' }}" required>
                            @if(old('form_action') === 'update')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') ?? 'Cancel' }}</button>
                        <button type="submit" class="btn btn-dark">{{ __('messages.update') ?? 'Update' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const createModalEl = document.getElementById('createMemberModal');
            if (createModalEl && @json($errors->any() && old('form_action') === 'create')) {
                const createModal = new bootstrap.Modal(createModalEl);
                createModal.show();
            }

            const editModalEl = document.getElementById('editMemberModal');
            if (editModalEl) {
                editModalEl.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const action = button.getAttribute('data-action');
                    const position = button.getAttribute('data-position');
                    const name = button.getAttribute('data-name');
                    const memberId = button.getAttribute('data-member-id');

                    const form = document.getElementById('editMemberForm');
                    const positionHidden = document.getElementById('edit_position');
                    const positionDisplay = document.getElementById('edit_position_display');
                    const nameInput = document.getElementById('edit_name');
                    const editActionInput = document.getElementById('edit_action');
                    const memberIdInput = document.getElementById('edit_member_id');

                    form.action = action;
                    positionHidden.value = position;
                    positionDisplay.value = position ? position.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '';
                    nameInput.value = name;
                    editActionInput.value = action;
                    memberIdInput.value = memberId;
                });

                if (@json($errors->any() && old('form_action') === 'update')) {
                    const form = document.getElementById('editMemberForm');
                    const positionHidden = document.getElementById('edit_position');
                    const positionDisplay = document.getElementById('edit_position_display');
                    const nameInput = document.getElementById('edit_name');
                    const editActionInput = document.getElementById('edit_action');
                    const memberIdInput = document.getElementById('edit_member_id');

                    const oldPosition = "{{ old('position') }}";
                    form.action = "{{ old('edit_action') }}";
                    editActionInput.value = "{{ old('edit_action') }}";
                    memberIdInput.value = "{{ old('member_id') }}";
                    positionHidden.value = oldPosition;
                    positionDisplay.value = oldPosition ? oldPosition.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '';
                    nameInput.value = "{{ old('name') }}";

                    const editModal = new bootstrap.Modal(editModalEl);
                    editModal.show();
                }
            }
        });
    </script>
</x-app-layout>

