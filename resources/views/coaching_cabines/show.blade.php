<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_cabines_show_title') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('messages.driver') }}: {{ $coachingCabine->driver->full_name ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.coaching_cabines_back_to_list') }}
                </a>
            </div>
        </div>

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

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.coaching_session') }} - {{ __('messages.information') ?? 'Informations' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.driver') }}</label>
                                <div class="fw-semibold">
                                    <a href="{{ route('drivers.show', $coachingCabine->driver) }}" class="text-decoration-none">
                                        {{ $coachingCabine->driver->full_name ?? '-' }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.flotte') }}</label>
                                <div class="fw-semibold">
                                    @if($coachingCabine->flotte)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $coachingCabine->flotte->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.type') ?? 'Type' }}</label>
                                <div>
                                    <span class="badge bg-{{ $coachingCabine->getTypeColor() }}-opacity-10 text-{{ $coachingCabine->getTypeColor() }}">
                                        {{ $coachingCabine->getTypeTitle() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.from_date') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->date ? $coachingCabine->date->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.date_fin') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->date_fin ? $coachingCabine->date_fin->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.moniteur') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->moniteur ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.status') }}</label>
                                <div>
                                    @php
                                        $statusLabels = [
                                            'planned' => __('messages.status_planned'),
                                            'in_progress' => __('messages.status_in_progress'),
                                            'completed' => __('messages.status_completed'),
                                            'cancelled' => __('messages.status_cancelled')
                                        ];
                                        $statusColors = [
                                            'planned' => 'primary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$coachingCabine->status] ?? 'secondary' }}-opacity-10 text-{{ $statusColors[$coachingCabine->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$coachingCabine->status] ?? $coachingCabine->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.validity_days') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->validity_days ?? '-' }} {{ __('messages.days') ?? 'jours' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.score') ?? 'Score' }}</label>
                                <div>
                                    @if($coachingCabine->score !== null)
                                        <span class="badge bg-{{ $coachingCabine->score >= 70 ? 'success' : ($coachingCabine->score >= 50 ? 'warning' : 'danger') }}-opacity-10 text-{{ $coachingCabine->score >= 70 ? 'success' : ($coachingCabine->score >= 50 ? 'warning' : 'danger') }}">
                                            {{ $coachingCabine->score }}/100
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.next_planning_session') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->next_planning_session ? $coachingCabine->next_planning_session->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                @if($coachingCabine->route_taken)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.route_taken') }}</h5>
                    </div>
                    <div class="card-body">
                        {{ $coachingCabine->route_taken }}
                    </div>
                </div>
                @endif
                @if($coachingCabine->assessment)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.assessment') }}</h5>
                    </div>
                    <div class="card-body">
                        {{ $coachingCabine->assessment }}
                    </div>
                </div>   
                @endif
                @if($coachingCabine->notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.notes') ?? 'Notes' }}</h5>
                    </div>
                    <div class="card-body">
                        {{ $coachingCabine->notes }}
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.actions') ?? 'Actions' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('coaching-cabines.edit', $coachingCabine) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                            </a>
                            <hr class="my-2">
                            <a href="{{ route('drivers.show', $coachingCabine->driver) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-person me-1"></i> {{ __('messages.view_driver') ?? 'Voir le chauffeur' }}
                            </a>
                            @if ($coachingCabine->status == 'completed')
                                <a href="{{ route('coaching-cabines.pdf', $coachingCabine) }}" class="btn btn-danger btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') ?? 'Exporter en PDF' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.metadata') ?? 'Métadonnées' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <div class="mb-2">
                                <strong>{{ __('messages.created_at') ?? 'Créé le' }}:</strong><br>
                                {{ $coachingCabine->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div>
                                <strong>{{ __('messages.updated_at') ?? 'Modifié le' }}:</strong><br>
                                {{ $coachingCabine->updated_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

