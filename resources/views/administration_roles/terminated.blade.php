<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <div class="mb-3 mb-lg-0">
                <h3 class="mb-1 text-dark fw-bold">
                    <i class="bi bi-person-x text-danger me-2"></i>
                    {{ __('messages.terminated_admins_title') }}
                </h3>
                <p class="text-muted mb-0">
                    {{ trans_choice('messages.terminated_admins_count', $users->count(), ['count' => $users->count()]) }}
                </p>
            </div>
            <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
                <a href="{{ route('administration-roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">{{ __('messages.name') }}</th>
                                <th class="py-3 px-4">{{ __('messages.email') }}</th>
                                <th class="py-3 px-4">{{ __('messages.phone_number') }}</th>
                                <th class="py-3 px-4">{{ __('messages.department') }}</th>
                                <th class="py-3 px-4">{{ __('messages.date_integration') }}</th>
                                <th class="py-3 px-4">{{ __('messages.terminated_date') }}</th>
                                <th class="py-3 px-4">{{ __('messages.terminated_cause') }}</th>
                                <th class="py-3 px-4">{{ __('messages.work_duration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="terminated-admin-row" data-admin-url="{{ route('administration-roles.show', $user) }}" style="cursor: pointer;">
                                    <td class="py-3 px-4">
                                        {{ $user->name ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $user->email ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $user->phone ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary text-uppercase">
                                            {{ $user->department ?? __('messages.not_available') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ optional($user->date_integration)->format('d/m/Y') ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ optional($user->terminated_date)->format('d/m/Y') ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($user->terminated_cause)
                                            <span title="{{ $user->terminated_cause }}">
                                                {{ \Illuminate\Support\Str::limit($user->terminated_cause, 90) }}
                                            </span>
                                        @else
                                            {{ __('messages.not_available') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($user->date_integration && $user->terminated_date)
                                            @php
                                                $diff = $user->date_integration->diff($user->terminated_date);
                                                $years = $diff->y;
                                                $months = $diff->m;
                                                $days = $user->date_integration->diffInDays($user->terminated_date);
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-emoji-smile-upside-down display-4 mb-2"></i>
                                            <p class="mb-0">{{ __('messages.no_admins_found') }}</p>
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
            document.querySelectorAll('.terminated-admin-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    const url = this.dataset.adminUrl;
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
</x-app-layout>


