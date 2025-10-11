<style>
    .main-container1 i {
        font-size: 20px;
    }

    .admin-img {
        padding-right: 30px;
        display: flex;
        justify-content: end;
        align-items: center;
        width: 15%;
    }

    .rounded-circle {
        width: 40%;
        height: auto;
        object-fit: cover;
    }

    @media(max-width:768px) {
        .rounded-circle {
            width: 70%;
        }
    }

    @media(max-width:480px) {
        .rounded-circle {
            width: 80%;
        }
    }

    @media(max-width:468px) {
        .rounded-circle {
            width: 100%;
        }

        .admin-img {
            width: 20%;
        }
    }

    .dropdown1 {
        border: none;
        font-family: 'Poppins', sans-serif;
    }

    .navbar-expand .navbar-nav .nav-link {
        color: white;
    }

    .navbar-light .navbar-nav .nav-link:focus,
    .navbar-light .navbar-nav .nav-link:hover {
        color: white !important;
    }

    .text-primary1 {
        color: white;
    }
</style>
<nav class="main-header navbar navbar-expand navbar-white navbar-light fixed-top d-flex justify-content-between align-items-center"
    style="font-family: 'Poppins', sans-serif; background-color: #034569; border:none; z-index: 1030;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>

    </ul>

    <!-- Right navbar links -->
    <div class="main-container1 d-flex justify-content-end align-items-center">
        <div class="admin-img">
            <!-- notification icon -->
            <a href="/notification" class="position-relative d-inline-block" style="width: 24px; height: 24px;">
                <i class="fas fa-bell text-primary1 mt-1"></i>
                <span id="notificationBadge" class="badge rounded-pill bg-danger position-absolute text-white" style="top: -5px; right: -5px; font-size: 0.65rem; padding: 2px 6px; display: none;">
                    <!-- Dynamic count will be inserted here -->
                </span>
            </a>

            <!-- Settings Dropdown -->
            <div class="dropdown" style="margin: 0 15px;">
                <button class="btn dropdown1" type="button" id="settingsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog text-primary1" style="font-size: 20px;"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="settingsDropdown">
                    <a class="dropdown-item" href="/site-information">
                        <i class="fas fa-info-circle"></i> Site Information
                    </a>
                    <a class="dropdown-item" href="/application-settings">
                        <i class="fas fa-cogs"></i> Application
                    </a>
                    <a class="dropdown-item" href="/dispenser">
                        <i class="fas fa-gas-pump"></i> Dispenser
                    </a>
                    <a class="dropdown-item" href="/product">
                        <i class="fas fa-oil-can"></i> Product
                    </a>
                    <a class="dropdown-item" href="/log-viewer">
                        <i class="fas fa-file-alt"></i> Logs
                    </a>
                    <a class="dropdown-item" href="/manage-access">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="dropdown-item" href="/stations">
                        <i class="fas fa-gas-pump"></i> Stations
                    </a>
                </div>
            </div>

            <!-- Profile Dropdown-->
            <div class="dropdown">
                <button class="btn dropdown1" type="button" id="profileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img id="userProfileImage" src="{{ asset('/assets/img/team/adminprofile-image.png') }}" alt="Profile" class="rounded-circle" style="width: 25px; height: 25px;">
                </button>
                <div class="dropdown-menu" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="/personal-information">Profile Manage</a>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        <hr>
        <script>
            let userImageUrl = "{{ asset('/assets/img/team/adminprofile-image.png') }}";
            document.getElementById('userProfileImage').src = userImageUrl;

            // Fetch notification count and update badge
            fetch('/alerts/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                });
        </script>

    </div>
    </div>
</nav>
