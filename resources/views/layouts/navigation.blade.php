<!-- Sidebar -->
<nav id="sidebar" class="bg-white text-dark d-none d-md-block" style="width: 250px; min-height: 100vh; max-height: 100vh; position: fixed; top: 0; left: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); transition: width 0.3s; overflow-y: auto; overflow-x: hidden;">
    <div class="sidebar-header p-3 d-flex align-items-center justify-content-between" style="position: sticky; top: 0; z-index: 2; background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.05);">
        <a href="{{ route('dashboard') }}" class="text-decoration-none d-flex align-items-center bg-white">
            <h1 class="offcanvas-title text-black fw-bold sidebar-logo-text" id="mobileSidebarLabel" style="font-size: 2rem;">GCV</h1>
        </a>
        <button id="sidebarToggle" type="button" class="sidebar-toggle-btn in-header">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <ul class="list-unstyled components p-3">
        <li class="mb-2">
            <a href="{{ route('dashboard') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('drivers.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.all_drivers') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-flag me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.violations_by_type') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-shield-check me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.score_points') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-clock-history me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.driving_time_reports') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-box-arrow-down me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.export_center') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-journal-check me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.action_plan') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('integrations.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                <i class="bi bi-person-check me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.driver_integrations') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('turnovers.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('turnovers.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.turnovers') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('concerns.driver-concerns.index') }}"
               class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('concerns.driver-concerns.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.concerns') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               data-bs-target="#formationsSubmenu" 
               aria-expanded="{{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'true' : 'false' }}"
               aria-controls="formationsSubmenu">
                <i class="bi bi-book me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.formations') }}</span>
                <i class="bi bi-chevron-down ms-auto sidebar-icon"></i>
            </a>
            <ul class="collapse list-unstyled ms-3 {{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'show' : '' }}" id="formationsSubmenu">
                <li class="mb-1">
                    <a href="{{ route('formation-categories.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formation-categories.*') ? 'active' : '' }}">
                        <i class="bi bi-collection me-2 text-gray-600 sidebar-icon"></i>
                        <span class="sidebar-text">{{ __('messages.formation_categories_title') }}</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('formations.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formations.*') ? 'active' : '' }}">
                        <i class="bi bi-book me-2 text-gray-600 sidebar-icon"></i>
                        <span class="sidebar-text">{{ __('messages.formations') }}</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('formation-processes.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formation-processes.*') ? 'active' : '' }}">
                        <i class="bi bi-book-half me-2 text-gray-600 sidebar-icon"></i>
                        <span class="sidebar-text">{{ __('messages.formation_processes') }}</span>
                    </a>
                </li>
            </ul>
        </li>
        {{-- organigram route --}}
        <li class="mb-2">
            <a href="{{ route('organigram.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('organigram.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.members') }}</span>
            </a>
        </li>
    </ul>

    {{-- sidebar footer setting dropdown --}}
    {{-- <div class="sidebar-footer p-3 border-top" style="position: absolute; bottom: 0; left: 0; width: 100%;">
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle w-100 text-start" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ Auth::user()->name }}</span>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="userDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person me-2 text-gray-600"></i>
                        {{ __('Profile') }}
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2 text-gray-600"></i>
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div> --}}
</nav>

<!-- Floating Toggle Button moved into header -->

