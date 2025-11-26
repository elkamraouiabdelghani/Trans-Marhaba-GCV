<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            {{-- Year Selector --}}
            <div class="card border-0 shadow-sm my-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                {{ __('messages.formation_planning_title') }}
                            </h2>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="GET" action="{{ route('formations.planning') }}" class="d-flex gap-2">
                                <select name="year" class="form-select" onchange="this.form.submit()">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year }}" {{ (int)$year === (int)$selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <noscript>
                                    <button class="btn btn-primary" type="submit">{{ __('messages.apply') }}</button>
                                </noscript>
                            </form>
                            <a href="{{ route('formations.planning.pdf', ['year' => $selectedYear]) }}"
                               class="btn btn-sm btn-danger d-flex align-items-center gap-2"
                               target="_blank" rel="noopener">
                                <i class="bi bi-file-earmark-pdf"></i>
                                {{ __('messages.download_planning_pdf') }}
                            </a>
                            <a href="{{ route('formations.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-list me-1"></i>
                                {{ __('messages.formations_list') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                    @foreach(range(1,12) as $month)
                        @php
                            $monthFormations = $formationsByMonth->get((string)$month) ?? collect();
                            $monthName = \Carbon\Carbon::create()->month($month)->translatedFormat('F');
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold">{{ ucfirst($monthName) }}</div>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                        {{ $monthFormations->count() }}
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    @forelse($monthFormations as $formation)
                                        @php
                                            $status = $formation->status === 'realized' ? 'success' : 'warning';
                                            $statusLabel = $formation->status === 'realized'
                                                ? __('messages.realized')
                                                : __('messages.planned');
                                            $realizingDate = $formation->realizing_date
                                                ? \Carbon\Carbon::parse($formation->realizing_date)->translatedFormat('d F')
                                                : __('messages.date_not_defined');
                                        @endphp
                                        <a href="{{ route('formations.edit', $formation) }}"
                                           class="text-decoration-none text-reset d-block">
                                            <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 text-primary fw-semibold">{{ $formation->theme }}</h6>
                                                        @if($formation->theme)
                                                            <div class="text-muted small">{{ $formation->theme }}</div>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-{{ $status }} bg-opacity-25 text-{{ $status }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 small text-muted d-flex align-items-center gap-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-calendar-event"></i>
                                                        <span>{{ $realizingDate }}</span>
                                                    </div>
                                                    @if($formation->duree)
                                                        <span class="text-muted">|</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-clock-history"></i>
                                                            <span>{{ $formation->duree }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                            {{ __('messages.no_formations_month') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

