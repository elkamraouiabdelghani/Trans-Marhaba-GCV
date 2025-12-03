<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid my-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center shadow-sm p-4 rounded mb-4">
            <div>
                <h5 class="mb-1 text-dark fw-bold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                    {{ __('messages.edit') }} — {{ $user->name }}
                </h5>
            </div>
            <a href="{{ route('administration-roles.show', $user) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back') }}
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('administration-roles.update', $user) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="department" class="form-label">{{ __('messages.department') }}</label>
                                    <input type="text"
                                           class="form-control @error('department') is-invalid @enderror"
                                           id="department"
                                           name="department"
                                           value="{{ old('department', $user->department) }}">
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-6">
                                    <label for="role" class="form-label">{{ __('messages.role') }}</label>
                                    <input type="text"
                                           class="form-control @error('role') is-invalid @enderror"
                                           id="role"
                                           name="role"
                                           value="{{ old('role', $user->role) }}">
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('status', $user->status) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                                    <input type="date"
                                           class="form-control @error('date_of_birth') is-invalid @enderror"
                                           id="date_of_birth"
                                           name="date_of_birth"
                                           value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_photo" class="form-label">{{ __('messages.profile_photo') }}</label>
                                    <input type="file"
                                           class="form-control @error('profile_photo') is-invalid @enderror"
                                           id="profile_photo"
                                           name="profile_photo"
                                           accept="image/*">
                                    @error('profile_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($user->profile_photo_path)
                                        <div class="d-flex align-items-center gap-3 mt-2">
                                            <img src="{{ route('administration-roles.profile-photo', $user) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 70px; height: 70px; object-fit: cover;">

                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       value="1"
                                                       id="remove_photo"
                                                       name="remove_photo">
                                                <label class="form-check-label" for="remove_photo">
                                                    {{ __('messages.remove_photo') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('administration-roles.show', $user) }}" class="btn btn-light">
                                    {{ __('messages.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-1"></i>{{ __('messages.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

