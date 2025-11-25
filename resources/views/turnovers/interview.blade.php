<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('turnovers.index') }}">{{ __('messages.turnovers') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('turnovers.edit', $turnover) }}">{{ __('messages.edit_turnover') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ __('messages.exit_interview') }}
                </li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-clipboard-check text-success me-2"></i>
                            {{ __('messages.exit_interview') }}
                        </h5>
                        <small class="text-muted">{{ __('messages.exit_interview_instructions') }}</small>
                    </div>
                    <a href="{{ route('turnovers.edit', $turnover) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('turnovers.interview.store', $turnover) }}">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ __('messages.validation_errors') ?? 'Please verify the following fields:' }}
                            </h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $ratingQuestions = collect($questions)->where('type', 'rating')->values();
                        $textQuestions = collect($questions)->where('type', 'text')->values();
                        $firstRatingTable = $ratingQuestions->slice(0, 6);
                        $remainingRatings = $ratingQuestions->slice(6);
                    @endphp

                    <section class="mb-5">
                        <h6 class="text-success text-uppercase fw-bold mb-3">{{ __('messages.general_information') }}</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.interview_date') }}</label>
                                <input type="date"
                                       name="interview_date"
                                       class="form-control @error('interview_date') is-invalid @enderror"
                                       value="{{ old('interview_date', $meta['interview_date']) }}"
                                       >
                                @error('interview_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.exit_interview_reference') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ sprintf('TURN-%05d', $turnover->id) }}"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.employee_name') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $meta['employee_name'] }}"
                                       readonly>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.position') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $turnover->position ?? '-' }}"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.flotte') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $turnover->flotte ?? '-' }}"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.interviewed_by') }}</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $turnover->interviewed_by ?? '-' }}"
                                       readonly>
                            </div>
                        </div>
                    </section>

                    <section class="mb-5">
                        <div class="alert alert-info">
                            <strong>{{ __('messages.exit_interview_disclaimer_title') }}:</strong>
                            {{ __('messages.exit_interview_disclaimer_body') }}
                        </div>

                        <h6 class="text-success text-uppercase fw-bold mb-3">{{ __('messages.rating_questions_section_title') }}</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-success text-dark">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>{{ __('messages.question_en') }}</th>
                                        <th>{{ __('messages.question_ar') }}</th>
                                        @foreach ($ratingScale as $value)
                                            <th style="width: 70px;">{{ $value }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($firstRatingTable as $question)
                                        <tr>
                                            <td class="fw-bold">{{ $question['number'] }}</td>
                                            <td class="text-start">{{ $question['text']['en'] }}</td>
                                            <td class="text-end fw-bold" dir="rtl">{{ $question['text']['ar'] }}</td>
                                            @foreach ($ratingScale as $rate)
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input"
                                                               type="radio"
                                                               name="{{ $question['key'] }}"
                                                               value="{{ $rate }}"
                                                               {{ (string) old($question['key'], $answers[$question['key']] ?? '') === (string) $rate ? 'checked' : '' }}>
                                                        <label class="form-check-label">{{ $rate }}</label>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;" class="text-center">#</th>
                                        <th>{{ __('messages.question_en') }}</th>
                                        <th class="text-end">{{ __('messages.question_ar') }}</th>
                                        <th style="width: 220px;" class="text-center">{{ __('messages.rating') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($remainingRatings as $question)
                                        <tr>
                                            <td class="fw-bold text-center">{{ $question['number'] }}</td>
                                            <td>{{ $question['text']['en'] }}</td>
                                            <td class="text-end fw-bold" dir="rtl">{{ $question['text']['ar'] }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                    @foreach ($ratingScale as $rate)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input"
                                                                   type="radio"
                                                                   id="{{ $question['key'] }}_{{ $rate }}"
                                                                   name="{{ $question['key'] }}"
                                                                   value="{{ $rate }}"
                                                                   {{ (string) old($question['key'], $answers[$question['key']] ?? '') === (string) $rate ? 'checked' : '' }}
                                                                   >
                                                            <label class="form-check-label" for="{{ $question['key'] }}_{{ $rate }}">{{ $rate }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="mb-5">
                        <h6 class="text-success text-uppercase fw-bold mb-3">{{ __('messages.text_questions_section_title') }}</h6>
                        <div class="row g-4">
                            @foreach ($textQuestions as $question)
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <span class="text-success me-2">{{ $question['number'] }}.</span>
                                        {{ $question['text']['en'] }}
                                        <span class="float-end fw-bold" dir="rtl">{{ $question['text']['ar'] }}</span>
                                    </label>
                                    <textarea name="{{ $question['key'] }}"
                                              rows="4"
                                              class="form-control @error($question['key']) is-invalid @enderror"
                                              placeholder="{{ __('messages.write_answer_here') }}">{{ old($question['key'], $answers[$question['key']] ?? '') }}</textarea>
                                    @error($question['key'])
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="mb-4">
                        <h6 class="text-success text-uppercase fw-bold mb-3">{{ __('messages.employee_signature_section') }}</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">{{ __('messages.employee_signature') }}</label>
                                <input type="text"
                                       class="form-control @error('employee_signature') is-invalid @enderror"
                                       name="employee_signature"
                                       value="{{ old('employee_signature', $meta['employee_signature']) }}"
                                       >
                                @error('employee_signature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">{{ __('messages.employee_name_confirmation') }}</label>
                                <input type="text"
                                       class="form-control @error('employee_name') is-invalid @enderror"
                                       name="employee_name"
                                       value="{{ old('employee_name', $meta['employee_name']) }}"
                                       >
                                @error('employee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('turnovers.edit', $turnover) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

