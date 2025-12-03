<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-1 fw-bold text-dark fs-4">
                    <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                    {{ $restPoint->name }}
                </h2>
                <p class="text-muted mb-0">
                    {{ __('messages.rest_point_details') ?? 'Rest Point Details' }}
                </p>
            </div>
            <div>
                <a href="{{ route('rest-points.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.back_to_list') ?? 'Back to list' }}
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>{{ __('messages.form_fix_errors') ?? 'Please correct the following errors:' }}</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- Main Section --}}
            <div class="col-lg-8">
                {{-- Rest Point Information Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.rest_point_information') ?? 'Rest Point Information' }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.name') ?? 'Name' }}
                                </label>
                                <p class="mb-0 fw-semibold">{{ $restPoint->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.type') ?? 'Type' }}
                                </label>
                                <p class="mb-0">
                                    <span class="badge bg-primary">
                                        {{ $types[$restPoint->type] ?? $restPoint->type }}
                                    </span>
                                </p>
                            </div>
                            @if($restPoint->description)
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.description') ?? 'Description' }}
                                    </label>
                                    <p class="mb-0">{{ $restPoint->description }}</p>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.created_at') ?? 'Created At' }}
                                </label>
                                <p class="mb-0">
                                    {{ $restPoint->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @if($restPoint->createdBy)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.created_by') ?? 'Created By' }}
                                    </label>
                                    <p class="mb-0">{{ $restPoint->createdBy->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Map Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-map me-2 text-primary"></i>
                            {{ __('messages.location') ?? 'Location' }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="rest-point-map" style="width: 100%; height: 400px; border-radius: 0 0 0.5rem 0.5rem;"></div>
                        <div class="p-3 bg-light">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ __('messages.coordinates') ?? 'Coordinates' }}: 
                                <strong>{{ $restPoint->latitude }}, {{ $restPoint->longitude }}</strong>
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Checklist Results --}}
                @if($checklist && $categories->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-list-check me-2 text-primary"></i>
                                {{ __('messages.checklist_results') ?? 'Checklist Results' }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="accordion" id="checklistResultsAccordion">
                                @foreach($categories as $index => $category)
                                    <div class="accordion-item mb-2 border rounded">
                                        <h2 class="accordion-header" id="resultHeading{{ $category->id }}">
                                            <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#resultCollapse{{ $category->id }}" 
                                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                    aria-controls="resultCollapse{{ $category->id }}">
                                                <i class="bi bi-list-check me-2 text-primary"></i>
                                                <strong>{{ $category->name }}</strong>
                                                <span class="badge bg-info bg-opacity-10 text-info ms-2">
                                                    {{ $category->items->count() }} {{ __('messages.items') ?? 'items' }}
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="resultCollapse{{ $category->id }}" 
                                             class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                             aria-labelledby="resultHeading{{ $category->id }}" 
                                             data-bs-parent="#checklistResultsAccordion">
                                            <div class="accordion-body p-4">
                                                @if($category->items->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 50%;">{{ __('messages.item') ?? 'Item' }}</th>
                                                                    <th style="width: 25%;" class="text-center">{{ __('messages.answer') ?? 'Answer' }}</th>
                                                                    <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($category->items as $item)
                                                                    @php
                                                                        $answer = $answersByItemId[$item->id] ?? null;
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="align-middle">
                                                                            <label class="mb-0 fw-semibold">{{ $item->label }}</label>
                                                                        </td>
                                                                        <td class="text-center align-middle">
                                                                            @if($answer)
                                                                                @if($answer->is_checked)
                                                                                    <span class="badge bg-success">
                                                                                        <i class="bi bi-check-circle me-1"></i>
                                                                                        {{ __('messages.yes') ?? 'Yes' }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-danger">
                                                                                        <i class="bi bi-x-circle me-1"></i>
                                                                                        {{ __('messages.no') ?? 'No' }}
                                                                                    </span>
                                                                                @endif
                                                                            @else
                                                                                <span class="text-muted">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            @if($answer && $answer->comment)
                                                                                <p class="mb-0 small">{{ $answer->comment }}</p>
                                                                            @else
                                                                                <span class="text-muted">-</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="alert alert-info mb-0">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        {{ __('messages.no_items_in_category') ?? 'No active items in this category.' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Checklist Documents --}}
                @if($checklist && $checklist->documents && count($checklist->documents) > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-images me-2 text-primary"></i>
                                {{ __('messages.checklist_documents') ?? 'Checklist Documents' }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                @foreach($checklist->documents as $document)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border">
                                            <a href="{{ asset('storage/' . $document) }}" target="_blank" class="text-decoration-none">
                                                <img src="{{ asset('storage/' . $document) }}" 
                                                     alt="Document" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover; cursor: pointer;">
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar Section --}}
            <div class="col-lg-4">
                {{-- Quick Actions --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-lightning me-2 text-primary"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-2">
                            <a href="{{ route('rest-points.edit', $restPoint) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i>
                                {{ __('messages.edit') ?? 'Edit' }}
                            </a>

                            @php
                                $canCreateChecklist = false;

                                if ($restPoint->inspection_status === 'no_inspection') {
                                    // No previous checklist: allow immediately
                                    $canCreateChecklist = true;
                                } elseif ($restPoint->last_inspection_date && $restPoint->next_inspection_due_at) {
                                    // Allow new checklist starting 2 weeks before due date
                                    $openFrom = $restPoint->next_inspection_due_at->copy()->subWeeks(2);
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
                                @if($restPoint->next_inspection_due_at)
                                    <small class="text-muted mt-1 d-block">
                                        {{ __('messages.next_inspection_due') ?? 'Next inspection due' }}:
                                        {{ $restPoint->next_inspection_due_at->format('d/m/Y') }}
                                    </small>
                                @endif
                            @endif
                            <hr class="my-2">
                            {{-- Delete rest point button --}}
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteRestPointModal">
                                <i class="bi bi-trash me-1"></i>
                                {{ __('messages.delete_rest_point') ?? 'Delete rest point' }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Checklist Summary --}}
                @if($checklist)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-clipboard-check me-2 text-primary"></i>
                                {{ __('messages.checklist_summary') ?? 'Checklist Summary' }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.status') ?? 'Status' }}
                                </label>
                                <p class="mb-0">
                                    @if($checklist->status === 'accepted')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ __('messages.accepted') ?? 'Accepted' }}
                                        </span>
                                    @elseif($checklist->status === 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>
                                            {{ __('messages.rejected') ?? 'Rejected' }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ __('messages.pending') ?? 'Pending' }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            @if($checklist->completed_at)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.completed_at') ?? 'Completed At' }}
                                    </label>
                                    <p class="mb-0">{{ $checklist->completed_at->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif
                            @if($checklist->completedByUser)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.completed_by') ?? 'Completed By' }}
                                    </label>
                                    <p class="mb-0">{{ $checklist->completedByUser->name }}</p>
                                </div>
                            @endif
                            @if($checklist->notes)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.general_comment') ?? 'General Comment' }}
                                    </label>
                                    <p class="mb-0">{{ $checklist->notes }}</p>
                                </div>
                            @endif
                            @if($checklist->documents && count($checklist->documents) > 0)
                                <div>
                                    <label class="form-label fw-semibold text-muted small">
                                        {{ __('messages.pictures') ?? 'Pictures' }}
                                    </label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">
                                            {{ count($checklist->documents) }} {{ __('messages.picture') ?? 'picture' }}(s)
                                        </span>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">
                                {{ __('messages.no_checklist_found') ?? 'No checklist found for this rest point.' }}
                            </p>
                        </div>
                    </div>
                @endif
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
                                            @php
                                                $historyDue = $historyChecklist->effective_inspection_date
                                                    ? $historyChecklist->effective_inspection_date->copy()->addMonthsNoOverflow(6)
                                                    : null;
                                            @endphp
                                            {{ $historyDue ? $historyDue->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>
                                            {{ $historyChecklist->completedByUser->name ?? '-' }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary js-checklist-pdf-btn"
                                                    data-pdf-url="{{ route('rest-points.checklists.pdf', [$restPoint, $historyChecklist]) }}">
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

    {{-- Delete Rest Point Modal --}}
    <div class="modal fade" id="deleteRestPointModal" tabindex="-1" aria-labelledby="deleteRestPointModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger" id="deleteRestPointModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.confirm_delete_rest_point_title') ?? 'Delete rest point and related data' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        {{ __('messages.confirm_delete_rest_point_message') ?? 'Are you sure you want to delete this rest point? This will permanently remove this rest point, all its checklists, answers, and documents.' }}
                    </p>
                    <p class="mb-0 text-danger small">
                        {{ __('messages.action_cannot_be_undone') ?? 'This action cannot be undone.' }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <form method="POST" action="{{ route('rest-points.destroy', $restPoint) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Checklist Modal --}}
    @if(isset($categories) && $categories->count() > 0)
        <div class="modal fade" id="createChecklistModal" tabindex="-1" aria-labelledby="createChecklistModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="createChecklistModalLabel">
                            <i class="bi bi-clipboard-plus me-2 text-primary"></i>
                            {{ __('messages.new_checklist') ?? 'New checklist' }} - {{ $restPoint->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('rest-points.checklists.store', $restPoint) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <p class="text-muted mb-3">
                                {{ __('messages.checklist_required_help') ?? 'Please complete the checklist for this rest point. All items are required.' }}
                            </p>

                            <div class="accordion" id="createChecklistAccordion">
                                @foreach($categories as $index => $category)
                                    @if($category->items->count() > 0)
                                        <div class="accordion-item mb-2 border rounded">
                                            <h2 class="accordion-header" id="createHeading{{ $category->id }}">
                                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#createCollapse{{ $category->id }}"
                                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                        aria-controls="createCollapse{{ $category->id }}">
                                                    <i class="bi bi-list-check me-2 text-primary"></i>
                                                    <strong>{{ $category->name }}</strong>
                                                    <span class="badge bg-info bg-opacity-10 text-info ms-2">
                                                        {{ $category->items->count() }} {{ __('messages.items') ?? 'items' }}
                                                    </span>
                                                </button>
                                            </h2>
                                            <div id="createCollapse{{ $category->id }}"
                                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                                 aria-labelledby="createHeading{{ $category->id }}"
                                                 data-bs-parent="#createChecklistAccordion">
                                                <div class="accordion-body p-3">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 50%;">{{ __('messages.item') ?? 'Item' }}</th>
                                                                    <th style="width: 20%;" class="text-center">{{ __('messages.yes') ?? 'Yes' }} / {{ __('messages.no') ?? 'No' }}</th>
                                                                    <th style="width: 30%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($category->items as $item)
                                                                    <tr>
                                                                        <td>
                                                                            <label class="mb-0 fw-semibold">{{ $item->label }}</label>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group" role="group">
                                                                                <input type="radio"
                                                                                       class="btn-check"
                                                                                       name="answers[{{ $item->id }}][value]"
                                                                                       id="modal_item_{{ $item->id }}_yes"
                                                                                       value="yes"
                                                                                       required>
                                                                                <label class="btn btn-outline-success btn-sm" for="modal_item_{{ $item->id }}_yes">
                                                                                    <i class="bi bi-check-circle"></i> {{ __('messages.yes') ?? 'Yes' }}
                                                                                </label>

                                                                                <input type="radio"
                                                                                       class="btn-check"
                                                                                       name="answers[{{ $item->id }}][value]"
                                                                                       id="modal_item_{{ $item->id }}_no"
                                                                                       value="no"
                                                                                       required>
                                                                                <label class="btn btn-outline-danger btn-sm" for="modal_item_{{ $item->id }}_no">
                                                                                    <i class="bi bi-x-circle"></i> {{ __('messages.no') ?? 'No' }}
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <textarea class="form-control form-control-sm"
                                                                                      name="answers[{{ $item->id }}][comment]"
                                                                                      rows="2"
                                                                                      placeholder="{{ __('messages.comment_optional') ?? 'Comment (optional)' }}"></textarea>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <hr class="my-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ __('messages.general_comment') ?? 'General Comment' }}
                                </label>
                                <textarea class="form-control"
                                          name="notes"
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
    @endif
</x-app-layout>

<!-- Leaflet CSS and JS -->
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
/>
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.L) {
        console.error('Leaflet library not loaded');
        return;
    }

    const mapContainer = document.getElementById('rest-point-map');
    if (!mapContainer) {
        return;
    }

    const lat = {{ $restPoint->latitude }};
    const lng = {{ $restPoint->longitude }};

    const map = L.map(mapContainer).setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Custom parking icon (same style/color as index page markers)
    const parkingIcon = L.divIcon({
        className: 'custom-marker',
        html: `
            <div class="custom-marker-pin" style="background-color: #28a745;">
                <span class="custom-marker-letter">P</span>
            </div>
        `,
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
    });

    // Add marker for the rest point
    L.marker([lat, lng], { icon: parkingIcon })
        .addTo(map)
        .bindPopup('<strong>{{ $restPoint->name }}</strong><br>{{ $types[$restPoint->type] ?? $restPoint->type }}')
        .openPopup();
});
</script>

<script>
// Load html2canvas and send map screenshot with checklist PDF download
(function () {
    function initChecklistPdfButtons() {
        var buttons = document.querySelectorAll('.js-checklist-pdf-btn');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-pdf-url');
                var mapEl = document.getElementById('rest-point-map');

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
                    form.appendChild(csrf);

                    var imgInput = document.createElement('input');
                    imgInput.type = 'hidden';
                    imgInput.name = 'map_image';
                    imgInput.value = img;
                    form.appendChild(imgInput);

                    document.body.appendChild(form);
                    form.submit();
                }).catch(function () {
                    btn.disabled = false;
                    window.location.href = url;
                });
            });
        });
    }

    if (typeof window.html2canvas === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        s.onload = initChecklistPdfButtons;
        document.head.appendChild(s);
    } else {
        initChecklistPdfButtons();
    }
})();
</script>

<style>
.custom-marker-pin {
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    border: 3px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-marker-letter {
    transform: rotate(45deg);
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
}
</style>

