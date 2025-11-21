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
                            @if(!$availableYears->contains($year))
                                <option value="{{ $year }}" selected>{{ $year }}</option>
                            @endif
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
                                                    $weekCount = count($week);
                                                    $weekStartDate = isset($week[0]) ? $week[0]['date'] : null;
                                                    $weekEndDate = ($weekCount == 7 && isset($week[6])) ? $week[6]['date'] : (isset($week[$weekCount - 1]) ? $week[$weekCount - 1]['date'] : null);
                                                @endphp
                                                @if($weekStartDate && $weekEndDate)
                                                    <div class="bg-light border border-secondary rounded p-2 text-center" style="cursor: pointer; min-height: 50px; display: flex; align-items: center; justify-content: center;"
                                                         onclick="window.location.href='{{ route('tbt-formations.create') }}?year={{ $year }}&week_start_date={{ $weekStartDate->format('Y-m-d') }}&week_end_date={{ $weekEndDate->format('Y-m-d') }}'"
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
</x-app-layout>
