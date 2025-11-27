<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid my-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center shadow-sm p-4 rounded mb-4">
            <div>
                <h5 class="mb-1 text-dark fw-bold">
                    <i class="bi bi-person-plus me-2 text-primary"></i>
                    {{ __('messages.create_new_user') ?? 'Create New User' }}
                </h5>
                <small class="text-muted">{{ __('messages.create_user_description') ?? 'Add a new administrative user to the system' }}</small>
            </div>
            <a href="{{ route('administration-roles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back') }}
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('administration-roles.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="{{ __('messages.name_placeholder') }}"
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
                                           value="{{ old('email') }}"
                                           placeholder="{{ __('messages.email_placeholder') }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">{{ __('messages.password') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="{{ __('messages.password_placeholder') }}"
                                               required>
                                        <button type="button"
                                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3"
                                                id="togglePassword"
                                                style="border: none; background: none; color: #6c757d; z-index: 10;"
                                                aria-label="{{ __('messages.toggle_password_visibility') ?? 'Toggle password visibility' }}">
                                            <i class="bi bi-eye" id="passwordEyeIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('messages.password_min_hint') ?? 'Minimum 8 characters' }}</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password"
                                               class="form-control @error('password_confirmation') is-invalid @enderror"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               placeholder="{{ __('messages.confirm_password_placeholder') }}"
                                               required>
                                        <button type="button"
                                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3"
                                                id="togglePasswordConfirmation"
                                                style="border: none; background: none; color: #6c757d; z-index: 10;"
                                                aria-label="{{ __('messages.toggle_password_visibility') ?? 'Toggle password visibility' }}">
                                            <i class="bi bi-eye" id="passwordConfirmationEyeIcon"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
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
                                           value="{{ old('phone') }}"
                                           placeholder="{{ __('messages.phone_placeholder') }}">
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
                                           value="{{ old('department') }}"
                                           placeholder="{{ __('messages.department_placeholder') }}">
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-6">
                                    <label for="role" class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror"
                                            id="role"
                                            name="role"
                                            required>
                                        <option value="">{{ __('messages.select_role') ?? 'Select role' }}</option>
                                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>
                                            {{ __('messages.role_manager') ?? 'Manager' }}
                                        </option>
                                        <option value="other" {{ old('role') === 'other' ? 'selected' : '' }}>
                                            {{ __('messages.role_other') ?? 'Other' }}
                                        </option>
                                    </select>
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
                                            <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>
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
                                           value="{{ old('date_of_birth') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="date_integration" class="form-label">{{ __('messages.date_integration') }}</label>
                                    <input type="date"
                                           class="form-control @error('date_integration') is-invalid @enderror"
                                           id="date_integration"
                                           name="date_integration"
                                           value="{{ old('date_integration') }}">
                                    @error('date_integration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
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
                                    <small class="text-muted">{{ __('messages.profile_photo_hint') ?? 'Maximum 2MB. Accepted formats: JPG, PNG, GIF' }}</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('administration-roles.index') }}" class="btn btn-light">
                                    {{ __('messages.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-1"></i>{{ __('messages.create') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const passwordEyeIcon = document.getElementById('passwordEyeIcon');

            if (togglePassword && passwordInput && passwordEyeIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle icon
                    if (type === 'text') {
                        passwordEyeIcon.classList.remove('bi-eye');
                        passwordEyeIcon.classList.add('bi-eye-slash');
                    } else {
                        passwordEyeIcon.classList.remove('bi-eye-slash');
                        passwordEyeIcon.classList.add('bi-eye');
                    }
                });
            }

            // Toggle password confirmation visibility
            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const passwordConfirmationEyeIcon = document.getElementById('passwordConfirmationEyeIcon');

            if (togglePasswordConfirmation && passwordConfirmationInput && passwordConfirmationEyeIcon) {
                togglePasswordConfirmation.addEventListener('click', function() {
                    const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordConfirmationInput.setAttribute('type', type);
                    
                    // Toggle icon
                    if (type === 'text') {
                        passwordConfirmationEyeIcon.classList.remove('bi-eye');
                        passwordConfirmationEyeIcon.classList.add('bi-eye-slash');
                    } else {
                        passwordConfirmationEyeIcon.classList.remove('bi-eye-slash');
                        passwordConfirmationEyeIcon.classList.add('bi-eye');
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

