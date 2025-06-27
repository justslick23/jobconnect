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
        {{-- Search Bar (desktop only) --}}
        <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
          <div class="input-group">
            <div class="input-group-prepend">
              <button type="submit" class="btn btn-search pe-1">
                <i class="fa fa-search search-icon"></i>
              </button>
            </div>
            <input type="text" placeholder="Search ..." class="form-control" />
          </div>
        </nav>
  
        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
          {{-- Mobile Search --}}
          <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
              <i class="fa fa-search"></i>
            </a>
            <ul class="dropdown-menu dropdown-search animated fadeIn">
              <form class="navbar-left navbar-form nav-search">
                <div class="input-group">
                  <input type="text" placeholder="Search ..." class="form-control" />
                </div>
              </form>
            </ul>
          </li>
  
          {{-- Messages Dropdown (Static Demo) --}}
          <li class="nav-item topbar-icon dropdown hidden-caret">
            <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" data-bs-toggle="dropdown">
              <i class="fa fa-envelope"></i>
            </a>
            <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
              <li>
                <div class="dropdown-title d-flex justify-content-between align-items-center">
                  Messages
                  <a href="#" class="small">Mark all as read</a>
                </div>
              </li>
              <li>
                <div class="message-notif-scroll scrollbar-outer">
                  <div class="notif-center">
                    <!-- Static message items or loop through messages -->
                  </div>
                </div>
              </li>
              <li>
                <a class="see-all" href="#">See all messages <i class="fa fa-angle-right"></i></a>
              </li>
            </ul>
          </li>
  
          {{-- Notifications Dropdown (Static Demo) --}}
          <li class="nav-item topbar-icon dropdown hidden-caret">
            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" data-bs-toggle="dropdown">
              <i class="fa fa-bell"></i>
              <span class="notification">4</span>
            </a>
            <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
              <li><div class="dropdown-title">You have 4 new notifications</div></li>
              <li>
                <div class="notif-scroll scrollbar-outer">
                  <div class="notif-center">
                    <!-- Static or dynamic notifications -->
                  </div>
                </div>
              </li>
              <li><a class="see-all" href="#">See all notifications <i class="fa fa-angle-right"></i></a></li>
            </ul>
          </li>
  
          {{-- Quick Actions (optional shortcuts) --}}
          <li class="nav-item topbar-icon dropdown hidden-caret">
            <a class="nav-link" data-bs-toggle="dropdown" href="#"><i class="fas fa-layer-group"></i></a>
            <div class="dropdown-menu quick-actions animated fadeIn">
              <div class="quick-actions-header">
                <span class="title mb-1">Quick Actions</span>
                <span class="subtitle op-7">Shortcuts</span>
              </div>
              <div class="quick-actions-scroll scrollbar-outer">
                <div class="quick-actions-items">
                  <div class="row m-0">
                    {{-- Add your action shortcuts here --}}
                    <a class="col-6 col-md-4 p-0" href="#">
                      <div class="quick-actions-item">
                        <div class="avatar-item bg-info rounded-circle"><i class="fas fa-file-excel"></i></div>
                        <span class="text">Reports</span>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </li>
  
          {{-- User Dropdown --}}
          @auth
          <li class="nav-item topbar-user dropdown hidden-caret">
            <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#">
              <div class="avatar-sm">
                <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle" />
              </div>
              <span class="profile-username">
                <span class="op-7">Hi,</span>
                <span class="fw-bold">{{ auth()->user()->name }}</span>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-user animated fadeIn">
              <div class="dropdown-user-scroll scrollbar-outer">
                <li>
                  <div class="user-box">
                    <div class="avatar-lg">
                      <img src="{{ asset('assets/img/profile.jpg') }}" alt="image profile" class="avatar-img rounded" />
                    </div>
                    <div class="u-text">
                      <h4>{{ auth()->user()->name }}</h4>
                      <p class="text-muted">{{ auth()->user()->email }}</p>
                      <a href="#" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#">My Profile</a>
                  <a class="dropdown-item" href="#">My Balance</a>
                  <a class="dropdown-item" href="#">Inbox</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#">Account Setting</a>
                  <div class="dropdown-divider"></div>
                  <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                  </form>
                </li>
              </div>
            </ul>
          </li>
          @endauth
        </ul>
      </div>
    </nav>
    <!-- End Navbar -->
  </div>
  