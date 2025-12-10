<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="/" class="nav-link">Home</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" id="notificationsDropdown" role="button">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge" id="notificationBadge" style="display: none;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationsMenu">
                <span class="dropdown-item dropdown-header" id="notificationHeader">No New Notifications</span>
                <div class="dropdown-divider"></div>
                <div id="notificationsList">
                    <div class="dropdown-item text-center text-muted py-3">
                        <small>No notifications</small>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer" id="clearAllNotifications" style="display: none;">Mark all as read</a>
            </div>
        </li>
        <!-- Dark Mode Toggle -->
        <li class="nav-item">
            <a class="nav-link" href="#" role="button" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" role="button" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                Logout
            </a>
        </li>
    </ul>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</nav>
