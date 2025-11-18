<link rel="stylesheet" href="path/to/adminlte.min.css">
<style>
    .nav-link.active {
        background-color: #000000 !important;
        color: white !important;
    }

    /* .nav-link:hover {
            background-color: #4b545c !important;
            color: white !important;
        } */
</style>


<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a class="brand-link">
        @if ($companyname && $companyname->image)
            <img src="{{ asset('storage/' . $companyname->image) }}" alt="{{ $companyname->name_company }}"
                class="brand-image img-circle elevation-3" style="opacity: 0.8" />
        @else
            <img src="../../../dist/img/AdminLTELogo.png" alt="Default Logo" class="brand-image img-circle elevation-3"
                style="opacity: 0.8" />
        @endif
        <span class="brand-text font-weight-light">{{ $companyname ? $companyname->name_company : 'AdminLTE 3' }}</span>
    </a>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="../../../dist/img/user.png" class="img-circle elevation-2" alt="User Image" />
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search" />
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @canany(['dashboard.superadmin', 'dashboard.employee'])
                    @can('dashboard.superadmin')
                        <li class="nav-item">
                            <a href="{{ route('dashboard.index') }}"
                                class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                                <i class="fas fa-home nav-icon"></i>
                                <p>Admin Dashboard</p>
                            </a>
                        </li>
                    @endcan

                    @can('dashboard.employee')
                        <li class="nav-item">
                            <a href="{{ route('dashboardemployee.index') }}"
                                class="nav-link {{ request()->routeIs('dashboardemployee.index') ? 'active' : '' }}">
                                <i class="fas fa-home nav-icon"></i>
                                <p>Employee Dashboard</p>
                            </a>
                        </li>
                    @endcan
                @endcanany

                @canany(['user.index', 'user.create', 'role.index', 'role.create'])
                    @php
                        $isMasterDataOpen = request()->routeIs('datauser.*') || request()->routeIs('role.*');
                    @endphp
                    <li class="nav-item {{ $isMasterDataOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $isMasterDataOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>
                                Master Data
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('datauser.index') }}"
                                    class="nav-link {{ request()->routeIs('datauser.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('role.index') }}"
                                    class="nav-link {{ request()->routeIs('role.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Role</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                @canany(['resignationrequest.approver', 'offrequest.approver', 'overtime.approvals', 'kasbon.index', 'payroll.index'] )
                    @php
                        $isHrMenuOpen = 
                            request()->routeIs('resignationrequest.approver') || 
                            request()->routeIs('offrequest.approver') || 
                            request()->routeIs('overtime.approvals') ||
                            request()->routeIs('kasbon.index') ||
                            request()->routeIs('payroll.index');
                    @endphp
                    <li class="nav-item {{ $isHrMenuOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $isHrMenuOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>
                                HR Menu
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        
                        <ul class="nav nav-treeview">
                            @can('kasbon.index')
                            <li class="nav-item">
                                <a href="{{ route('kasbon.index') }}" 
                                    class="nav-link {{ request()->routeIs('kasbon.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kasbon</p>
                                </a>
                            </li>
                            @endcan

                            @can('offrequest.approver')
                            <li class="nav-item">
                                <a href="{{ route('offrequest.approver') }}"
                                    class="nav-link {{ request()->routeIs('offrequest.approver') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>
                                        Off Request Approval
                                        @if ($pendingCount > 0)
                                            <span class="right badge badge-danger">{{ $pendingCount }}</span>
                                        @endif

                                    </p>
                                </a>
                            </li>
                            @endcan

                            @can('overtime.approvals')
                            <li class="nav-item">
                                <a href="{{ route('overtime.approvals') }}" 
                                    class="nav-link {{ request()->routeIs('overtime.approvals') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Overtime Approval</p>
                                    @if ($pendingOvertimeCount > 0)
                                    <span class="right badge badge-danger">{{ $pendingOvertimeCount }}</span>
                                    @endif
                                </a>
                            </li>
                            @endcan

                            @can('payroll.index')
                            <li class="nav-item">
                                <a href="{{ route('payroll.index') }}" 
                                    class="nav-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Payroll</p>
                                </a>
                            </li>
                            @endcan

                            @canany('resignationrequest.approver')
                            <li class="nav-item">
                                <a href="{{ route('resignationrequest.approver') }}"
                                    class="nav-link {{ request()->routeIs('resignationrequest.approver') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Resignation Request Approval</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @canany(['employee.index', 'employee.create', 'attendance.index', 'attendance.scan', 'offrequest.index',
                    'offrequest.create', 'payroll.index', 'payroll.create', 'divisions.index',
                    'divisions.create', 'submitresign.index', 'submitresign.create', 'resignationrequest.index',
                    'resignationrequest.create'])
                    @php
                        $isEmployeeDataOpen = 
                            request()->routeIs('employee.*') || 
                            request()->routeIs('attandance.*') || 
                            request()->routeIs('offrequest.index') ||
                            request()->routeIs('offrequest.create') ||
                            request()->routeIs('submitresign.*') || 
                            request()->routeIs('resignationrequest.index') || 
                            request()->routeIs('resignationrequest.create') || 
                            request()->routeIs('divisions.*');
                    @endphp
                    <li
                        class="nav-item {{ $isEmployeeDataOpen ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ $isEmployeeDataOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-id-badge"></i>
                            <p>
                                Employee Data
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @canany(['employee.index', 'employee.create'])
                                <li class="nav-item">
                                    @can('employee.index')
                                        <a href="{{ route('employee.index') }}"
                                            class="nav-link {{ request()->routeIs('employee.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Employee</p>
                                        </a>
                                    @elsecan('employee.create')
                                        <a href="{{ route('employee.create') }}"
                                            class="nav-link {{ request()->routeIs('employee.create') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Create Employee</p>
                                        </a>
                                    @endcan
                                </li>
                            @endcanany

                            @canany(['attendance.index'])
                                <li class="nav-item">
                                    @can('attendance.index')
                                        <a href="{{ route('attandance.index') }}"
                                            class="nav-link {{ request()->routeIs('attandance.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Attendance</p>
                                        </a>
                                    @endcan
                                </li>
                            @endcanany

                            @canany(['attendance.scan'])
                                <li class="nav-item">
                                    @can('attendance.scan')
                                        <a href="{{ route('attandance.scanView') }}"
                                            class="nav-link {{ request()->routeIs('attandance.scanView') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Attendance Scan</p>
                                        </a>
                                    @endcan
                                </li>
                            @endcanany

                            @canany(['resignationrequest.index', 'resignationrequest.create'])
                                <li class="nav-item">
                                    <a href="{{ route('resignationrequest.index') }}"
                                        class="nav-link {{ request()->routeIs('resignationrequest.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Resignation Request</p>
                                    </a>
                                @elsecan( 'resignationrequest.create')
                                    <a href="{{ route('resignationrequest.create') }}"
                                        class="nav-link {{ request()->routeIs('resignationrequest.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Resignation Request</p>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['submitresign.index', 'submitresign.create'])
                                <li class="nav-item">
                                    @can('submitresign.index')
                                        <a href="{{ route('submitresign.index') }}"
                                            class="nav-link {{ request()->routeIs('submitresign.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Submit Resignation</p>
                                        </a>
                                    @elsecan('submitresign.create')
                                        <a href="{{ route('submitresign.create') }}"
                                            class="nav-link {{ request()->routeIs('submitresign.create') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Submit Resignation</p>
                                        </a>
                                    @endcan
                                </li>
                            @endcanany

                            @canany(['offrequest.index', 'offrequest.create'])
                                <li class="nav-item">
                                    @can('offrequest.index')
                                        <a href="{{ route('offrequest.index') }}"
                                            class="nav-link {{ request()->routeIs('offrequest.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Off Request</p>
                                        </a>
                                    @elsecan('offrequest.create')
                                        <a href="{{ route('offrequest.create') }}"
                                            class="nav-link {{ request()->routeIs('offrequest.create') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Create Off Request</p>

                                        </a>
                                    @endcan
                                </li>
                            @endcanany

                            @canany(['divisions.index', 'divisions.create'])
                                <li class="nav-item">
                                    @can('divisions.index')
                                        <a href="{{ route('divisions.index') }}"
                                            class="nav-link {{ request()->routeIs('divisions.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Division</p>
                                        </a>
                                    @elsecan('divisions.create')
                                        <a href="{{ route('divisions.create') }}"
                                            class="nav-link {{ request()->routeIs('divisions.create') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Division Create</p>
                                        </a>
                                    @endcan
                                </li>
                            @endcanany
                        </ul>
                    </li>
                @endcanany

                @canany(['recruitment.index', 'recruitment.create'])
                    <li class="nav-item">
                        @can('recruitment.index')
                            <a href="{{ route('recruitment.index') }}"
                                class="nav-link {{ request()->routeIs('recruitment.index') ? 'active' : '' }}">
                                <i class="fas fa-users nav-icon"></i>
                                <p>Recruitment</p>
                            </a>
                        @endcan
                    </li>
                @endcanany

                @canany(['event.index'])
                    <li class="nav-item menu-open">
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                @can('event.index')
                                    <a href="{{ route('event.index') }}"
                                        class="nav-link {{ request()->routeIs('event.index') ? 'active' : '' }}">
                                        <i class="fas fa-calendar nav-icon"></i>
                                        <p>Calender</p>
                                    </a>
                                @endcan

                            </li>
                        </ul>
                    </li>

                @endcanany


                @canany(['overtime.create'])
                    <li class="nav-item menu-open">
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                @can('overtime.create')
                                    <a href="{{ route('overtime.index') }}" class="nav-link">
                                        <i class="fas fa-clock nav-icon"></i>
                                        <p>Overtime</p>
                                    </a>
                                @endcan
                            </li>
                        </ul>
                    </li>
                @endcanany

                @canany(['employeebook.index'])
                    <li class="nav-item menu-open">
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('employeebooks.index') }}"
                                    class="nav-link {{ request()->routeIs('employeebooks.index') ? 'active' : '' }}">
                                    <i class="fas fa-book nav-icon"></i>
                                    <p>Employee Books</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                @canany(['slippay.index'])
                    <li class="nav-item menu-open">
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('slippay.index') }}" class="nav-link">
                                    <i class="fas fa-credit-card nav-icon"></i>
                                    <p>Slip Pay</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                @canany(['settings.index'])
                    <li class="nav-item menu-open">
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('settings.index') }}"
                                    class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                    <i class="fas fa-cog nav-icon"></i>
                                    <p>Setting</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany


                <!-- Logout -->
                <li class="nav-item mt-3">
                    <a href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <p>{{ __('Log Out') }}</p>
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                        @csrf
                    </form>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
