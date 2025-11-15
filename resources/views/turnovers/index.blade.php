<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-3">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-list-ul text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.total') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $totalTurnovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.pending') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $pendingTurnovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.confirmed') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $confirmedTurnovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
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

        <!-- Graph Filter Bar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('turnovers.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="graph_date_from" class="form-label small">{{ __('messages.from_date') }}</label>
                        <input type="date" 
                               class="form-control form-control-sm" 
                               id="graph_date_from" 
                               name="graph_date_from" 
                               value="{{ $graphDateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label for="graph_date_to" class="form-label small">{{ __('messages.to_date') }}</label>
                        <input type="date" 
                               class="form-control form-control-sm" 
                               id="graph_date_to" 
                               name="graph_date_to" 
                               value="{{ $graphDateTo }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-dark">
                            <i class="bi bi-search me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('turnovers.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                    <!-- Preserve other filters -->
                    @if(request('flotte'))
                        <input type="hidden" name="flotte" value="{{ request('flotte') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('date_from'))
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    @endif
                    @if(request('date_to'))
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    @endif
                </form>
            </div>
        </div>

        <!-- Turnover Percentage Graph -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        {{ __('messages.turnover_percentage') }}
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadGraphPdf">
                        <i class="bi bi-download me-1"></i>
                        {{ __('messages.download_pdf') }}
                    </button>
                </div>
            </div>
            <div class="card-body p-4" id="graphCardBody">
                <canvas id="turnoverPercentageChart" height="80"></canvas>
            </div>
        </div>

        <!-- Turnovers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        {{ __('messages.turnovers_list') ?? 'Liste des Turnovers' }}
                    </h5>
                    <a href="{{ route('turnovers.create') }}" class="btn btn-dark btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>
                        {{ __('messages.create_turnover') ?? 'Nouveau Turnover' }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.departure_date') ?? 'Date de départ' }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.flotte') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.name') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.position') ?? 'Poste' }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.departure_reason') ?? 'Raison de départ' }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.exit_interview_status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.confirmed_at') }}</th>
                                <th class="border-0 py-3 px-4 text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($turnovers as $turnover)
                                <tr>
                                    <td class="py-3 px-4">
                                        {{ $turnover->departure_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $turnover->flotte ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $turnover->person_name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $turnover->position ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $turnover->departure_reason }}">
                                            {{ Str::limit($turnover->departure_reason, 50) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($turnover->status === 'confirmed')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ __('messages.confirmed') ?? 'Confirmé' }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ __('messages.pending') ?? 'En attente' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($turnover->interview_completed)
                                            <span class="badge bg-success bg-opacity-25 text-success">
                                                <i class="bi bi-clipboard2-check me-1"></i>
                                                {{ __('messages.exit_interview_completed') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                <i class="bi bi-clipboard2 me-1"></i>
                                                {{ __('messages.exit_interview_pending') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $turnover->confirmed_at ? $turnover->confirmed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        @php
                                            $hasExitInterview = $turnover->interview_completed && $turnover->turnover_pdf_path;
                                            $canFillInterview = !empty($turnover->interview_notes) && !empty($turnover->interviewed_by);
                                        @endphp
                                        <div class="d-flex gap-2 justify-content-end group flex-wrap" role="group">
                                            <a href="{{ route('turnovers.edit', $turnover) }}" 
                                                class="btn btn-sm btn-outline-warning" 
                                                title="{{ __('messages.edit') ?? 'Modifier' }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($hasExitInterview)
                                                <a href="{{ route('turnovers.interview.download', $turnover) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="{{ __('messages.download_pdf') }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @else
                                                @if($canFillInterview)
                                                    <a href="{{ route('turnovers.interview', $turnover) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="{{ __('messages.start_exit_interview') ?? 'Commencer l\'entretien de sortie' }}">
                                                        <i class="bi bi-clipboard-plus"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('turnovers.edit', $turnover) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="{{ __('messages.start_exit_interview') ?? 'Commencer l\'entretien de sortie' }}">
                                                        <i class="bi bi-clipboard-plus"></i>
                                                    </a>
                                                @endif
                                            @endif
                                            @if(!$turnover->isConfirmed())
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="{{ __('messages.confirm_turnover') }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmTurnoverModal"
                                                        data-turnover-id="{{ $turnover->id }}"
                                                        data-turnover-name="{{ $turnover->person_name ?? 'N/A' }}"
                                                        data-turnover-date="{{ $turnover->departure_date->format('d/m/Y') }}"
                                                        data-turnover-position="{{ $turnover->position ?? 'N/A' }}"
                                                        data-turnover-driver="{{ $turnover->driver_id ? '1' : '0' }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_turnovers_found') ?? 'Aucun turnover trouvé.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($turnovers->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $turnovers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Confirm Turnover Modal -->
    <div class="modal fade" id="confirmTurnoverModal" tabindex="-1" aria-labelledby="confirmTurnoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="confirmTurnoverModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __('messages.confirm_turnover') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ __('messages.warning') }}:</strong>
                        {{ __('messages.confirm_turnover_warning') }}
                    </div>
                    <p class="mb-2"><strong>{{ __('messages.departure_date') }}:</strong> <span id="confirmTurnoverDate"></span></p>
                    <p class="mb-2"><strong>{{ __('messages.name') }}:</strong> <span id="confirmTurnoverName"></span></p>
                    <p class="mb-0"><strong>{{ __('messages.position') }}:</strong> <span id="confirmTurnoverPosition"></span></p>
                    <div id="confirmDriverWarning" class="alert alert-info mt-3 mb-0" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('messages.driver_will_be_terminated') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <form id="confirmTurnoverForm" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.confirm_turnover') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- html2canvas for capturing the graph -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Turnover Percentage Chart
            const chartCtx = document.getElementById('turnoverPercentageChart');
            if (chartCtx) {
                const turnoverData = @json($turnoverPercentageData ?? []);
                
                const labels = turnoverData.map(item => item.label);
                const percentages = turnoverData.map(item => item.percentage);
                
                // Create constant array for max target line (2%)
                const maxTargetData = labels.map(() => 2);
                
                new Chart(chartCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: '{{ __('messages.turnover_percentage') }}',
                                data: percentages,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                borderWidth: 2,
                                pointRadius: 5,
                                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointHoverRadius: 7,
                                pointHoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                                pointHoverBorderColor: '#fff',
                                tension: 0.1,
                                fill: false
                            },
                            {
                                label: '{{ __('messages.max_target') }}',
                                data: maxTargetData,
                                borderColor: 'rgba(220, 53, 69, 1)',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                pointHoverRadius: 0,
                                tension: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toFixed(1) + '%';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: '{{ __('messages.percentage') }}'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '{{ $isSameYear ? __('messages.month') : __('messages.year') }}'
                                }
                            }
                        }
                    }
                });
            }

            // Download graph as PDF
            const downloadBtn = document.getElementById('downloadGraphPdf');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const graphCardBody = document.getElementById('graphCardBody');
                    if (!graphCardBody) {
                        console.error('Graph card body not found');
                        return;
                    }

                    // Show loading state
                    const originalText = downloadBtn.innerHTML;
                    downloadBtn.disabled = true;
                    downloadBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>{{ __('messages.generating_pdf') }}';

                    // Get date range from filter inputs
                    const dateFrom = document.getElementById('graph_date_from')?.value || '';
                    const dateTo = document.getElementById('graph_date_to')?.value || '';
                    const dateRangeText = dateFrom && dateTo 
                        ? '{{ __('messages.from_date') }}: ' + dateFrom + ' - {{ __('messages.to_date') }}: ' + dateTo
                        : '';

                    // Use html2canvas to capture only the graph body (without header)
                    html2canvas(graphCardBody, {
                        backgroundColor: '#ffffff',
                        scale: 2,
                        logging: false,
                        useCORS: true,
                        allowTaint: true
                    }).then(function(canvas) {
                        try {
                            // Create PDF
                            const imgData = canvas.toDataURL('image/png');
                            const { jsPDF } = window.jspdf;
                            const pdf = new jsPDF({
                                orientation: 'landscape',
                                unit: 'mm',
                                format: 'a4'
                            });

                            // Calculate dimensions
                            const pdfWidth = pdf.internal.pageSize.getWidth();
                            const pdfHeight = pdf.internal.pageSize.getHeight();
                            const imgWidth = pdfWidth - 20; // 10mm margin on each side
                            const imgHeight = (canvas.height * imgWidth) / canvas.width;

                            // Add title
                            pdf.setFontSize(16);
                            pdf.text('{{ __('messages.turnover_percentage') }}', pdfWidth / 2, 15, { align: 'center' });

                            // Add date range (filter duration) if available
                            if (dateRangeText) {
                                pdf.setFontSize(10);
                                pdf.text(dateRangeText, pdfWidth / 2, 22, { align: 'center' });
                            }

                            // Add image
                            pdf.addImage(imgData, 'PNG', 10, dateRangeText ? 30 : 25, imgWidth, imgHeight);

                            // Save PDF
                            const fileName = 'turnover_percentage_' + new Date().toISOString().split('T')[0] + '.pdf';
                            pdf.save(fileName);

                            // Reset button
                            downloadBtn.disabled = false;
                            downloadBtn.innerHTML = originalText;
                        } catch (error) {
                            console.error('Error creating PDF:', error);
                            alert('{{ __('messages.pdf_generation_error') }}');
                            downloadBtn.disabled = false;
                            downloadBtn.innerHTML = originalText;
                        }
                    }).catch(function(error) {
                        console.error('Error capturing graph:', error);
                        alert('{{ __('messages.pdf_generation_error') }}');
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = originalText;
                    });
                });
            }

            // Confirm Turnover Modal
            const confirmModal = document.getElementById('confirmTurnoverModal');
            const confirmForm = document.getElementById('confirmTurnoverForm');
            const confirmTurnoverName = document.getElementById('confirmTurnoverName');
            const confirmTurnoverDate = document.getElementById('confirmTurnoverDate');
            const confirmTurnoverPosition = document.getElementById('confirmTurnoverPosition');
            const confirmDriverWarning = document.getElementById('confirmDriverWarning');

            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const turnoverId = button.getAttribute('data-turnover-id');
                    const turnoverName = button.getAttribute('data-turnover-name');
                    const turnoverDate = button.getAttribute('data-turnover-date');
                    const turnoverPosition = button.getAttribute('data-turnover-position');
                    const isDriver = button.getAttribute('data-turnover-driver') === '1';
                    
                    // Update form action
                    confirmForm.action = '{{ route("turnovers.confirm", ":id") }}'.replace(':id', turnoverId);
                    
                    // Update modal content
                    confirmTurnoverName.textContent = turnoverName;
                    confirmTurnoverDate.textContent = turnoverDate;
                    confirmTurnoverPosition.textContent = turnoverPosition;
                    
                    // Show/hide driver warning
                    if (isDriver) {
                        confirmDriverWarning.style.display = 'block';
                    } else {
                        confirmDriverWarning.style.display = 'none';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

