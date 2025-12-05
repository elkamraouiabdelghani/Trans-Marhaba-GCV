<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container -->
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
    </div>

    <div class="container-fluid py-4 mt-4">
        <!-- Year Selector -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                        {{ __('messages.tbt_planning_title') }} - {{ $year }}
                    </h4>
                    <div class="d-flex gap-2">
                        <select id="yearSelector" class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('tbt-formations.planning') }}?year=' + this.value">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('tbt-formations.planning.pdf', ['year' => $year]) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i>
                            {{ __('messages.download_pdf') }}
                        </a>
                        <a href="{{ route('tbt-formations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-list me-1"></i>
                            {{ __('messages.tbt_planning_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered mb-0" style="min-width: 100%;">
                        <tbody>
                            @foreach($calendarData as $month => $monthData)
                                @php
                                    $weekCount = count($monthData['weeks']);
                                @endphp
                                
                                <!-- Month Name Row -->
                                <tr class="table-secondary">
                                    <td rowspan="5" class="text-center align-middle fw-bold" style="min-width: 120px; position: sticky; left: 0; background: #e9ecef; z-index: 10;">
                                        {{ $monthData['shortName'] }}
                                    </td>
                                </tr>
                                
                                <!-- Week Numbers Row -->
                                <tr>
                                    @foreach($monthData['weeks'] as $weekIndex => $week)
                                        <td class="text-center align-middle fw-bold" style="min-width: 140px;">
                                            {{ __('messages.tbt_planning_week') }}{{ $weekIndex + 1 }}
                                        </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Day Names Row -->
                                <tr>
                                    @foreach($monthData['weeks'] as $weekIndex => $week)
                                        <td class="text-center align-middle" style="font-size: 0.85rem;">
                                            @php
                                                $dayNames = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
                                                $dayCount = 0;
                                                foreach($week as $key => $day) {
                                                    if($key === 'formation') continue;
                                                    $dayCount++;
                                                }
                                            @endphp
                                            @for($i = 0; $i < 7; $i++)
                                                @if($i < $dayCount)
                                                    {{ $dayNames[$i] }}@if($i < 6) @endif
                                                @endif
                                            @endfor
                                        </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Day Numbers Row -->
                                <tr>
                                    @foreach($monthData['weeks'] as $weekIndex => $week)
                                        <td class="text-center align-middle" style="font-size: 0.85rem;">
                                            @php
                                                $dayNumbers = [];
                                                foreach($week as $key => $day) {
                                                    if($key === 'formation') continue;
                                                    $dayNumbers[] = [
                                                        'value' => $day['day'],
                                                        'inMonth' => $day['isInMonth'] ?? false,
                                                    ];
                                                }
                                            @endphp
                                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                @foreach($dayNumbers as $dayData)
                                                    <span class="px-1 {{ $dayData['inMonth'] ? 'fw-semibold text-dark' : 'text-muted' }}">
                                                        {{ $dayData['value'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Formation Names Row -->
                                <tr>
                                    @foreach($monthData['weeks'] as $weekIndex => $week)
                                        <td class="p-2" style="vertical-align: middle; min-height: 60px;">
                                            @if(isset($week['formation']) && $week['formation'])
                                                @php
                                                    $statusClass = $week['formation']->status === 'realized' ? 'bg-success' : 'bg-warning text-dark';
                                                    $statusLabel = $week['formation']->status === 'realized'
                                                        ? __('messages.tbt_formation_status_realized')
                                                        : __('messages.tbt_formation_status_planned');
                                                @endphp
                                                <div class="bg-primary bg-opacity-10 border border-primary rounded p-2 text-center" style="cursor: pointer; min-height: 70px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;" 
                                                     onclick="window.location.href='{{ route('tbt-formations.edit', $week['formation']) }}'"
                                                     title="{{ __('messages.tbt_formation_edit') }}">
                                                    <div>
                                                        <div class="small text-dark" style="font-size: 0.75rem;">{{ Str::limit($week['formation']->title, 50) }}</div>
                                                        @if($week['formation']->participant)
                                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                                {{ Str::limit($week['formation']->participant, 45) }}
                                                            </div>
                                                        @endif
                                                        <div class="mt-1">
                                                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                @php
                                                    // Get only day entries, exclude 'formation' key
                                                    $weekDays = array_filter($week, function($key) {
                                                        return is_numeric($key);
                                                    }, ARRAY_FILTER_USE_KEY);
                                                    
                                                    // Reset array keys to get proper indices
                                                    $weekDays = array_values($weekDays);
                                                    $weekCount = count($weekDays);
                                                    
                                                    // Get start and end dates
                                                    $weekStartDate = ($weekCount > 0 && isset($weekDays[0]['date'])) ? $weekDays[0]['date'] : null;
                                                    $weekEndDate = ($weekCount > 0 && isset($weekDays[$weekCount - 1]['date'])) ? $weekDays[$weekCount - 1]['date'] : null;
                                                @endphp
                                                @if($weekStartDate && $weekEndDate)
                                                    @php
                                                        $monthFromDate = $weekStartDate->month;
                                                    @endphp
                                                    <div class="bg-light border border-secondary rounded p-2 text-center" style="cursor: pointer; min-height: 50px; display: flex; align-items: center; justify-content: center;"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#addFormationModal"
                                                         data-year="{{ $year }}"
                                                         data-month="{{ $monthFromDate }}"
                                                         data-week-start="{{ $weekStartDate->format('Y-m-d') }}"
                                                         data-week-end="{{ $weekEndDate->format('Y-m-d') }}"
                                                         title="{{ __('messages.tbt_planning_add_week') }}">
                                                        <div class="text-muted small">
                                                            <i class="bi bi-plus-circle"></i><br>
                                                            <span style="font-size: 0.7rem;">{{ __('messages.tbt_planning_no_formation') }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="bg-light border border-secondary rounded p-2 text-center" style="min-height: 50px; display: flex; align-items: center; justify-content: center;">
                                                        <div class="text-muted small">
                                                            <span style="font-size: 0.7rem;">{{ __('messages.tbt_planning_no_formation') }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Spacer row between months -->
                                @if($month < 12)
                                    <tr>
                                        <td colspan="{{ $weekCount + 1 }}" style="height: 20px; background-color: #f8f9fa;"></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Formation Modal -->
    <div class="modal fade" id="addFormationModal" tabindex="-1" aria-labelledby="addFormationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFormationModalLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.tbt_formations_create_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formationForm" action="{{ route('tbt-formations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="from_planning" value="1">

                        <div class="mb-3">
                            <label for="modal_title" class="form-label">{{ __('messages.tbt_formation_title') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="modal_title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modal_participant" class="form-label">{{ __('messages.tbt_formation_participant') }}</label>
                            <textarea class="form-control @error('participant') is-invalid @enderror"
                                     id="modal_participant"
                                     name="participant"
                                     rows="2">{{ old('participant') }}</textarea>
                            @error('participant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modal_status" class="form-label">{{ __('messages.tbt_formation_status') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="modal_status"
                                    name="status"
                                    required>
                                <option value="planned" {{ old('status', 'planned') === 'planned' ? 'selected' : '' }}>
                                    {{ __('messages.tbt_formation_status_planned') }}
                                </option>
                                <option value="realized" {{ old('status') === 'realized' ? 'selected' : '' }}>
                                    {{ __('messages.tbt_formation_status_realized') }}
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modal_description" class="form-label">{{ __('messages.tbt_formation_description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="modal_description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-3">
                        <h6 class="mb-3">{{ __('messages.tbt_formation_planning') }}</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_year" class="form-label">{{ __('messages.tbt_formation_year') }} <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('year') is-invalid @enderror" 
                                       id="modal_year" 
                                       name="year" 
                                       value="{{ old('year', $year ?? date('Y')) }}" 
                                       min="2000" 
                                       max="2100"
                                       required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="modal_month" class="form-label">{{ __('messages.tbt_formation_month') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('month') is-invalid @enderror" 
                                        id="modal_month" 
                                        name="month"
                                        required>
                                    <option value="">{{ __('messages.tbt_formation_select_month') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('fr')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_week_start_date" class="form-label">{{ __('messages.tbt_formation_week_start') }} <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('week_start_date') is-invalid @enderror" 
                                       id="modal_week_start_date" 
                                       name="week_start_date" 
                                       value="{{ old('week_start_date') }}" 
                                       required>
                                @error('week_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.tbt_formation_week_monday') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="modal_week_end_date" class="form-label">{{ __('messages.tbt_formation_week_end') }} <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('week_end_date') is-invalid @enderror" 
                                       id="modal_week_end_date" 
                                       name="week_end_date" 
                                       value="{{ old('week_end_date') }}" 
                                       required>
                                @error('week_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.tbt_formation_week_sunday') }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_notes" class="form-label">{{ __('messages.tbt_formation_notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="modal_notes" 
                                      name="notes" 
                                      rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modal_documents" class="form-label fw-semibold">{{ __('messages.upload_documents') }}</label>
                            <input type="file"
                                   id="modal_documents"
                                   name="documents[]"
                                   class="form-control @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror"
                                   multiple>
                            <small class="text-muted">{{ __('messages.max_file_size') ?? 'Max file size' }}: 10MB</small>
                            @error('documents')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('documents.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="modal_is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="modal_is_active">
                                {{ __('messages.tbt_formation_active') }}
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        {{ __('messages.tbt_formation_cancel') }}
                    </button>
                    <button type="submit" form="formationForm" class="btn btn-dark">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.tbt_formation_save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th, .table td {
            border: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .bg-primary.bg-opacity-10:hover {
            background-color: rgba(13, 110, 253, 0.2) !important;
        }
        .bg-light:hover {
            background-color: #e9ecef !important;
        }
        .table-secondary td {
            background-color: #e9ecef !important;
        }
    </style>

    <script>
        // Handle modal opening and pre-fill form
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('addFormationModal');
            if (modal) {
                modal.addEventListener('show.bs.modal', function(event) {
                    // Button that triggered the modal
                    const button = event.relatedTarget;
                    
                    // Extract data from data attributes
                    const year = button.getAttribute('data-year');
                    const month = button.getAttribute('data-month');
                    const weekStart = button.getAttribute('data-week-start');
                    const weekEnd = button.getAttribute('data-week-end');
                    
                    // Pre-fill form fields
                    document.getElementById('modal_year').value = year || '';
                    document.getElementById('modal_month').value = month || '';
                    document.getElementById('modal_week_start_date').value = weekStart || '';
                    document.getElementById('modal_week_end_date').value = weekEnd || '';
                    
                    // Clear other fields
                    document.getElementById('modal_title').value = '';
                    document.getElementById('modal_participant').value = '';
                    document.getElementById('modal_status').value = 'planned';
                    document.getElementById('modal_description').value = '';
                    document.getElementById('modal_notes').value = '';
                    document.getElementById('modal_documents').value = '';
                    document.getElementById('modal_is_active').checked = true;
                });
            }

            // Auto-update month when week_start_date changes
            const weekStartInput = document.getElementById('modal_week_start_date');
            if (weekStartInput) {
                weekStartInput.addEventListener('change', function() {
                    const startDate = new Date(this.value);
                    if (startDate && !isNaN(startDate.getTime())) {
                        const month = startDate.getMonth() + 1; // JavaScript months are 0-indexed
                        document.getElementById('modal_month').value = month;
                    }
                });
            }
        });
    </script>
</x-app-layout>
