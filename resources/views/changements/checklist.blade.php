<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        @if(session('success'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
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
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
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

    <div class="container-fluid py-4 mt-4">
        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-list-check me-2 text-primary"></i>
                            {{ __('messages.step_6_checklist') }}
                        </h5>
                        <small class="text-muted">
                            {{ $changement->changementType->name ?? __('messages.not_available') }} - 
                            {{ $changement->date_changement->format('d/m/Y') }}
                        </small>
                    </div>
                    <a href="{{ route('changements.show', $changement) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Checklist Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">{{ __('messages.checklist_title') }}</h6>
                <small class="text-muted">{{ __('messages.checklist_instructions') }}</small>
            </div>
            <div class="card-body">
                @if($principaleCretaires->isEmpty())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('messages.no_principale_cretaires') }}
                    </div>
                @else
                    <form action="{{ route('changements.save-checklist', $changement) }}" method="POST">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;" class="text-center">{{ __('messages.sous_cretaires') }}</th>
                                        @foreach($principaleCretaires as $principale)
                                            <th class="text-center" style="min-width: 150px;">
                                                <div class="fw-bold">{{ $principale->name }}</div>
                                                @if($principale->code)
                                                    <small class="text-muted">({{ $principale->code }})</small>
                                                @endif
                                            </th>
                                        @endforeach
                                        <th style="width: 25%;" class="text-center">{{ __('messages.observation') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Collect all sous-cretaires with their principale
                                        $allSousCretaires = collect();
                                        foreach ($principaleCretaires as $principale) {
                                            foreach ($principale->sousCretaires as $sous) {
                                                $allSousCretaires->push([
                                                    'sous' => $sous,
                                                    'principale' => $principale,
                                                ]);
                                            }
                                        }
                                        $allSousCretaires = $allSousCretaires->sortBy([
                                            ['principale.name', 'asc'],
                                            ['sous.name', 'asc'],
                                        ]);
                                    @endphp

                                    @foreach($allSousCretaires as $item)
                                        @php
                                            $sous = $item['sous'];
                                            $principale = $item['principale'];
                                            $result = $checklistResults->get($sous->id);
                                            $currentStatus = old("checklist.{$sous->id}.status", $result ? $result->status : 'N/A');
                                            $currentObservation = old("checklist.{$sous->id}.observation", $result ? $result->observation : '');
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">
                                                <div>{{ $sous->name }}</div>
                                                @if($sous->description)
                                                    <small class="text-muted">{{ $sous->description }}</small>
                                                @endif
                                            </td>
                                            @foreach($principaleCretaires as $pc)
                                                <td class="text-center">
                                                    @if($pc->id === $principale->id)
                                                        <div class="d-flex justify-content-center gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="radio" 
                                                                       name="checklist[{{ $sous->id }}][status]" 
                                                                       id="status_ok_{{ $sous->id }}" 
                                                                       value="OK" 
                                                                       {{ $currentStatus === 'OK' ? 'checked' : '' }}
                                                                       required>
                                                                <label class="form-check-label text-success fw-bold" for="status_ok_{{ $sous->id }}">
                                                                    OK
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="radio" 
                                                                       name="checklist[{{ $sous->id }}][status]" 
                                                                       id="status_ko_{{ $sous->id }}" 
                                                                       value="KO" 
                                                                       {{ $currentStatus === 'KO' ? 'checked' : '' }}>
                                                                <label class="form-check-label text-danger fw-bold" for="status_ko_{{ $sous->id }}">
                                                                    KO
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="radio" 
                                                                       name="checklist[{{ $sous->id }}][status]" 
                                                                       id="status_na_{{ $sous->id }}" 
                                                                       value="N/A" 
                                                                       {{ $currentStatus === 'N/A' ? 'checked' : '' }}>
                                                                <label class="form-check-label text-muted fw-bold" for="status_na_{{ $sous->id }}">
                                                                    N/A
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td>
                                                <textarea class="form-control form-control-sm" 
                                                          name="checklist[{{ $sous->id }}][observation]" 
                                                          rows="2" 
                                                          placeholder="{{ __('messages.observation_placeholder') }}">{{ $currentObservation }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if($allSousCretaires->isEmpty())
                                        <tr>
                                            <td colspan="{{ $principaleCretaires->count() + 2 }}" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-6 mb-3"></i>
                                                    <p class="mb-0">{{ __('messages.no_sous_cretaires') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('changements.show', $changement) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>
                                    {{ __('messages.save_checklist') }}
                                </button>
                                @if($changement->check_list_path)
                                    <a href="{{ route('changements.checklist.download', $changement) }}" 
                                       class="btn btn-outline-success">
                                        <i class="bi bi-file-pdf me-1"></i>
                                        {{ __('messages.download_checklist') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Initialize and show toasts on page load
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(function(toastEl) {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });
        });
    </script>

    <style>
        .table th {
            vertical-align: middle;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .form-check-label {
            cursor: pointer;
        }
    </style>
</x-app-layout>

