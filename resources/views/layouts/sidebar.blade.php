<aside class="main-sidebar sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
        <span class="brand-text font-weight-light"> <img src="{{ asset('assets/img/logo/site-logo.jpg') }}" width=80%" alt="Logo"></span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel d-flex mb-3 mt-3 pb-3">
            <div class="image">
                <img src="{{ asset('dist/img/default-profile.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name ?? '' }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- Sidebar Menu -->
        @php
            $route_name = request()->route()->getName();
        @endphp
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @if (auth()->user())
                    <!-- Primary menu required by spec -->
                    <li class="nav-item {{ request()->routeIs('dashboard') ? 'menu-open' : '' }}">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Home</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('operations-monitor') ? 'menu-open' : '' }}">
                        <a href="{{ route('operations-monitor') }}" class="nav-link {{ request()->routeIs('operations-monitor') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Operations Monitor</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('hos-reports*') ? 'menu-open' : '' }}">
                        <a href="{{ route('hos-reports') }}" class="nav-link {{ request()->routeIs('hos-reports*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Reports</p>
                        </a>
                    </li>


                    @if(auth()->user()->hasPermission('view-fuel-grades'))
                    <li class="nav-item {{ request()->routeIs('price-updates') ? 'menu-open' : '' }}">
                        <a href="{{ route('price-updates') }}" class="nav-link {{ request()->routeIs('price-updates') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-dollar-sign"></i>
                            <p>Price Update </p>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item {{ request()->routeIs('alerts.index') ? 'menu-open' : '' }}">
                        <a href="{{ route('alerts.index') }}" class="nav-link {{ request()->routeIs('alerts.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exclamation-triangle"></i>
                            <p>Alerts</p>
                        </a>
                    </li>

                    @if (config('app.show_notifications_menu'))
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                    @endif
                  
                    @if(auth()->user()->hasPermission('view-users'))
                    <li class="nav-item {{ in_array($route_name, ['users.index', 'users.create', 'users.edit', 'users.show']) ? 'menu-open' : '' }}">
                        <a href="{{ route('users.index') }}" class="nav-link {{ in_array($route_name, ['users.index', 'users.create', 'users.edit', 'users.show']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>User Management</p>
                        </a>
                    </li>
                    @endif

                    @if (config('app.show_help_menu'))
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-question-circle"></i>
                            <p>Help</p>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link p-0 text-left">
                                <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                                <p class="text-danger">Logout</p>
                            </button>
                        </form>
                    </li>

                    @if (config('app.show_legacy_menu'))
                        <li class="nav-header">Legacy</li>
                        @if(config('app.show_pump_transactions_menu'))
                        <li class="nav-item {{ $route_name == 'pump_transactions' ? 'menu-open' : ''}}">
                            <a href="{{ route('pump_transactions') }}" class="nav-link {{ $route_name == 'pump_transactions' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>Pump Transactions</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_pumps_menu'))
                        <li class="nav-item {{ $route_name == 'pumps' ? 'menu-open' : ''}}">
                            <a href="{{ route('pumps') }}" class="nav-link {{ $route_name == 'pumps' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-gas-pump"></i>
                                <p>Pumps</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_tank_measurements_menu'))
                        <li class="nav-item {{ $route_name == 'tank_measurements' ? 'menu-open' : ''}}">
                            <a href="{{ route('tank_measurements') }}" class="nav-link {{ $route_name == 'tank_measurements' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-tint"></i>
                                <p>Tank Measurements</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_tank_deliveries_menu'))
                        <li class="nav-item {{ $route_name == 'tank_deliveries' ? 'menu-open' : ''}}">
                            <a href="{{ route('tank_deliveries') }}" class="nav-link {{ $route_name == 'tank_deliveries' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-truck-loading"></i>
                                <p>Tank Deliveries</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_tank_inventories_menu'))
                        <li class="nav-item {{ $route_name == 'tank_inventories' ? 'menu-open' : ''}}">
                            <a href="{{ route('tank_inventories') }}" class="nav-link {{ $route_name == 'tank_inventories' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Tank Inventories</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_product_wise_summaries_menu'))
                        <li class="nav-item {{ $route_name == 'product_wise_summaries' ? 'menu-open' : ''}}">
                            <a href="{{ route('product_wise_summaries') }}" class="nav-link {{ $route_name == 'product_wise_summaries' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Product Summaries</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_payment_mode_wise_summaries_menu'))
                        <li class="nav-item {{ $route_name == 'payment_mode_wise_summaries' ? 'menu-open' : ''}}">
                            <a href="{{ route('payment_mode_wise_summaries') }}" class="nav-link {{ $route_name == 'payment_mode_wise_summaries' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-credit-card"></i>
                                <p>Payment Summaries</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_shift_templates_menu'))
                        <li class="nav-item {{ $route_name == 'shift_templates' ? 'menu-open' : ''}}">
                            <a href="{{ route('shift_templates') }}" class="nav-link {{ $route_name == 'shift_templates' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Shift Templates</p>
                            </a>
                        </li>
                        @endif
                        @if(config('app.show_pts_users_menu'))
                        <li class="nav-item {{ $route_name == 'pts_users' ? 'menu-open' : ''}}">
                            <a href="{{ route('pts_users') }}" class="nav-link {{ $route_name == 'pts_users' ? 'active' : ''}}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>PTS Users</p>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('view-roles'))
                        <li class="nav-item {{ in_array($route_name, ['roles.index', 'roles.create', 'roles.edit', 'roles.show']) ? 'menu-open' : '' }}">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ in_array($route_name, ['roles.index', 'roles.create', 'roles.edit', 'roles.show']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Role Management</p>
                            </a>
                        </li>
                        @endif
                    @endif
                @endif

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

