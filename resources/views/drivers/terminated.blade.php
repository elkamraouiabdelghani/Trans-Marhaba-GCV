<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <div>
                <h3 class="mb-1 text-dark fw-bold">
                    <i class="bi bi-slash-circle text-danger me-2"></i>
                    {{ __('messages.terminated_drivers') }}
                </h3>
                <p class="text-muted mb-0">
                    {{ trans_choice('messages.terminated_drivers_count', $drivers->count(), ['count' => $drivers->count()]) }}
                </p>
            </div>
            <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back_to_list') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">{{ __('messages.name') }}</th>
                                <th class="py-3 px-4">{{ __('messages.phone_number') }}</th>
                                <th class="py-3 px-4">{{ __('messages.date_integration') }}</th>
                                <th class="py-3 px-4">{{ __('messages.terminated_date') }}</th>
                                <th class="py-3 px-4">{{ __('messages.terminated_cause') }}</th>
                                <th class="py-3 px-4">{{ __('messages.work_duration') }}</th>
                                <th class="py-3 px-4">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $driver)
                                <tr class="terminated-driver-row" data-driver-url="{{ route('drivers.show', $driver) }}" style="cursor: pointer;">
                                    <td class="py-3 px-4">
                                        {{ $driver->full_name ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $driver->phone ?? $driver->phone_number ?? $driver->phone_numbre ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ optional($driver->date_integration)->format('d/m/Y') ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ optional($driver->terminated_date)->format('d/m/Y') ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($driver->terminated_cause)
                                            <span title="{{ $driver->terminated_cause }}">
                                                {{ \Illuminate\Support\Str::limit($driver->terminated_cause, 90) }}
                                            </span>
                                        @else
                                            {{ __('messages.not_available') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($driver->date_integration && $driver->terminated_date)
                                            @php
                                                $diff = $driver->date_integration->diff($driver->terminated_date);
                                                $years = $diff->y;
                                                $months = $diff->m;
                                                $days = $driver->date_integration->diffInDays($driver->terminated_date);
                                            @endphp
                                            {{ $years > 0 ? $years . ' ' . __('messages.years') : '' }}
                                            {{ $months > 0 ? $months . ' ' . __('messages.months') : '' }}
                                            @if(!$years && !$months)
                                                {{ $days }} {{ __('messages.days') }}
                                            @endif
                                        @else
                                            {{ __('messages.not_available') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-danger bg-opacity-10 text-danger">{{ __('messages.terminated') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-emoji-smile-upside-down display-4 mb-2"></i>
                                            <p class="mb-0">{{ __('messages.no_drivers_found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.terminated-driver-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    const url = this.dataset.driverUrl;
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
</x-app-layout>

