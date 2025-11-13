<nav class="d-none d-md-flex align-items-center bg-white w-100" style="min-height: 50px;">
    <div class="container-fluid d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center">
            <h1 class="fw-bold text-dark">
                @if(request()->routeIs('dashboard'))
                    {{ __('messages.dashboard') }}
                @elseif(request()->routeIs('drivers.*'))
                    {{ __('messages.drivers') }}
                @elseif(request()->routeIs('violations.*'))
                    {{ __('messages.violations') }}
                @elseif(request()->routeIs('reports.*'))
                    {{ __('messages.reports') }}
                @elseif(request()->routeIs('integrations.*'))
                    {{ __('messages.driver_integrations') }}
                @elseif(request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*'))
                    {{ __('messages.formations') }}
                @elseif(request()->routeIs('turnovers.*') || request()->routeIs('turnovers.index') || request()->routeIs('turnovers.create') || request()->routeIs('turnovers.edit') || request()->routeIs('turnovers.show'))
                    {{ __('messages.turnovers') }}
                @elseif(request()->routeIs('concern-types.*') || request()->routeIs('driver-concerns.*') || request()->routeIs('driver-concerns.index') || request()->routeIs('driver-concerns.create') || request()->routeIs('driver-concerns.edit') || request()->routeIs('driver-concerns.show'))
                    {{ __('messages.concerns') }}
                @else
                    {{ __('messages.brand') }}
                @endif
            </h1>
        </div>
        <ul class="nav">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link text-dark">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('drivers.index') }}" class="nav-link text-dark">{{ __('messages.drivers') }}</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-dark">{{ __('messages.violations') }}</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-dark">{{ __('messages.reports') }}</a>
            </li>
        </ul>
        {{-- language switcher --}}
        <div class="d-flex align-items-center">
            <button class="btn btn-light me-2" type="button" title="{{ __('messages.search') }}" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="bi bi-search"></i></button>
            <div class="dropdown me-2">
                <button class="btn btn-light dropdown-toggle" type="button" id="topnavUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('messages.profile') }}">
                    <i class="bi bi-person"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="topnavUserDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i>
                            {{ __('messages.profile') }}
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                {{ __('messages.log_out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ strtoupper(app()->getLocale()) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'en']) }}">English</a></li>
                    <li><a class="dropdown-item" href="{{ route('locale.switch', ['locale' => 'fr']) }}">Français</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 75vw; width: 75vw;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="searchModalLabel">{{ __('messages.search_by_date_range') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="searchForm" method="GET" action="#">
                    <div class="container-fluid">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label for="dateFrom" class="form-label">{{ __('messages.from_date') }}</label>
                                <input type="date" class="form-control" id="dateFrom" name="from" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="dateTo" class="form-label">{{ __('messages.to_date') }}</label>
                                <input type="date" class="form-control" id="dateTo" name="to" required>
                            </div>
                            <div class="col-12 col-md-4 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                <button type="button" class="btn btn-primary" onclick="performSearch()">{{ __('messages.search') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function performSearch() {
    const form = document.getElementById('searchForm');
    const fromDate = document.getElementById('dateFrom').value;
    const toDate = document.getElementById('dateTo').value;
    
    if (!fromDate || !toDate) {
        alert('Please select both from and to dates');
        return;
    }
    
    if (new Date(fromDate) > new Date(toDate)) {
        alert('From date cannot be greater than To date');
        return;
    }
    
    // Get current URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('from', fromDate);
    urlParams.set('to', toDate);
    
    // Redirect to current page with date parameters
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}
</script>
