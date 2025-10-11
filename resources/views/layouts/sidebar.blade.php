<aside class="main-sidebar sidebar-dark-primary elevation-4"
    style="background-color: #034569; position: fixed; top: 0; left: 0; bottom: 0; overflow: hidden;font-family: 'Poppins', sans-serif;">
    <!-- Brand Logo with logo above text -->
    <div class="brand-link py-3 text-center"
        style="border-bottom: 1px solid #2d2d42; display: flex; flex-direction: column; align-items: center;">
        @if($globalSiteSettings->site_logo)
        <img src="{{ asset('storage/' . $globalSiteSettings->site_logo) }}" width="100px" alt="Site Logo"
            style="margin-bottom: 10px; max-height: 100px; object-fit: contain;">
        @endif
        <span class="brand-text font-weight-bold" style="font-size: 24px; color:#53AEDF;">{{
            $globalSiteSettings->site_name ?? 'CROVER SYSTEMS' }}</span>
    </div>

    <!-- Sidebar -->
    <div class="sidebar px-0" style="display: flex; flex-direction: column; height: calc(100vh - 120px);">
        <!-- Sidebar Menu with scroll -->
        <nav class="mt-3" style="flex: 1; overflow-y: auto; overflow-x: hidden;">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <div class="d-flex flex-wrap">
                    <!-- Dashboard -->
                    <!-- <li class="nav-item my-2 text-center">
                        <a href="{{ route('home') }}" class="nav-link p-2 text-white" style="display: flex; flex-direction:column; justify-content:center; align-items: center;"> -->

                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('home') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <!-- <a class="nav-link p-2 text-white" href="{{ route('dashboard') }}"> -->
                            <!-- <a href="#" class="nav-link p-2 text-white" style="display: flex; flex-direction: column; align-items: center;"> -->
                            <img src="{{ asset('/assets/img/logo/dashboard.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium collapse.show">Dashboard</span>
                        </a>
                    </li>

                    <!-- Shift Operation -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('shifts.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/operation.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Shift Operation</span>
                        </a>
                    </li>

                    <!-- Pump Transaction -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('pump-transactions.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/trends.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Pump Transaction</span>
                        </a>
                    </li>

                    <!-- Shift Summaries -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('summaries.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/reports.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Shift Summaries</span>
                        </a>
                    </li>

                    <!-- Multi-Shift Reports -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('multi-shift-reports.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/reports.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Multi-Shift Reports</span>
                        </a>
                    </li>

                    <!-- Mop Change -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('mop-change.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/card.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Mop Change</span>
                        </a>
                    </li>

                    <!-- Pump -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('pump') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">

                            <img src="{{ asset('/assets/img/logo/reports.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Pump</span>
                        </a>

                    </li>

                    <!-- Price update -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('pump.priceupdate') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">

                            <img src="{{ asset('/assets/img/logo/status.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Price Update</span>
                        </a>
                    </li>

                    <!-- Tank Measurement -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('tank-measurement.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">

                            <img src="{{ asset('/assets/img/logo/configure.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Tank Measurement</span>
                        </a>
                    </li>

                    <!-- Tank Inventory -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('tank-inventory.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/configure.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Tank Inventory</span>
                        </a>
                    </li>
                    <!-- in tank delivery -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('in-tank-delivery.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/configure.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">In Tank Delivery</span>
                        </a>
                    </li>
                    <!-- tank monitoring -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('in-tank-delivery.monitoring') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/monitoring.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Tank Monitoring</span>
                        </a>
                    </li>

                    <!-- Station Management -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('stations.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">

                            <img src="{{ asset('/assets/img/logo/configure.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">Stations</span>
                        </a>
                    </li>

                    <!-- PTS Users Management -->
                    <li class="nav-item my-2 text-center">
                        <a href="{{ route('pts-users.index') }}" class="nav-link p-2 text-white"
                            style="display: flex; flex-direction: column; align-items: center;">
                            <img src="{{ asset('/assets/img/logo/configure.svg') }}" width="24px" class="mb-1">
                            <span class="brand-text medium">PTS Users</span>
                        </a>
                    </li>
                </div>
            </ul>
        </nav>

        <!-- Logout at bottom - Fixed position -->
        <div class="brand-link py-3 text-center"
            style="border-top: 1px solid #2d2d42; border-bottom:none; display: flex; flex-direction: column; align-items: center; margin-top: auto;">
            <a class="nav-link text-white"
                style="display: flex; flex-direction: column; align-items: center; margin-right:18px;" role="button"
                href="{{ route('logout') }}"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <img src="{{ asset('/assets/img/logo/logoutbutton.svg') }}" width="24px" class="mb-1">
                <span class="brand-text medium" style="font-size: 1rem; color:#53AEDF;">Logout</span>
            </a>
        </div>
    </div>



    <!-- <div class="brand-link py-3 text-center" style="border-bottom: 1px solid #2d2d42; display: flex; flex-direction: column; align-items: center;">
            <a class="nav-link text-white" style="display: flex; flex-direction: column; align-items: center;" role="button" href="{{ route('logout') }}"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <img src="{{ asset('/assets/img/logo/logoutbutton.svg') }}" width="24px" class="mb-1">
                <span class="medium" style="font-size: 1rem; color:#53AEDF;">Logout</span>
            </a>
        </div> -->

    <!-- <div class="brand-link py-3 text-center" style="border-bottom: 1px solid #2d2d42; display: flex; flex-direction: column; align-items: center;">
            <img src="{{ asset('/assets/img/logo/logoutbutton.svg') }}" width="24px" class="mb-1">
            <span class="brand-text font-weight-bold" style="font-size: 1rem; color:#53AEDF;">Logout</span>
        </div> -->
</aside>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
    /* Custom styling */
    .nav-item.active {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
    }

    .brand-link {
        padding: 1rem 0.5rem !important;
    }
</style>