<!-- Mobile Header -->
<div class="d-md-none bg-white shadow-sm position-fixed w-100" style="top: 0; left: 0; z-index: 1000;">
    <div class="d-flex justify-content-between align-items-center p-2">
        <button class="btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
            <i class="bi bi-list text-gray-600"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <div class="text-center">
                <span class="fw-bolder text-black" style="font-size: 0.9rem;">
                    @if(request()->routeIs('dashboard'))
                        {{ __('messages.dashboard') }}
                    @elseif(request()->routeIs('drivers.*'))
                        {{ __('messages.drivers') }}
                    @elseif(request()->routeIs('integrations.*'))
                        {{ __('messages.driver_integrations') }}
                    @elseif(request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*'))
                        {{ __('messages.formations') }}
                    @elseif(request()->routeIs('violations.*'))
                        {{ __('messages.violations') }}
                    @elseif(request()->routeIs('reports.*'))
                        {{ __('messages.reports') }}
                    @elseif(request()->routeIs('concerns.*') || request()->routeIs('concerns.concern-types.*') || request()->routeIs('concerns.driver-concerns.*'))
                        {{ __('messages.concerns') }}
                    @elseif(request()->routeIs('turnovers.*') || request()->routeIs('turnovers.index') || request()->routeIs('turnovers.create') || request()->routeIs('turnovers.edit') || request()->routeIs('turnovers.show'))
                        {{ __('messages.turnovers') }}
                    @else
                        GCV
                    @endif
                </span>
            </div>
        </a>
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

<!-- Mobile Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h1 class="offcanvas-title text-black fw-bold" id="mobileSidebarLabel" style="font-size: 2rem;">GCV</h1>
        <button type="button" class="btn-close" style="position: fixed; right: 20px;" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="list-unstyled components p-3">
            <li class="mb-2">
                <a href="{{ route('dashboard') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2 text-gray-600"></i>
                    {{ __('messages.dashboard') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="{{ route('drivers.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-people me-2 text-gray-600"></i>
                    {{ __('messages.all_drivers') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-flag me-2 text-gray-600"></i>
                    {{ __('messages.violations_by_type') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-shield-check me-2 text-gray-600"></i>
                    {{ __('messages.score_points') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-clock-history me-2 text-gray-600"></i>
                    {{ __('messages.driving_time_reports') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-box-arrow-down me-2 text-gray-600"></i>
                    {{ __('messages.export_center') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-journal-check me-2 text-gray-600"></i>
                    {{ __('messages.action_plan') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="{{ route('integrations.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-person-check me-2 text-gray-600"></i>
                    {{ __('messages.driver_integrations') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="{{ route('turnovers.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('turnovers.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right me-2 text-gray-600 sidebar-icon"></i>
                    <span class="sidebar-text">{{ __('messages.turnovers') }}</span>
                </a>
            </li>
            <li class="mb-2">
                <a href="{{ route('concerns.driver-concerns.index') }}"
                   class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('concerns.driver-concerns.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle me-2 text-gray-600"></i>
                    {{ __('messages.concerns') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="#" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'active' : '' }}" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#formationsSubmenuMobile" 
                    aria-expanded="{{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'true' : 'false' }}"
                    aria-controls="formationsSubmenuMobile">
                        <i class="bi bi-book me-2 text-gray-600"></i>
                        {{ __('messages.formations') }}
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ms-3 {{ (request()->routeIs('formations.*') || request()->routeIs('formation-processes.*') || request()->routeIs('formation-categories.*')) ? 'show' : '' }}" id="formationsSubmenuMobile">
                        <li class="mb-1">
                            <a href="{{ route('formation-categories.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formation-categories.*') ? 'active' : '' }}">
                                <i class="bi bi-collection me-2 text-gray-600"></i>
                                {{ __('messages.formation_categories_title') }}
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="{{ route('formations.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formations.*') ? 'active' : '' }}">
                                <i class="bi bi-book me-2 text-gray-600"></i>
                                {{ __('messages.formations') }}
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="{{ route('formation-processes.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('formation-processes.*') ? 'active' : '' }}">
                                <i class="bi bi-book-half me-2 text-gray-600"></i>
                                {{ __('messages.formation_processes') }}
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- organigram route --}}
                <li class="mb-2">
                    <a href="{{ route('organigram.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('organigram.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2 text-gray-600 sidebar-icon"></i>
                        <span class="sidebar-text">{{ __('messages.members') }}</span>
                    </a>
                </li>
            </ul>

        {{-- mobile sidebar footer --}}
        <div class="border-top mt-auto" style="position: sticky; bottom: 0; z-index: 2; background-color: #fff; border-top: 1px solid rgba(0,0,0,0.05);">
            <div class="p-3">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle w-100 text-start" type="button" id="mobileUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2 text-gray-600"></i>
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="mobileUserDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2 text-gray-600"></i>
                                {{ __('Profile') }}
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2 text-gray-600"></i>
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Add these styles to your CSS */
    .wrapper {
        display: flex;
        width: 100%;
    }

    .content-wrapper {
        width: calc(100% - 250px);
        margin-left: 250px;
        min-height: 100vh;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }

    #sidebar {
        z-index: 999;
        transition: all 0.3s;
    }

    #sidebar .active {
        background-color: #f3f4f6;
        border-radius: 5px;
        color: #374151 !important;
    }

    #sidebar a:hover {
        background-color: #f3f4f6;
        border-radius: 5px;
        color: #gray !important;
    }

    /* Formations dropdown styles */
    #formationsSubmenu,
    #formationsSubmenuMobile {
        transition: all 0.3s ease;
    }

    #formationsSubmenu a,
    #formationsSubmenuMobile a {
        font-size: 0.9rem;
    }

    #formationsSubmenu a.active,
    #formationsSubmenuMobile a.active {
        background-color: #e9ecef;
        border-radius: 5px;
    }

    /* Chevron rotation for dropdown */
    a[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }

    /* Collapsed sidebar styles */
    #sidebar.collapsed {
        width: 70px !important;
    }

    #sidebar.collapsed .sidebar-text {
        display: none;
    }

    /* Hide the sidebar title/logo when collapsed */
    #sidebar.collapsed .sidebar-logo-text {
        display: none;
    }

    /* Hide chevron icon when sidebar is collapsed */
    #sidebar.collapsed .bi-chevron-down {
        display: none !important;
    }

    #sidebar.collapsed .sidebar-logo {
        width: 100%;
    }

    #sidebar.collapsed .dropdown-toggle::after {
        display: none;
    }

    #sidebar.collapsed .collapse {
        display: none !important;
    }

    #sidebar.collapsed .dropdown-menu {
        position: fixed !important;
        left: 70px !important;
        top: auto !important;
        transform: none !important;
        margin-top: 0 !important;
        min-width: 200px;
    }

    #sidebar.collapsed .sidebar-footer .dropdown-menu {
        position: fixed !important;
        left: 70px !important;
        bottom: 0 !important;
        top: auto !important;
        transform: none !important;
        margin-top: 0 !important;
        min-width: 200px;
    }

    #sidebar.collapsed #sidebarToggle i {
        transform: rotate(180deg);
    }

    #sidebar.collapsed .sidebar-header {
        padding: 1rem 0.5rem !important;
        text-align: center;
        justify-content: center !important;
        align-items: center !important;
        flex-direction: column;
    }

    #sidebar.collapsed .sidebar-header a {
        display: none !important;
    }

    #sidebar.collapsed .sidebar-header .sidebar-toggle-btn {
        margin: 0 auto !important;
    }

    /* Tooltip for collapsed sidebar */
    #sidebar.collapsed a[title] {
        position: relative;
    }

    #sidebar.collapsed a[title]:hover::after {
        content: attr(title);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        margin-left: 10px;
    }

    /* Adjust content when sidebar is collapsed */
    .content-wrapper.expanded {
        width: calc(100% - 70px);
        margin-left: 70px;
    }

    /* Mobile Styles */
    @media (max-width: 767.98px) {
        .content-wrapper {
            width: 100%;
            margin-left: 0;
            padding-top: 60px; /* Height of mobile header */
        }
        
        .offcanvas {
            width: 280px;
        }

        .offcanvas-body {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
        }

        .offcanvas-body .components {
            flex: 1;
        }

        /* Hide toggle button on mobile */
        #sidebarToggle {
            display: none;
        }
    }

    #sidebar {
        width: 250px;
        min-height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        z-index: 999;
        background: #fff;
        transition: width 0.3s;
    }
    #sidebar.collapsed {
        width: 70px !important;
    }
    .sidebar-toggle-btn {
        width: 36px;
        height: 36px;
        border-radius: 10%;
        background: #374151;
        color: #fff;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    /* When button is inside sidebar header */
    #sidebar .sidebar-header .sidebar-toggle-btn.in-header {
        position: static;
    }
    .sidebar-toggle-btn:focus {
        outline: none;
    }
    .sidebar-toggle-btn i {
        font-size: 1.2rem;
        transition: transform 0.3s;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        // Check if sidebar state is stored in localStorage
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (contentWrapper) {
                contentWrapper.classList.add('expanded');
            }
            sidebarToggle.querySelector('i').classList.remove('bi-chevron-left');
            sidebarToggle.querySelector('i').classList.add('bi-chevron-right');
        }
        
        // Add titles for tooltips when collapsed
        const sidebarLinks = sidebar.querySelectorAll('a[href]');
        sidebarLinks.forEach(link => {
            const text = link.querySelector('.sidebar-text');
            if (text) {
                link.setAttribute('title', text.textContent.trim());
            }
        });
        
        // Move toggle button when sidebar is collapsed
        // Button is now inside header; no left/right positioning needed
        // update button icon
        function updatebuttonicon() {
            if (sidebar.classList.contains('collapsed')){
                sidebarToggle.querySelector('i').classList.remove('bi-chevron-left');
                sidebarToggle.querySelector('i').classList.add('bi-chevron-right');
            }
            sidebarToggle.querySelector('i').classList.remove('bi-chevron-right');
            sidebarToggle.querySelector('i').classList.add('bi-chevron-left');
        }
        updatebuttonicon();
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
            if (contentWrapper) {
                contentWrapper.classList.toggle('expanded');
            }
            updatebuttonicon();
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');
        });
        
        // Dropdown open on icon click when sidebar is collapsed
        const dropdownToggles = sidebar.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                if (sidebar.classList.contains('collapsed')) {
                    e.preventDefault();
                    // Expand sidebar
                    sidebar.classList.remove('collapsed');
                    if (contentWrapper) contentWrapper.classList.remove('expanded');
                    localStorage.setItem('sidebarCollapsed', 'false');
                    // Open the corresponding dropdown after sidebar expands
                    const dropdownMenu = this.nextElementSibling;
                    setTimeout(() => {
                        if (dropdownMenu && dropdownMenu.classList.contains('collapse')) {
                            dropdownMenu.classList.add('show');
                        }
                    }, 300); // Wait for sidebar to expand
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target)) {
                sidebar.querySelectorAll('.collapse.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // Handle formations dropdown chevron rotation
        const formationsDropdowns = document.querySelectorAll('[data-bs-target="#formationsSubmenu"], [data-bs-target="#formationsSubmenuMobile"]');
        formationsDropdowns.forEach(dropdown => {
            const targetId = dropdown.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);
            
            if (target) {
                // Bootstrap collapse event listeners
                target.addEventListener('show.bs.collapse', function() {
                    const chevron = dropdown.querySelector('.bi-chevron-down');
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                });
                
                target.addEventListener('hide.bs.collapse', function() {
                    const chevron = dropdown.querySelector('.bi-chevron-down');
                    if (chevron) {
                        chevron.style.transform = 'rotate(0deg)';
                    }
                });
            }
        });
    });
</script>
