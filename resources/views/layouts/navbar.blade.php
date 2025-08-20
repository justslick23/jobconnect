<div class="main-header">
    <div class="main-header-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo">
                <img
                    src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}"
                    alt="navbar brand"
                    class="navbar-brand"
                    height="20"
                />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
            </div>
        </div>
        <!-- End Logo Header -->
    </div>

    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid">

            <!-- Page Title (Optional) -->
            <div class="navbar-brand d-lg-block d-none">
                <h4 class="mb-0 text-dark">@yield('title', 'Dashboard')</h4>
            </div>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">

                <!-- Notifications -->
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-bell"></i>
                        <span class="notification">3</span>
                    </a>
                    <ul class="dropdown-menu notif-box animated fadeIn">
                        <li><div class="dropdown-title">You have 3 new notifications</div></li>
                        <li>
                            <div class="notif-scroll scrollbar-outer">
                                <div class="notif-center">
                                    <a href="#">
                                        <div class="notif-icon notif-primary"><i class="fa fa-user-plus"></i></div>
                                        <div class="notif-content">
                                            <span class="block">New user registered</span>
                                            <span class="time">5 minutes ago</span>
                                        </div>
                                    </a>
                                    <a href="#">
                                        <div class="notif-icon notif-success"><i class="fa fa-comment"></i></div>
                                        <div class="notif-content">
                                            <span class="block">New application received</span>
                                            <span class="time">12 minutes ago</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li><a class="see-all" href="#">See all notifications <i class="fa fa-angle-right"></i></a></li>
                    </ul>
                </li>

                <!-- User Profile Dropdown -->
                @auth
                <li class="nav-item topbar-user dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic d-flex align-items-center" data-bs-toggle="dropdown" href="#">
                        <div class="avatar-sm rounded-circle bg-primary d-flex justify-content-center align-items-center me-2" style="width:40px; height:40px; font-size:16px; color:#fff;">
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => $n[0])->join('') }}
                        </div>
                        <span class="fw-bold text-dark">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <li>
                            <div class="user-box d-flex align-items-center p-3">
                                <div class="avatar-lg rounded-circle bg-primary d-flex justify-content-center align-items-center me-3" style="width:60px; height:60px; font-size:24px; color:#fff;">
                                    {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => $n[0])->join('') }}
                                </div>
                                <div class="u-text">
                                    <h4 class="mb-0">{{ auth()->user()->name }}</h4>
                                    <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a>
                            <a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
                @endauth

            </ul>
        </div>
    </nav>
</div>
