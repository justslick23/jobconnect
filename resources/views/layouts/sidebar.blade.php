<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
      <!-- Logo Header -->
      <div class="logo-header" data-background-color="dark">
          <a href="{{ route('dashboard') }}" class="logo">
              <h3>JobConnect </hr>
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

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
          <ul class="nav nav-secondary">
              @auth
              <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                  <a href="{{ route('dashboard') }}">
                      <i class="fas fa-home"></i>
                      <p>Dashboard</p>
                  </a>
              </li>
              @endauth

              @php $user = auth()->user(); @endphp

              @if($user && $user->isApplicant())
              <li class="nav-section"><h4 class="text-section">Applicant</h4></li>
              <li class="nav-item {{ request()->is('applications*') ? 'active' : '' }}">
                  <a href="{{ route('job-applications.index') }}">
                      <i class="fas fa-file-alt"></i>
                      <p>My Applications</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('job-requisitions*') ? 'active' : '' }}">
                  <a href="{{ route('job-requisitions.index') }}">
                      <i class="fas fa-search"></i>
                      <p>Job Listings</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('profile/complete') ? 'active' : '' }}">
                  <a href="{{ route('applicant.profile.edit') }}">
                      <i class="fas fa-user"></i>
                      <p>Complete Profile</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('interviews*') ? 'active' : '' }}">
                  <a href="{{ route('interviews.index') }}">
                      <i class="fas fa-calendar-alt"></i>
                      <p>Interviews</p>
                  </a>
              </li>
              @endif

              @if($user && $user->isManager())
              <li class="nav-section"><h4 class="text-section">Manager</h4></li>
              <li class="nav-item {{ request()->is('job-requisitions*') ? 'active' : '' }}">
                  <a href="{{ route('job-requisitions.index') }}">
                      <i class="fas fa-briefcase"></i>
                      <p>Requisitions</p>
                  </a>
              </li>
              <li class="nav-item">
                  <a href="#">
                      <i class="fas fa-users"></i>
                      <p>Applicants</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('interviews*') ? 'active' : '' }}">
                  <a href="{{ route('interviews.index') }}">
                      <i class="fas fa-calendar-alt"></i>
                      <p>Interviews</p>
                  </a>
              </li>
              @endif

              @if($user && $user->isHrAdmin())
              <li class="nav-section"><h4 class="text-section">HR/Admin</h4></li>
              <li class="nav-item {{ request()->is('job-requisitions*') ? 'active' : '' }}">
                  <a href="{{ route('job-requisitions.index') }}">
                      <i class="fas fa-briefcase"></i>
                      <p>Job Posts</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('job-applications*') ? 'active' : '' }}">
                  <a href="{{ route('job-applications.index') }}">
                      <i class="fas fa-user-check"></i>
                      <p>Manage Applications</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('interviews*') ? 'active' : '' }}">
                  <a href="{{ route('interviews.index') }}">
                      <i class="fas fa-calendar-alt"></i>
                      <p>Interviews</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('departments*') ? 'active' : '' }}">
                  <a href="{{ route('departments.index') }}">
                      <i class="fas fa-building"></i>
                      <p>Departments</p>
                  </a>
              </li>
        
              <li class="nav-item {{ request()->is('shortlisting-settings*') ? 'active' : '' }}">
                  <a href="{{ route('shortlisting-settings.index') }}">
                      <i class="fas fa-sliders-h"></i>
                      <p>Shortlisting Settings</p>
                  </a>
              </li>
              <li class="nav-item {{ request()->is('users*') ? 'active' : '' }}">
                  <a href="{{ route('users.index') }}">
                      <i class="fas fa-users-cog"></i>
                      <p>Users</p>
                  </a>
              </li>
        
              @endif

              <li class="nav-section"><h4 class="text-section">Analytics</h4></li>
              <li class="nav-item {{ request()->is('reports') ? 'active' : '' }}">
                  <a href="{{ route('reports') }}">
                      <i class="fas fa-chart-bar"></i>
                      <p>Reports</p>
                  </a>
              </li>

              @auth
              <li class="nav-item">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                      @csrf
                  </form>
                  <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      <i class="fas fa-sign-out-alt text-danger"></i>
                      <p class="text-danger">Logout</p>
                  </a>
              </li>
              @endauth
          </ul>
      </div>
  </div>
</div>
<!-- End Sidebar -->