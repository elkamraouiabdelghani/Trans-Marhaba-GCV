<!-- Sidebar -->
<nav id="sidebar" class="bg-white text-dark d-none d-md-block" style="width: 250px; min-height: 100vh; position: fixed; top: 0; left: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); transition: width 0.3s;">
    <div class="sidebar-header p-3 d-flex align-items-center justify-content-between">
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
            <a href="{{ route('drivers.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2">
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
            <a href="{{ route('formation-types.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2">
                <i class="bi bi-book me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.formation_types') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{ route('drivers.integrations') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('drivers.integrations.*') ? 'active' : '' }}">
                <i class="bi bi-person-plus me-2 text-gray-600 sidebar-icon"></i>
                <span class="sidebar-text">{{ __('messages.start_integration') }}</span>
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
                    @elseif(request()->routeIs('violations.*'))
                        {{ __('messages.violations') }}
                    @elseif(request()->routeIs('reports.*'))
                        {{ __('messages.reports') }}
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
                <a href="{{ route('formation-types.index') }}" class="text-dark text-decoration-none d-flex align-items-center p-2">
                    <i class="bi bi-book me-2 text-gray-600"></i>
                    {{ __('messages.formation_types') }}
                </a>
            </li>
            <li class="mb-2">
                <a href="{{ route('drivers.integrations') }}" class="text-dark text-decoration-none d-flex align-items-center p-2 {{ request()->routeIs('drivers.integrations.*') ? 'active' : '' }}">
                    <i class="bi bi-person-plus me-2 text-gray-600"></i>
                    {{ __('messages.start_integration') }}
                </a>
            </li>
        </ul>

        {{-- mobile sidebar footer --}}
        <div class="border-top mt-auto">
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
    }

    #sidebar.collapsed .sidebar-header a {
        justify-content: center !important;
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
});
</script>
