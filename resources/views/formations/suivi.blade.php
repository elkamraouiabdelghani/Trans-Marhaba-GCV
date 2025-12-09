<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.formations_suivi_title') ?? 'Formations Suivi' }}</h1>
                <p class="text-muted mb-0">{{ __('messages.formations_suivi_subtitle') ?? 'Follow-up of formations and TBT formations per driver' }}</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="export-pdf-btn" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') ?? 'Export PDF' }}
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">{{ __('messages.filters') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('formations.suivi') }}" class="row g-3 align-items-end" id="suivi-filter-form">
                    <div class="col-md-2">
                        <label class="form-label small">{{ __('messages.formations_suivi_type') ?? 'Type' }}</label>
                        <select name="type" id="type-select" class="form-select">
                            <option value="formation" {{ $type === 'formation' ? 'selected' : '' }}>{{ __('messages.formations_suivi_type_formation') ?? 'Formation' }}</option>
                            <option value="tbt" {{ $type === 'tbt' ? 'selected' : '' }}>{{ __('messages.formations_suivi_type_tbt') ?? 'TBT Formation' }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">{{ __('messages.year') ?? 'Year' }}</label>
                        <select name="year" class="form-select">
                            @php
                                $yearsList = $type === 'tbt' ? ($tbtYears ?? []) : ($formationYears ?? []);
                            @endphp
                            @foreach($yearsList as $y)
                                <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">{{ __('messages.driver') }}</label>
                        <select name="driver_id" class="form-select">
                            <option value="">{{ __('messages.all_drivers') }}</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}" {{ (string)$driverId === (string)$d->id ? 'selected' : '' }}>{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">{{ __('messages.flotte') }}</label>
                        <select name="flotte_id" class="form-select">
                            <option value="">{{ __('messages.all_flottes') }}</option>
                            @foreach($flottes as $f)
                                <option value="{{ $f->id }}" {{ (string)$flotteId === (string)$f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small" id="theme-label">{{ $type === 'tbt' ? __('messages.tbt_formation_title') ?? 'Title' : __('messages.theme') ?? 'Theme' }}</label>
                        <select name="theme" id="theme-select" class="form-select">
                            <option value="">{{ __('messages.all_formations') }}</option>
                            @foreach($themes as $t)
                                <option value="{{ $t }}" {{ $theme === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">{{ __('messages.formation_type_label') }}</label>
                        <select name="formation_type" id="formation-type-select" class="form-select" {{ $type === 'tbt' ? 'disabled' : '' }}>
                            <option value="">{{ __('messages.all_formations') }}</option>
                            @foreach($formationTypes as $key => $label)
                                <option value="{{ $key }}" {{ $formationType === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> {{ __('messages.search') }}
                        </button>
                        <a href="{{ route('formations.suivi') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">{{ __('messages.formations_suivi_chart_title') ?? 'Planned vs Realized per Driver' }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="suiviChart" height="140"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-muted small mb-2">{{ __('messages.summary') ?? 'Summary' }}</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ __('messages.planned') }}</span>
                            <span class="fw-bold text-primary">{{ $chartData['totals']['planned'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ __('messages.realized') }}</span>
                            <span class="fw-bold text-success">{{ $chartData['totals']['realized'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">{{ __('messages.formations_suivi_table_title') ?? 'Details' }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.driver') }}</th>
                                        <th class="text-end">{{ __('messages.planned') }}</th>
                                        <th class="text-end">{{ __('messages.realized') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($chartData['labels'] as $idx => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="text-end">{{ $chartData['planned'][$idx] ?? 0 }}</td>
                                            <td class="text-end">{{ $chartData['realized'][$idx] ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_data') ?? 'No data' }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formationThemes = @json($formationThemesAll ?? []);
                const tbtThemes = @json($tbtThemesAll ?? []);
                const typeSelect = document.getElementById('type-select');
                const themeSelect = document.getElementById('theme-select');
                const themeLabel = document.getElementById('theme-label');
                const formationTypeSelect = document.getElementById('formation-type-select');

                function rebuildThemes(list) {
                    if (!themeSelect) return;
                    const current = themeSelect.value;
                    themeSelect.innerHTML = '';
                    const optAll = document.createElement('option');
                    optAll.value = '';
                    optAll.textContent = "{{ __('messages.all_formations') }}";
                    themeSelect.appendChild(optAll);
                    list.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t;
                        opt.textContent = t;
                        themeSelect.appendChild(opt);
                    });
                    if (list.includes(current)) {
                        themeSelect.value = current;
                    }
                }

                function handleTypeChange() {
                    const val = typeSelect.value;
                    if (val === 'tbt') {
                        themeLabel.textContent = "{{ __('messages.tbt_formation_title') ?? 'Title' }}";
                        rebuildThemes(tbtThemes);
                        if (formationTypeSelect) {
                            formationTypeSelect.value = '';
                            formationTypeSelect.disabled = true;
                        }
                    } else {
                        themeLabel.textContent = "{{ __('messages.theme') ?? 'Theme' }}";
                        rebuildThemes(formationThemes);
                        if (formationTypeSelect) {
                            formationTypeSelect.disabled = false;
                        }
                    }
                }

                if (typeSelect) {
                    typeSelect.addEventListener('change', handleTypeChange);
                    handleTypeChange();
                }

                const labels = @json($chartData['labels']);
                const planned = @json($chartData['planned']);
                const realized = @json($chartData['realized']);

                const ctx = document.getElementById('suiviChart');
                if (!ctx) return;

                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: "{{ __('messages.planned') }}",
                                data: planned,
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            },
                            {
                                label: "{{ __('messages.realized') }}",
                                data: realized,
                                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                        },
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });

                // PDF Export with chart screenshot
                const exportPdfBtn = document.getElementById('export-pdf-btn');
                if (exportPdfBtn) {
                    exportPdfBtn.addEventListener('click', function() {
                        const btn = this;
                        const originalHtml = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> {{ __('messages.generating_pdf') ?? "Generating PDF..." }}';

                        // Get the chart card (includes header with title and body with chart)
                        const chartCard = document.querySelector('.col-lg-8 .card');
                        const chartContainer = document.getElementById('suiviChart');
                        
                        if (!chartContainer || !window.html2canvas) {
                            // Fallback to simple GET request
                            window.location.href = '{{ route("formations.suivi.pdf", request()->all()) }}';
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            return;
                        }

                        // Wait a bit for chart to fully render
                        setTimeout(function() {
                            // Capture the entire chart card (includes title, chart and legend)
                            html2canvas(chartCard, {
                                backgroundColor: '#ffffff',
                                scale: 2,
                                logging: false,
                                useCORS: true,
                                allowTaint: false
                            }).then(function(canvas) {
                                // Convert to base64
                                const chartImage = canvas.toDataURL('image/png');

                                // Create form to submit
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '{{ route("formations.suivi.pdf") }}';
                                
                                // Add CSRF token
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = '{{ csrf_token() }}';
                                form.appendChild(csrfInput);

                                // Add chart image
                                const imageInput = document.createElement('input');
                                imageInput.type = 'hidden';
                                imageInput.name = 'chart_image';
                                imageInput.value = chartImage;
                                form.appendChild(imageInput);

                                // Add all filter parameters
                                const params = new URLSearchParams(window.location.search);
                                params.forEach(function(value, key) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = key;
                                    input.value = value;
                                    form.appendChild(input);
                                });

                                document.body.appendChild(form);
                                form.submit();
                            }).catch(function(error) {
                                console.error('Error capturing chart:', error);
                                // Fallback to simple GET request
                                window.location.href = '{{ route("formations.suivi.pdf", request()->all()) }}';
                                btn.disabled = false;
                                btn.innerHTML = originalHtml;
                            });
                        }, 500);
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

