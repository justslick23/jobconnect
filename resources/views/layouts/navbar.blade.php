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
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
    <!-- End Logo Header -->
  </div>

  <!-- Navbar Header -->
  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
      <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
        {{-- Just notifications and user profile --}}
        <li class="nav-item topbar-icon dropdown hidden-caret">
          <a class="nav-link" href="#" id="notifDropdown" data-bs-toggle="dropdown">
            <i class="fa fa-bell"></i>
            <span class="notification">2</span>
          </a>
          <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
            <li><div class="dropdown-title">Notifications</div></li>
            <li>
              <div class="notif-scroll scrollbar-outer">
                <div class="notif-center">
                  <div class="notif-item">
                    <div class="notif-content">
                      <span class="subject">New application received</span>
                      <span class="time">5 min ago</span>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </li>

        {{-- User Profile --}}
        @auth
        <li class="nav-item topbar-user dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#">
            <div class="avatar-sm">
              <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle" />
            </div>
            <span class="profile-username">
              <span class="fw-bold">{{ auth()->user()->name }}</span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-user animated fadeIn">
            <li>
              <div class="user-box">
                <div class="avatar-lg">
                  <img src="{{ asset('assets/img/profile.jpg') }}" alt="profile" class="avatar-img rounded" />
                </div>
                <div class="u-text">
                  <h4>{{ auth()->user()->name }}</h4>
                  <p class="text-muted">{{ auth()->user()->email }}</p>
                </div>
              </div>
            </li>
            <li>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a>
              <a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a>
              <div class="dropdown-divider"></div>
              <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
              </form>
            </li>
          </ul>
        </li>
        @endauth
      </ul>
    </div>
  </nav>
  <!-- End Navbar -->
</div>