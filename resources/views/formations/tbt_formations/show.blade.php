<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ $tbtFormation->title }}</h1>
                <p class="text-muted mb-0">
                    {{ __('messages.tbt_formation_year') }} {{ $tbtFormation->year }} ·
                    {{ __('messages.tbt_formation_month') }} {{ \Carbon\Carbon::create($tbtFormation->year, $tbtFormation->month, 1)->locale('fr')->monthName }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tbt-formations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.back_to_list') ?? 'Back to list' }}
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.information') ?? 'Information' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.tbt_formation_year') }}</small>
                                <div class="fw-semibold">{{ $tbtFormation->year }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.tbt_formation_month') }}</small>
                                <div class="fw-semibold">{{ \Carbon\Carbon::create($tbtFormation->year, $tbtFormation->month, 1)->locale('fr')->monthName }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.tbt_formation_week') }}</small>
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($tbtFormation->week_start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($tbtFormation->week_end_date)->format('d/m') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.tbt_formation_status') }}</small>
                                @php
                                    $statusClass = $tbtFormation->status === 'realized' ? 'success' : 'warning';
                                    $statusLabel = $tbtFormation->status === 'realized'
                                        ? __('messages.tbt_formation_status_realized')
                                        : __('messages.tbt_formation_status_planned');
                                @endphp
                                <div>
                                    <span class="badge bg-{{ $statusClass }} bg-opacity-25 text-{{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.tbt_formation_active') }}</small>
                                <div>
                                    @if($tbtFormation->is_active)
                                        <span class="badge bg-success">{{ __('messages.tbt_formation_active_status') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.tbt_formation_inactive_status') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($tbtFormation->participant)
                            <div class="col-md-6">
                                <small class="text-muted">{{ __('messages.formation_participant') }}</small>
                                <div class="fw-semibold">{{ $tbtFormation->participant }}</div>
                            </div>
                            @endif
                            @if($tbtFormation->description)
                            <div class="col-12">
                                <small class="text-muted">{{ __('messages.description') }}</small>
                                <div class="fw-semibold">{{ $tbtFormation->description }}</div>
                            </div>
                            @endif
                            @if($tbtFormation->notes)
                            <div class="col-12">
                                <small class="text-muted">{{ __('messages.notes') ?? 'Notes' }}</small>
                                <div class="fw-semibold">{{ $tbtFormation->notes }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($tbtFormation->documents && is_array($tbtFormation->documents) && count($tbtFormation->documents) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.documents') ?? 'Documents' }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($tbtFormation->documents as $doc)
                                <li class="mb-2">
                                    <a href="{{ asset('storage/'.$doc['path']) }}" target="_blank">
                                        <i class="bi bi-file-earmark-text me-1"></i> {{ $doc['name'] ?? basename($doc['path']) }}
                                    </a>
                                    @if(isset($doc['uploaded_at']))
                                        <small class="text-muted">({{ \Carbon\Carbon::parse($doc['uploaded_at'])->format('d/m/Y') }})</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('tbt-formations.edit', $tbtFormation) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                        </a>
                        <a href="{{ route('tbt-formations.presence-pdf', $tbtFormation) }}" class="btn btn-sm btn-danger" target="_blank" rel="noopener">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('messages.generate_presence_list') ?? 'Presence PDF' }}
                        </a>
                        @if($tbtFormation->status !== 'realized')
                            <form action="{{ route('tbt-formations.mark-realized', $tbtFormation) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('messages.mark_as_realized') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2 text-primary"></i>
                    {{ __('messages.drivers') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.done_at') ?? 'Done at' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tbtFormation->driverTbtFormations as $df)
                                <tr>
                                    <td class="ps-3">{{ $df->driver->full_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ __('messages.completed') ?? 'Completed' }}</span>
                                    </td>
                                    <td>{{ $df->done_at ? $df->done_at->format('d/m/Y') : '-' }}</td>
                                    <td class="text-end pe-3">
                                        @if($df->driver)
                                            <a href="{{ route('tbt-formations.certificate-pdf', [$tbtFormation, $df]) }}" class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener" title="{{ __('messages.generate_certificate') ?? 'Certificate PDF' }}">
                                                <i class="bi bi-filetype-pdf"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        {{ __('messages.no_drivers_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

