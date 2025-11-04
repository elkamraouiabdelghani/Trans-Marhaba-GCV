<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container - Top Center -->
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

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Progress Sidebar -->
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-list-check me-2 text-primary"></i>
                            {{ __('messages.driver_integration') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">{{ __('messages.progress') }}</span>
                                <span class="text-muted small fw-bold">{{ $progressPercentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ $progressPercentage }}%" 
                                     aria-valuenow="{{ $progressPercentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Steps List -->
                        <div class="list-group list-group-flush">
                            @foreach($stepsOrder as $index => $stepKey)
                                @php
                                    $stepStatus = $integration->getStep($stepKey);
                                    $isActive = $step === $stepKey;
                                    $isCompleted = $stepStatus && $stepStatus->status === 'passed';
                                    $isFailed = $stepStatus && $stepStatus->status === 'failed';
                                @endphp
                                <div class="list-group-item border-0 px-0 py-2 {{ $isActive ? 'bg-light' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            @if($isCompleted)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @elseif($isFailed)
                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                            @elseif($isActive)
                                                <i class="bi bi-circle-fill text-primary"></i>
                                            @else
                                                <i class="bi bi-circle text-muted"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <small class="d-block fw-bold {{ $isActive ? 'text-primary' : ($isCompleted ? 'text-success' : ($isFailed ? 'text-danger' : 'text-muted')) }}">
                                                {{ $index + 1 }}. {{ $stepLabels[$stepKey] ?? $stepKey }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($driver)
                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted d-block mb-1">{{ __('messages.driver') }}:</small>
                                <strong class="text-dark">{{ $driver->full_name ?? __('messages.not_available') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step Form -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-file-text me-2 text-primary"></i>
                                {{ $stepLabel }}
                            </h5>
                            <a href="{{ route('drivers.integrations') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('drivers.integrations.step.save', ['integration' => $integration->id, 'step' => $step]) }}" method="POST">
                            @csrf

                            @include('drivers.integrations.steps.' . $step, ['stepData' => $stepData])

                            <hr class="my-4">

                            <div class="d-flex gap-2 justify-content-between">
                                <div>
                                    @if($canGoPrevious)
                                        <button type="submit" name="action" value="previous" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            {{ __('messages.previous') }}
                                        </button>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @if(!$driver->is_integrated && $step == \App\Models\DriverIntegration::STEP_VALIDATION_FINALE)
                                        <button type="submit" name="action" value="save" class="btn btn-outline-primary">
                                            <i class="bi bi-save me-1"></i>
                                            {{ __('messages.save') }}
                                        </button>
                                    @endif
                                    @if($canGoNext)
                                        <button type="submit" name="action" value="next" class="btn btn-primary">
                                            {{ __('messages.next') }}
                                            <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

