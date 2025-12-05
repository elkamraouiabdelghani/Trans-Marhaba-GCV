<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-0 fw-bold text-dark fs-4">
                    <i class="bi bi-signpost-split me-2 text-primary"></i>
                    {{ $journey->name }}
                </h2>
            </div>
            <div>
                <a href="{{ route('journeys.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('messages.back_to_list') ?? 'Back to list' }}
                </a>
            </div>
        </div>

        <!-- Main Content and Sidebar -->
        <div class="row g-4">
            <!-- Main Section -->
            <div class="col-lg-8">
                <!-- Journey Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.journey_information') ?? 'Journey Information' }}
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.from_location') ?? 'From Location' }}
                                </label>
                                <p class="mb-0">
                                    @if($journey->from_location_name)
                                        <span class="fw-semibold">{{ $journey->from_location_name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $journey->from_latitude }}, {{ $journey->from_longitude }}</small>
                                    @else
                                        <span class="fw-semibold">{{ $journey->from_latitude }}, {{ $journey->from_longitude }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.to_location') ?? 'To Location' }}
                                </label>
                                <p class="mb-0">
                                    @if($journey->to_location_name)
                                        <span class="fw-semibold">{{ $journey->to_location_name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $journey->to_latitude }}, {{ $journey->to_longitude }}</small>
                                    @else
                                        <span class="fw-semibold">{{ $journey->to_latitude }}, {{ $journey->to_longitude }}</span>
                                    @endif
                                </p>
                            </div>
                            @if($journey->details)
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.details') ?? 'Details' }}
                                    </label>
                                    <div class="card border bg-light rounded-3 shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="mb-0">{{ $journey->details }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.total_score') ?? 'Total Score' }}
                                </label>
                                <p class="mb-0">
                                    <span class="badge bg-primary fs-6">{{ number_format($journey->total_score, 2) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.status') ?? 'Status' }}
                                </label>
                                <p class="mb-0">
                                    @php
                                        $status = $journey->status;
                                        $statusClasses = [
                                            'excellent' => 'bg-success',
                                            'good' => 'bg-info',
                                            'average' => 'bg-warning',
                                            'less' => 'bg-danger',
                                        ];
                                        $statusClass = $statusClasses[$status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }} fs-6">
                                        {{ __('messages.journey_status_' . $status) ?? ucfirst($status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-map me-2 text-primary"></i>
                            {{ __('messages.map') ?? 'Map' }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="journey-map" style="width: 100%; height: 500px; border-radius: 0.5rem;"></div>
                    </div>
                </div>

                <!-- Black Points List -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                            {{ __('messages.black_points') ?? 'Black Points' }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ __('messages.name') ?? 'Name' }}</th>
                                        <th>{{ __('messages.coordinates') ?? 'Coordinates' }}</th>
                                        <th>{{ __('messages.description') ?? 'Description' }}</th>
                                        <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($journey->blackPoints as $blackPoint)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold">{{ $blackPoint->name }}</div>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    {{ $blackPoint->latitude }}, {{ $blackPoint->longitude }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    {{ $blackPoint->description ? Str::limit($blackPoint->description, 50) : '-' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group" role="group">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="{{ __('messages.edit') ?? 'Edit' }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editBlackPointModal"
                                                            data-black-point-id="{{ $blackPoint->id }}"
                                                            data-name="{{ $blackPoint->name }}"
                                                            data-latitude="{{ $blackPoint->latitude }}"
                                                            data-longitude="{{ $blackPoint->longitude }}"
                                                            data-description="{{ $blackPoint->description }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="{{ __('messages.delete') ?? 'Delete' }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteBlackPointModal"
                                                            data-delete-url="{{ route('journeys.black-points.destroy', $blackPoint) }}"
                                                            data-black-point-name="{{ $blackPoint->name }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                {{ __('messages.no_black_points_found') ?? 'No black points found' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Checklist Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-list-check me-2 text-primary"></i>
                            {{ __('messages.checklist') ?? 'Checklist' }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if($checklist)
                            <!-- Checklist Answers -->
                            @if($checklist->answers && $checklist->answers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('messages.item') ?? 'Item' }}</th>
                                                <th class="text-center">{{ __('messages.weight') ?? 'Weight' }}</th>
                                                <th class="text-center">{{ __('messages.score') ?? 'Score' }}</th>
                                                <th class="text-center">{{ __('messages.note') ?? 'Note' }}</th>
                                                <th>{{ __('messages.comment') ?? 'Comment' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($checklist->answers as $answer)
                                                <tr>
                                                    <td>
                                                        @if($answer->templateItem)
                                                            <div>
                                                                <strong class="fw-semibold">{{ $answer->templateItem->donnees ?? '-' }}</strong>
                                                                @if($answer->templateItem->cirees_appreciation)
                                                                    <br><small class="text-muted">{{ $answer->templateItem->cirees_appreciation }}</small>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-secondary">{{ $answer->weight ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $answer->score ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary">{{ number_format($answer->note ?? 0, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $answer->comment ?? '-' }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">{{ __('messages.no_checklist_answers') ?? 'No checklist answers found.' }}</p>
                            @endif

                            @if($checklist->notes)
                                <div class="mb-4">
                                    <label class="form-label fw-bolder small mb-2">
                                        {{ __('messages.general_comment') ?? 'General Comment' }}
                                    </label>
                                    <div class="card border bg-light rounded-3 shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="mb-0">{{ $checklist->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Documents -->
                            @if($checklist->documents && count($checklist->documents) > 0)
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-images me-2 text-primary"></i>
                                        {{ __('messages.checklist_documents') ?? 'Checklist Documents' }}
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($checklist->documents as $document)
                                            @php
                                                $docUrl = route('journeys.checklists.document', ['encoded' => base64_encode($document)]);
                                            @endphp
                                            <div class="col-md-4 col-sm-6">
                                                <div class="card border">
                                                    <a href="{{ $docUrl }}" target="_blank" class="text-decoration-none">
                                                        <img src="{{ $docUrl }}"
                                                             alt="Document"
                                                             class="card-img-top"
                                                             style="height: 200px; object-fit: cover; cursor: pointer;">
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ __('messages.no_checklist_found_journey') ?? 'No checklist has been completed for this journey yet.' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('journeys.edit', $journey) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i>
                                {{ __('messages.edit') ?? 'Edit' }}
                            </a>
                            <button type="button" 
                                    class="btn btn-primary" 
                                    id="addBlackPointBtn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addBlackPointModal">
                                <i class="bi bi-plus-circle me-2"></i>
                                {{ __('messages.add_black_point') ?? 'Add Black Point' }}
                            </button>
                            {{-- New Checklist Button --}}
                            @php
                                $canCreateChecklist = false;

                                if ($journey->inspection_status === 'no_inspection') {
                                    // No previous checklist: allow immediately
                                    $canCreateChecklist = true;
                                } elseif ($journey->last_inspection_date && $journey->next_inspection_due_at) {
                                    // Allow new checklist starting 2 weeks before due date
                                    $openFrom = $journey->next_inspection_due_at->copy()->subWeeks(2);
                                    $canCreateChecklist = \Illuminate\Support\Carbon::now()->greaterThanOrEqualTo($openFrom);
                                }
                            @endphp

                            @if ($canCreateChecklist)
                                <button type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createChecklistModal">
                                    <i class="bi bi-clipboard-plus me-1"></i>
                                    {{ __('messages.new_checklist') ?? 'New checklist' }}
                                </button>
                            @else
                                <button class="btn btn-outline-secondary btn-sm" type="button" disabled>
                                    <i class="bi bi-clipboard-plus me-1"></i>
                                    {{ __('messages.new_checklist') ?? 'New checklist' }}
                                </button>
                                @if($journey->next_inspection_due_at)
                                    <small class="text-muted mt-1 d-block">
                                        {{ __('messages.next_inspection_due') ?? 'Next inspection due' }}:
                                        {{ $journey->next_inspection_due_at->format('d/m/Y') }}
                                    </small>
                                @endif
                            @endif
                            @php
                                $latestChecklist = isset($checklistsHistory) && $checklistsHistory->count() > 0 
                                    ? $checklistsHistory->first() 
                                    : null;
                            @endphp
                            @if($latestChecklist)
                                <button type="button"
                                        class="btn btn-sm btn-danger js-checklist-pdf-btn"
                                        data-pdf-url="{{ route('journeys.checklists.pdf', $latestChecklist) }}">
                                    <i class="bi bi-download me-1"></i>
                                    {{ __('messages.download_pdf') ?? 'Download PDF' }}
                                </button>
                            @endif
                            @if(Auth::user()->role === 'admin')
                                <hr class="my-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteJourneyModal">
                                    <i class="bi bi-trash me-2"></i>
                                    {{ __('messages.delete') ?? 'Delete' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Checklist Information -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-list-check me-2 text-primary"></i>
                            {{ __('messages.checklist_info') ?? 'Checklist Information' }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @if($checklist)
                            <div class="d-flex flex-column gap-3">
                                @if($checklist->completed_at)
                                    <div>
                                        <label class="form-label fw-semibold text-muted small mb-1">
                                            {{ __('messages.completed_at') ?? 'Completed At' }}
                                        </label>
                                        <p class="mb-0">
                                            <span class="fw-semibold">{{ $checklist->completed_at->format('d/m/Y H:i') }}</span>
                                        </p>
                                    </div>
                                @endif
                                
                                @if($nextInspectionDue)
                                    <div>
                                        <label class="form-label fw-semibold text-muted small mb-1">
                                            {{ __('messages.next_inspection_due') ?? 'Next Inspection Due' }}
                                        </label>
                                        <p class="mb-0">
                                            <span class="badge {{ $nextInspectionDue->isPast() ? 'bg-danger' : ($nextInspectionDue->isToday() ? 'bg-warning' : 'bg-info') }}">
                                                {{ $nextInspectionDue->format('d/m/Y') }}
                                            </span>
                                        </p>
                                    </div>
                                @endif
                                
                                @if($checklist->completedByUser)
                                    <div>
                                        <label class="form-label fw-semibold text-muted small mb-1">
                                            {{ __('messages.completed_by') ?? 'Completed By' }}
                                        </label>
                                        <p class="mb-0">
                                            <span class="fw-semibold">{{ $checklist->completedByUser->name ?? $checklist->completedByUser->email ?? '-' }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ __('messages.no_checklist_found_journey') ?? 'No checklist has been completed for this journey yet.' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Checklist history --}}
        @if(isset($checklistsHistory) && $checklistsHistory->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        {{ __('messages.checklist_history') ?? 'Checklist history' }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('messages.completed_at') ?? 'Completed at' }}</th>
                                    <th>{{ __('messages.status') ?? 'Status' }}</th>
                                    <th>{{ __('messages.total_score') ?? 'Total Score' }}</th>
                                    <th>{{ __('messages.journey_status') ?? 'Journey Status' }}</th>
                                    <th>{{ __('messages.next_inspection_due') ?? 'Next inspection due' }}</th>
                                    <th>{{ __('messages.completed_by') ?? 'Completed by' }}</th>
                                    <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($checklistsHistory as $historyChecklist)
                                    <tr>
                                        <td class="ps-3">
                                            {{ optional($historyChecklist->completed_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @if($historyChecklist->status === 'accepted')
                                                <span class="badge bg-success">
                                                    {{ __('messages.accepted') ?? 'Accepted' }}
                                                </span>
                                            @elseif($historyChecklist->status === 'rejected')
                                                <span class="badge bg-danger">
                                                    {{ __('messages.rejected') ?? 'Rejected' }}
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    {{ __('messages.pending') ?? 'Pending' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ number_format($historyChecklist->total_score, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $checklistScore = $historyChecklist->total_score ?? 0;
                                                $journeyStatus = 'less';
                                                if ($checklistScore > 350 && $checklistScore < 580) {
                                                    $journeyStatus = 'excellent';
                                                } elseif ($checklistScore > 150 && $checklistScore <= 349) {
                                                    $journeyStatus = 'good';
                                                } elseif ($checklistScore > 100 && $checklistScore <= 149) {
                                                    $journeyStatus = 'average';
                                                }
                                                $statusClasses = [
                                                    'excellent' => 'bg-success',
                                                    'good' => 'bg-info',
                                                    'average' => 'bg-warning',
                                                    'less' => 'bg-danger',
                                                ];
                                                $statusClass = $statusClasses[$journeyStatus] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ __('messages.journey_status_' . $journeyStatus) ?? ucfirst($journeyStatus) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $historyDue = $historyChecklist->completed_at
                                                    ? $historyChecklist->completed_at->copy()->addMonthsNoOverflow(6)
                                                    : null;
                                            @endphp
                                            @if($historyDue)
                                                <span class="badge {{ $historyDue->isPast() ? 'bg-danger' : ($historyDue->isToday() ? 'bg-warning' : 'bg-info') }}">
                                                    {{ $historyDue->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $historyChecklist->completedByUser->name ?? '-' }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary js-checklist-pdf-btn"
                                                    data-pdf-url="{{ route('journeys.checklists.pdf', $historyChecklist) }}">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Add Black Point Modal -->
    <div class="modal fade" id="addBlackPointModal" tabindex="-1" aria-labelledby="addBlackPointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBlackPointModalLabel">
                        {{ __('messages.add_black_point') ?? 'Add Black Point' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('journeys.black-points.store', $journey) }}" method="POST" id="addBlackPointForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="black_point_name" class="form-label fw-semibold">
                                {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="black_point_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                {{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span>
                            </label>
                            <div id="black-point-map" style="width: 100%; height: 300px; border-radius: 0.5rem; margin-bottom: 0.5rem; position: relative; z-index: 1;"></div>
                            <input type="hidden" name="latitude" id="black_point_latitude" required>
                            <input type="hidden" name="longitude" id="black_point_longitude" required>
                            <small class="text-muted" id="black-point-coordinates-label">
                                {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="black_point_description" class="form-label fw-semibold">
                                {{ __('messages.description') ?? 'Description' }}
                            </label>
                            <textarea class="form-control" id="black_point_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            {{ __('messages.create') ?? 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Black Point Modal -->
    <div class="modal fade" id="editBlackPointModal" tabindex="-1" aria-labelledby="editBlackPointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBlackPointModalLabel">
                        {{ __('messages.edit_black_point') ?? 'Edit Black Point' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBlackPointForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_black_point_name" class="form-label fw-semibold">
                                {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_black_point_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                {{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span>
                            </label>
                            <div id="edit-black-point-map" style="width: 100%; height: 300px; border-radius: 0.5rem; margin-bottom: 0.5rem;"></div>
                            <input type="hidden" name="latitude" id="edit_black_point_latitude" required>
                            <input type="hidden" name="longitude" id="edit_black_point_longitude" required>
                            <small class="text-muted" id="edit-black-point-coordinates-label">
                                {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_black_point_description" class="form-label fw-semibold">
                                {{ __('messages.description') ?? 'Description' }}
                            </label>
                            <textarea class="form-control" id="edit_black_point_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            {{ __('messages.update') ?? 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Black Point Modal -->
    <div class="modal fade" id="deleteBlackPointModal" tabindex="-1" aria-labelledby="deleteBlackPointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteBlackPointModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.confirm_delete') ?? 'Confirm Deletion' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteBlackPointForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>{{ __('messages.warning') ?? 'Warning' }}!</strong>
                            <p class="mb-0 mt-2" id="deleteBlackPointMessage">
                                {{ __('messages.confirm_delete_black_point') ?? 'Are you sure you want to delete this black point? This action cannot be undone.' }}
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Journey Modal -->
    <div class="modal fade" id="deleteJourneyModal" tabindex="-1" aria-labelledby="deleteJourneyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteJourneyModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.confirm_delete') ?? 'Confirm Deletion' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteJourneyForm" method="POST" action="#">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>{{ __('messages.warning') ?? 'Warning' }}!</strong>
                            <p class="mb-0 mt-2">
                                {{ __('messages.confirm_delete_journey') ?? 'Are you sure you want to delete this journey? This action cannot be undone and will delete all associated black points and checklists.' }}
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') ?? 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.L) {
            console.error('Leaflet library not loaded');
            return;
        }

        // Main Journey Map
        const journeyMapContainer = document.getElementById('journey-map');
        let journeyMap = null;
        let fromMarker = null;
        let toMarker = null;
        let routeLine = null;
        let routeShadow = null;
        let blackPointMarkers = [];

        // Helper: fetch and draw professional road route using OSRM
        async function fetchRouteAndDrawOnJourneyMap(fromLat, fromLng, toLat, toLng) {
            if (!journeyMap) return;

            try {
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
                const response = await fetch(osrmUrl);

                if (!response.ok) {
                    throw new Error('Route service unavailable');
                }

                const data = await response.json();
                if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                    throw new Error('No route found');
                }

                const route = data.routes[0];
                const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // [lng, lat] → [lat, lng]

                // Remove existing route layers
                if (routeLine) {
                    journeyMap.removeLayer(routeLine);
                }
                if (routeShadow) {
                    journeyMap.removeLayer(routeShadow);
                }

                // Shadow (below)
                routeShadow = L.polyline(coordinates, {
                    color: '#1e40af',
                    weight: 7,
                    opacity: 0.3,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(journeyMap);

                // Main route (on top)
                routeLine = L.polyline(coordinates, {
                    color: '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(journeyMap);

                // Fit map to show from/to, route, and black points
                const group = new L.featureGroup([fromMarker, toMarker, routeLine, ...blackPointMarkers]);
                journeyMap.fitBounds(group.getBounds().pad(0.1));
            } catch (error) {
                console.warn('Failed to fetch OSRM route, using straight line:', error);

                // Fallback: simple straight line
                if (routeLine) {
                    journeyMap.removeLayer(routeLine);
                }
                if (routeShadow) {
                    journeyMap.removeLayer(routeShadow);
                }

                routeLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.75,
                    dashArray: '10,5',
                }).addTo(journeyMap);

                const group = new L.featureGroup([fromMarker, toMarker, routeLine, ...blackPointMarkers]);
                journeyMap.fitBounds(group.getBounds().pad(0.1));
            }
        }

        if (journeyMapContainer) {
            // Calculate center point between from and to
            const fromLat = {{ $journey->from_latitude }};
            const fromLng = {{ $journey->from_longitude }};
            const toLat = {{ $journey->to_latitude }};
            const toLng = {{ $journey->to_longitude }};
            
            const centerLat = (fromLat + toLat) / 2;
            const centerLng = (fromLng + toLng) / 2;

            journeyMap = L.map(journeyMapContainer).setView([centerLat, centerLng], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(journeyMap);

            // Add from marker
            const fromLocationName = @json($journey->from_location_name);
            let fromPopupContent = '<strong>{{ __('messages.from_location') ?? 'From Location' }}</strong>';
            if (fromLocationName) {
                fromPopupContent += '<br><span class="text-primary fw-semibold">' + fromLocationName + '</span>';
            }else{
                fromPopupContent += '<br><span class="text-primary fw-semibold">' + fromLat + ', ' + fromLng + '</span>';
            }
            
            fromMarker = L.marker([fromLat, fromLng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">F</span></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(journeyMap).bindPopup(fromPopupContent);

            // Add to marker
            const toLocationName = @json($journey->to_location_name);
            let toPopupContent = '<strong>{{ __('messages.to_location') ?? 'To Location' }}</strong>';
            if (toLocationName) {
                toPopupContent += '<br><span class="text-primary fw-semibold">' + toLocationName + '</span>';
            }else{
                toPopupContent += '<br><span class="text-primary fw-semibold">' + toLat + ', ' + toLng + '</span>';
            }
            
            toMarker = L.marker([toLat, toLng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">T</span></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(journeyMap).bindPopup(toPopupContent);

            // Add black points markers
            const blackPoints = @json($journey->blackPoints);
            blackPoints.forEach(function(point) {
                const iconColor = '#dc3545'; // Default red color for black points
                
                const marker = L.marker([parseFloat(point.latitude), parseFloat(point.longitude)], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: `<div class="custom-marker-pin" style="background-color: ${iconColor};"><i class="bi bi-exclamation-triangle-fill"></i></div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                }).addTo(journeyMap);
                
                marker.bindPopup(`
                    <div class="p-2">
                        <h6 class="fw-bold mb-2">${point.name}</h6>
                        ${point.description ? `<p class="mb-1">${point.description}</p>` : ''}
                    </div>
                `);
                
                blackPointMarkers.push(marker);
            });

            // Draw professional route line (roads / autoroute) between from and to
            fetchRouteAndDrawOnJourneyMap(fromLat, fromLng, toLat, toLng);

            // Click on map to add black point
            journeyMap.on('click', function(e) {
                const addBtn = document.getElementById('addBlackPointBtn');
                if (addBtn) {
                    addBtn.click();
                    // Set coordinates in the modal map
                    setTimeout(function() {
                        if (blackPointMap) {
                            blackPointMap.setView([e.latlng.lat, e.latlng.lng], 13);
                            if (blackPointMarker) {
                                blackPointMarker.setLatLng([e.latlng.lat, e.latlng.lng]);
                            } else {
                                blackPointMarker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(blackPointMap);
                            }
                            blackPointLat = e.latlng.lat;
                            blackPointLng = e.latlng.lng;
                            updateBlackPointCoordsLabel();
                            document.getElementById('black_point_latitude').value = blackPointLat.toFixed(6);
                            document.getElementById('black_point_longitude').value = blackPointLng.toFixed(6);
                        }
                    }, 300);
                }
            });
        }

        // Add Black Point Map
        let blackPointMap = null;
        let blackPointMarker = null;
        let blackPointRouteLine = null;
        let blackPointRouteShadow = null;
        let blackPointFromMarker = null;
        let blackPointToMarker = null;
        let blackPointExistingMarkers = [];
        let blackPointLat = null;
        let blackPointLng = null;

        // Helper: fetch and draw route on black point modal map
        async function fetchRouteAndDrawOnBlackPointMap(fromLat, fromLng, toLat, toLng) {
            if (!blackPointMap) return;

            try {
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
                const response = await fetch(osrmUrl);

                if (!response.ok) {
                    throw new Error('Route service unavailable');
                }

                const data = await response.json();
                if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                    throw new Error('No route found');
                }

                const route = data.routes[0];
                const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // [lng, lat] → [lat, lng]

                // Remove existing route layers
                if (blackPointRouteLine) {
                    blackPointMap.removeLayer(blackPointRouteLine);
                }
                if (blackPointRouteShadow) {
                    blackPointMap.removeLayer(blackPointRouteShadow);
                }

                // Shadow (below)
                blackPointRouteShadow = L.polyline(coordinates, {
                    color: '#1e40af',
                    weight: 7,
                    opacity: 0.3,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(blackPointMap);

                // Main route (on top)
                blackPointRouteLine = L.polyline(coordinates, {
                    color: '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(blackPointMap);

                // Fit map to show from/to, route, existing black points, and new marker
                const allLayers = [blackPointFromMarker, blackPointToMarker, blackPointRouteLine, ...blackPointExistingMarkers];
                if (blackPointMarker) {
                    allLayers.push(blackPointMarker);
                }
                const group = new L.featureGroup(allLayers);
                blackPointMap.fitBounds(group.getBounds().pad(0.1));
            } catch (error) {
                console.warn('Failed to fetch OSRM route, using straight line:', error);

                // Fallback: simple straight line
                if (blackPointRouteLine) {
                    blackPointMap.removeLayer(blackPointRouteLine);
                }
                if (blackPointRouteShadow) {
                    blackPointMap.removeLayer(blackPointRouteShadow);
                }

                blackPointRouteLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.75,
                    dashArray: '10,5',
                }).addTo(blackPointMap);

                const allLayers = [blackPointFromMarker, blackPointToMarker, blackPointRouteLine, ...blackPointExistingMarkers];
                if (blackPointMarker) {
                    allLayers.push(blackPointMarker);
                }
                const group = new L.featureGroup(allLayers);
                blackPointMap.fitBounds(group.getBounds().pad(0.1));
            }
        }

        const addBlackPointModal = document.getElementById('addBlackPointModal');
        if (addBlackPointModal) {
            // Get journey coordinates once
            const fromLat = parseFloat(@json($journey->from_latitude));
            const fromLng = parseFloat(@json($journey->from_longitude));
            const toLat = parseFloat(@json($journey->to_latitude));
            const toLng = parseFloat(@json($journey->to_longitude));

            // Initialize map when modal is shown (not when it starts showing)
            addBlackPointModal.addEventListener('shown.bs.modal', function () {
                const mapContainer = document.getElementById('black-point-map');
                if (!mapContainer) return;

                // Calculate center
                const centerLat = (fromLat + toLat) / 2;
                const centerLng = (fromLng + toLng) / 2;

                if (!blackPointMap) {
                    // Initialize map
                    blackPointMap = L.map(mapContainer, {
                        zoomControl: true,
                        scrollWheelZoom: true
                    }).setView([centerLat, centerLng], 8);

                    // Add tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(blackPointMap);

                    // Add from marker
                    const blackPointFromLocationName = @json($journey->from_location_name);
                    let blackPointFromPopupContent = '<strong>{{ __('messages.from_location') ?? 'From Location' }}</strong>';
                    if (blackPointFromLocationName) {
                        blackPointFromPopupContent += '<br><span class="text-primary fw-semibold">' + blackPointFromLocationName + '</span>';
                    }
                    blackPointFromPopupContent += '<br><small class="text-muted">' + fromLat + ', ' + fromLng + '</small>';
                    
                    blackPointFromMarker = L.marker([fromLat, fromLng], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">F</span></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        })
                    }).addTo(blackPointMap).bindPopup(blackPointFromPopupContent);

                    // Add to marker
                    const blackPointToLocationName = @json($journey->to_location_name);
                    let blackPointToPopupContent = '<strong>{{ __('messages.to_location') ?? 'To Location' }}</strong>';
                    if (blackPointToLocationName) {
                        blackPointToPopupContent += '<br><span class="text-primary fw-semibold">' + blackPointToLocationName + '</span>';
                    }
                    blackPointToPopupContent += '<br><small class="text-muted">' + toLat + ', ' + toLng + '</small>';
                    
                    blackPointToMarker = L.marker([toLat, toLng], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">T</span></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        })
                    }).addTo(blackPointMap).bindPopup(blackPointToPopupContent);

                    // Add existing black points markers
                    const blackPoints = @json($journey->blackPoints);
                    blackPoints.forEach(function(point) {
                        const iconColor = '#dc3545';
                        
                        const marker = L.marker([parseFloat(point.latitude), parseFloat(point.longitude)], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: `<div class="custom-marker-pin" style="background-color: ${iconColor};"><i class="bi bi-exclamation-triangle-fill"></i></div>`,
                                iconSize: [30, 30],
                                iconAnchor: [15, 30]
                            })
                        }).addTo(blackPointMap);
                        
                        marker.bindPopup(`
                            <div class="p-2">
                                <h6 class="fw-bold mb-2">${point.name}</h6>
                                ${point.description ? `<p class="mb-1">${point.description}</p>` : ''}
                            </div>
                        `);
                        
                        blackPointExistingMarkers.push(marker);
                    });

                    // Draw route
                    fetchRouteAndDrawOnBlackPointMap(fromLat, fromLng, toLat, toLng);

                    // Click on map to add new black point
                    blackPointMap.on('click', function (e) {
                        blackPointLat = e.latlng.lat;
                        blackPointLng = e.latlng.lng;
                        if (blackPointMarker) {
                            blackPointMarker.setLatLng([blackPointLat, blackPointLng]);
                        } else {
                            blackPointMarker = L.marker([blackPointLat, blackPointLng], {
                                icon: L.divIcon({
                                    className: 'custom-marker',
                                    html: '<div class="custom-marker-pin" style="background-color: #ffc107;"><i class="bi bi-plus-circle-fill"></i></div>',
                                    iconSize: [30, 30],
                                    iconAnchor: [15, 30]
                                })
                            }).addTo(blackPointMap);
                        }
                        updateBlackPointCoordsLabel();
                        document.getElementById('black_point_latitude').value = blackPointLat.toFixed(6);
                        document.getElementById('black_point_longitude').value = blackPointLng.toFixed(6);
                    });
                }

                // Always invalidate size when modal is shown to fix display issues
                // Use multiple timeouts to ensure map renders properly
                setTimeout(function() {
                    if (blackPointMap) {
                        blackPointMap.invalidateSize();
                        // Re-center and fit bounds after size is corrected
                        const allLayers = [blackPointFromMarker, blackPointToMarker, ...blackPointExistingMarkers];
                        if (blackPointRouteLine) {
                            allLayers.push(blackPointRouteLine);
                        }
                        if (allLayers.length > 0) {
                            const group = new L.featureGroup(allLayers);
                            blackPointMap.fitBounds(group.getBounds().pad(0.1));
                        }
                    }
                }, 100);
                
                setTimeout(function() {
                    if (blackPointMap) {
                        blackPointMap.invalidateSize();
                    }
                }, 300);
            });

            // Reset form when modal is hidden
            addBlackPointModal.addEventListener('hidden.bs.modal', function () {
                blackPointLat = null;
                blackPointLng = null;
                if (blackPointMarker) {
                    blackPointMap.removeLayer(blackPointMarker);
                    blackPointMarker = null;
                }
                document.getElementById('black_point_name').value = '';
                document.getElementById('black_point_description').value = '';
                document.getElementById('black_point_latitude').value = '';
                document.getElementById('black_point_longitude').value = '';
                const label = document.getElementById('black-point-coordinates-label');
                if (label) {
                    label.innerHTML = '{{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}';
                }
            });
        }

        function updateBlackPointCoordsLabel() {
            const label = document.getElementById('black-point-coordinates-label');
            if (label && blackPointLat !== null && blackPointLng !== null) {
                const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                label.innerHTML = coordsLabel + ': <span class="fw-semibold">' + blackPointLat.toFixed(6) + ', ' + blackPointLng.toFixed(6) + '</span>';
            }
        }

        // Edit Black Point Map
        let editBlackPointMap = null;
        let editBlackPointMarker = null;
        let editBlackPointLat = null;
        let editBlackPointLng = null;

        const editBlackPointModal = document.getElementById('editBlackPointModal');
        if (editBlackPointModal) {
            editBlackPointModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const blackPointId = button.getAttribute('data-black-point-id');
                const name = button.getAttribute('data-name');
                const lat = parseFloat(button.getAttribute('data-latitude'));
                const lng = parseFloat(button.getAttribute('data-longitude'));
                const description = button.getAttribute('data-description');

                // Set form values
                document.getElementById('edit_black_point_name').value = name || '';
                document.getElementById('edit_black_point_description').value = description || '';
                document.getElementById('edit_black_point_latitude').value = lat.toFixed(6);
                document.getElementById('edit_black_point_longitude').value = lng.toFixed(6);

                // Set update URL
                const form = document.getElementById('editBlackPointForm');
                form.action = '{{ url('/journeys/black-points') }}/' + blackPointId;

                // Initialize map
                if (!editBlackPointMap) {
                    const mapContainer = document.getElementById('edit-black-point-map');
                    editBlackPointMap = L.map(mapContainer).setView([lat, lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(editBlackPointMap);

                    editBlackPointMarker = L.marker([lat, lng]).addTo(editBlackPointMap);
                    editBlackPointLat = lat;
                    editBlackPointLng = lng;
                    updateEditBlackPointCoordsLabel();

                    editBlackPointMap.on('click', function (e) {
                        editBlackPointLat = e.latlng.lat;
                        editBlackPointLng = e.latlng.lng;
                        editBlackPointMarker.setLatLng([editBlackPointLat, editBlackPointLng]);
                        updateEditBlackPointCoordsLabel();
                        document.getElementById('edit_black_point_latitude').value = editBlackPointLat.toFixed(6);
                        document.getElementById('edit_black_point_longitude').value = editBlackPointLng.toFixed(6);
                    });
                } else {
                    editBlackPointMap.setView([lat, lng], 13);
                    editBlackPointMarker.setLatLng([lat, lng]);
                    editBlackPointLat = lat;
                    editBlackPointLng = lng;
                    updateEditBlackPointCoordsLabel();
                    editBlackPointMap.invalidateSize();
                }
            });
        }

        function updateEditBlackPointCoordsLabel() {
            const label = document.getElementById('edit-black-point-coordinates-label');
            if (label && editBlackPointLat !== null && editBlackPointLng !== null) {
                const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                label.innerHTML = coordsLabel + ': <span class="fw-semibold">' + editBlackPointLat.toFixed(6) + ', ' + editBlackPointLng.toFixed(6) + '</span>';
            }
        }

        // Delete Black Point Modal
        const deleteBlackPointModal = document.getElementById('deleteBlackPointModal');
        if (deleteBlackPointModal) {
            deleteBlackPointModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const deleteUrl = button.getAttribute('data-delete-url');
                const blackPointName = button.getAttribute('data-black-point-name');

                const form = document.getElementById('deleteBlackPointForm');
                if (form && deleteUrl) {
                    form.action = deleteUrl;
                }

                const message = document.getElementById('deleteBlackPointMessage');
                if (message && blackPointName) {
                    message.textContent = '{{ __('messages.confirm_delete_black_point_name') ?? 'Are you sure you want to delete the black point' }} "' + blackPointName + '"? {{ __('messages.action_cannot_be_undone') ?? 'This action cannot be undone.' }}';
                }
            });
        }

        // Form validation
        const addBlackPointForm = document.getElementById('addBlackPointForm');
        if (addBlackPointForm) {
            addBlackPointForm.addEventListener('submit', function (e) {
                const lat = document.getElementById('black_point_latitude')?.value;
                const lng = document.getElementById('black_point_longitude')?.value;

                if (!lat || !lng) {
                    e.preventDefault();
                    alert(@json(__('messages.location_required') ?? 'Please select a location on the map before submitting.'));
                    return false;
                }
            });
        }
    });
    </script>

    <style>
        .custom-marker {
            background: transparent;
            border: none;
        }
        .custom-marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .custom-marker-pin .bi {
            transform: rotate(45deg);
        }
        .custom-marker-letter {
            transform: rotate(45deg);
        }
    </style>
    @endpush

    <script>
    // Load html2canvas and send map screenshot with checklist PDF download
    (function () {
        function initChecklistPdfButtons() {
            var buttons = document.querySelectorAll('.js-checklist-pdf-btn');
            if (!buttons.length) return;

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = btn.getAttribute('data-pdf-url');
                    var mapEl = document.getElementById('journey-map');

                    if (!window.html2canvas || !mapEl) {
                        window.location.href = url;
                        return;
                    }

                    btn.disabled = true;

                    window.html2canvas(mapEl, {
                        backgroundColor: '#ffffff',
                        scale: 2,
                        logging: false,
                        useCORS: true,
                        allowTaint: false,
                        width: mapEl.offsetWidth,
                        height: mapEl.offsetHeight
                    }).then(function (canvas) {
                        var img = canvas.toDataURL('image/png');

                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;

                        var csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';

                        var mapInput = document.createElement('input');
                        mapInput.type = 'hidden';
                        mapInput.name = 'map_image';
                        mapInput.value = img;

                        form.appendChild(csrf);
                        form.appendChild(mapInput);
                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);

                        btn.disabled = false;
                    }).catch(function (err) {
                        console.error('Failed to capture map:', err);
                        window.location.href = url;
                        btn.disabled = false;
                    });
                });
            });
        }

        // Load html2canvas from CDN if not already loaded
        if (!window.html2canvas) {
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = initChecklistPdfButtons;
            document.head.appendChild(script);
        } else {
            initChecklistPdfButtons();
        }
    })();
    </script>

    {{-- Create Checklist Modal --}}
    @if(isset($checklistItems) && $checklistItems->count() > 0)
        <div class="modal fade" id="createChecklistModal" tabindex="-1" aria-labelledby="createChecklistModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="createChecklistModalLabel">
                            <i class="bi bi-clipboard-plus me-2 text-primary"></i>
                            {{ __('messages.new_checklist') ?? 'New checklist' }} - {{ $journey->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('journeys.checklists.store', $journey) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <p class="text-muted mb-3">
                                {{ __('messages.checklist_required_help') ?? 'Please complete the checklist for this journey. Fill weight (1-10) and score (1-5) for each item.' }}
                            </p>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">{{ __('messages.item') ?? 'Item' }}</th>
                                            <th style="width: 15%;" class="text-center">{{ __('messages.weight') ?? 'Weight' }} (1-10)</th>
                                            <th style="width: 15%;" class="text-center">{{ __('messages.score') ?? 'Score' }} (1-5)</th>
                                            <th style="width: 15%;" class="text-center">{{ __('messages.note') ?? 'Note' }}</th>
                                            <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($checklistItems as $item)
                                            <tr>
                                                <td class="align-middle">
                                                    <div>
                                                        <strong>{{ $item->donnees }}</strong>
                                                        @if($item->cirees_appreciation)
                                                            <br><small class="text-muted">{{ $item->cirees_appreciation }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="number" 
                                                           class="form-control form-control-sm text-center checklist-weight" 
                                                           name="checklist[{{ $item->id }}][weight]" 
                                                           id="modal_checklist_{{ $item->id }}_weight"
                                                           min="1" 
                                                           max="10" 
                                                           value="1"
                                                           required
                                                           data-item-id="{{ $item->id }}">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="number" 
                                                           class="form-control form-control-sm text-center checklist-score" 
                                                           name="checklist[{{ $item->id }}][score]" 
                                                           id="modal_checklist_{{ $item->id }}_score"
                                                           min="1" 
                                                           max="5" 
                                                           value="1"
                                                           required
                                                           data-item-id="{{ $item->id }}">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="text" 
                                                           class="form-control form-control-sm text-center checklist-note" 
                                                           id="modal_checklist_{{ $item->id }}_note"
                                                           value="1"
                                                           readonly
                                                           style="background-color: #f8f9fa;">
                                                    <input type="hidden" 
                                                           name="checklist[{{ $item->id }}][note]" 
                                                           id="modal_checklist_{{ $item->id }}_note_hidden"
                                                           value="1">
                                                </td>
                                                <td class="align-middle">
                                                    <textarea class="form-control form-control-sm" 
                                                              name="checklist[{{ $item->id }}][comment]" 
                                                              id="modal_checklist_{{ $item->id }}_comment"
                                                              rows="2" 
                                                              placeholder="{{ __('messages.comment_optional') ?? 'Comment (optional)' }}"></textarea>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <hr class="my-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ __('messages.general_comment') ?? 'General Comment' }}
                                </label>
                                <textarea class="form-control"
                                          name="checklist_notes"
                                          rows="3"
                                          placeholder="{{ __('messages.general_comment_placeholder') ?? 'Enter any general comments or notes about this checklist...' }}"></textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.status') ?? 'Status' }}
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio"
                                               class="btn-check"
                                               name="checklist_status"
                                               id="modal_checklist_status_accepted"
                                               value="accepted"
                                               checked>
                                        <label class="btn btn-outline-success" for="modal_checklist_status_accepted">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ __('messages.accepted') ?? 'Accepted' }}
                                        </label>

                                        <input type="radio"
                                               class="btn-check"
                                               name="checklist_status"
                                               id="modal_checklist_status_rejected"
                                               value="rejected">
                                        <label class="btn btn-outline-danger" for="modal_checklist_status_rejected">
                                            <i class="bi bi-x-circle me-1"></i>
                                            {{ __('messages.rejected') ?? 'Rejected' }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_checklist_documents" class="form-label fw-semibold">
                                        <i class="bi bi-images me-2"></i>
                                        {{ __('messages.pictures') ?? 'Pictures' }}
                                    </label>
                                    <input type="file"
                                           class="form-control"
                                           id="modal_checklist_documents"
                                           name="checklist_documents[]"
                                           multiple
                                           accept="image/*">
                                    <small class="text-muted">
                                        {{ __('messages.pictures_help') ?? 'You can select multiple pictures. Accepted formats: JPG, PNG, GIF.' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.save') ?? 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate note (weight × score) for journey checklist modal
            document.querySelectorAll('#createChecklistModal .checklist-weight, #createChecklistModal .checklist-score').forEach(function(input) {
                input.addEventListener('input', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const weightInput = document.getElementById('modal_checklist_' + itemId + '_weight');
                    const scoreInput = document.getElementById('modal_checklist_' + itemId + '_score');
                    const noteInput = document.getElementById('modal_checklist_' + itemId + '_note');
                    const noteHiddenInput = document.getElementById('modal_checklist_' + itemId + '_note_hidden');

                    if (weightInput && scoreInput && noteInput && noteHiddenInput) {
                        const weight = parseFloat(weightInput.value) || 0;
                        const score = parseFloat(scoreInput.value) || 0;
                        const note = weight * score;
                        
                        noteInput.value = note.toFixed(2);
                        noteHiddenInput.value = note.toFixed(2);
                    }
                });
            });
        });
        </script>
    @endif
</x-app-layout>

