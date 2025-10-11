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
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->

                {{--                @if (auth()->user()->role == \App\Enums\UserRole::Admin->value) --}}
                {{--                    <li class="nav-item {{$route_name=='user.index'?'menu-open':''}}"> --}}
                {{--                        <a href="{{route('user.index')}}" class="nav-link {{$route_name=='user.index'?'active':''}}"> --}}
                {{--                            <i class="nav-icon fas fa-user"></i> --}}
                {{--                            <p> --}}
                {{--                                Users --}}

                {{--                            </p> --}}
                {{--                        </a> --}}
                {{--                    </li> --}}
                {{--                @endif --}}

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
