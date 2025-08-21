@extends('layouts.auth')

@section('content')
<div class="careers-page">
    <!-- Hero Section -->
    <section class="hero position-relative text-white py-5">
        <!-- Background image -->
        <div class="hero-bg position-absolute top-0 start-0 w-100 h-100" 
             style="background: url('{{ asset('assets/img/CBS-Staff-2024-scaled-1.jpg') }}') center/cover no-repeat;">
        </div>
        <!-- Overlay -->
        <div class="overlay position-absolute top-0 start-0 w-100 h-100" 
             style="background: rgba(5, 6, 52, 0.85);">
        </div>

        <!-- Auth Links (top right) -->
        <div class="position-absolute top-0 end-0 p-3 z-3">
            <div class="d-flex gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endguest
            </div>
        </div>

        <div class="container position-relative z-2">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8 mx-auto text-center">
                   
                    <h1 class="display-3 fw-bold mb-4">
                        Build Your Future at<br>
                        <span class="text-warning">Computer Business Solutions</span>
                    </h1>
                    <p class="lead mb-5 opacity-90">
                        Where innovation meets opportunity. Join our team of passionate professionals 
                        creating tomorrow's digital solutions today.
                    </p>
                    
                    <!-- Metrics Cards -->
                    <div class="row g-3 mb-5">
                        <div class="col-6 col-md-3">
                            <div class="card bg-white bg-opacity-10 border-0 text-center">
                                <div class="card-body py-3">
                                    <h3 class="text-warning mb-0">{{ $jobs->count() }}</h3>
                                    <small class="opacity-75 text-white">Open Positions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-white bg-opacity-10 border-0 text-center">
                                <div class="card-body py-3">
                                    <h3 class="text-warning mb-0">{{ $departments->count() }}</h3>
                                    <small class="opacity-75 text-white">Departments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-white bg-opacity-10 border-0 text-center">
                                <div class="card-body py-3">
                                    <h3 class="text-warning mb-0">65+</h3>
                                    <small class="opacity-75 text-white">Team Members</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-white bg-opacity-10 border-0 text-center">
                                <div class="card-body py-3">
                                    <h3 class="text-warning mb-0">25+</h3>
                                    <small class="opacity-75 text-white">Years Experience</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-rocket me-2"></i>Start Your Journey
                            </a>
                            <a href="#openings" class="btn btn-outline-light btn-lg px-4">
                                <i class="fas fa-arrow-down me-2"></i>View Openings
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-tachometer-alt me-2"></i>Your Dashboard
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Openings Section -->
    <section id="openings" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-3">Current Opportunities</h2>
                    <p class="lead text-muted">Find your perfect role and start your journey with us</p>
                </div>
            </div>
            
            @if($jobs->count())
                <!-- Filter Tabs -->
                <div class="nav nav-pills justify-content-center mb-4 bg-light rounded-pill p-1" style="width: fit-content; margin: 0 auto;">
                    <button class="nav-link active rounded-pill px-4" data-filter="all">
                        All <span class="badge bg-primary ms-1">{{ $jobs->count() }}</span>
                    </button>
                    <button class="nav-link rounded-pill px-4" data-filter="Software Development">
                        Development <span class="badge bg-primary ms-1">{{ $jobs->where('department.name', 'Software Development')->count() }}</span>
                    </button>
                    <button class="nav-link rounded-pill px-4" data-filter="Infrastructure Projects and Services">
                        Infrastructure <span class="badge bg-primary ms-1">{{ $jobs->where('department.name', 'Infrastructure Projects and Services')->count() }}</span>
                    </button>
                    <button class="nav-link rounded-pill px-4" data-filter="Marketing">
                        Marketing <span class="badge bg-primary ms-1">{{ $jobs->where('department.name', 'Marketing')->count() }}</span>
                    </button>
                    <button class="nav-link rounded-pill px-4" data-filter="Sales">
                        Sales <span class="badge bg-primary ms-1">{{ $jobs->where('department.name', 'Sales')->count() }}</span>
                    </button>
                </div>

                <!-- Jobs List -->
                <div class="jobs-container">
                    @foreach($jobs as $job)
                        <div class="job-item card border-0 shadow-sm mb-3 hover-lift" data-department="{{ $job->department->name }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="badge bg-primary">{{ $job->department->name }}</span>
                                            <span class="badge bg-{{ $job->employment_type == 'full-time' ? 'success' : 'warning' }}">
                                                {{ ucfirst($job->employment_type) }}
                                            </span>
                                        </div>
                                        <h5 class="card-title text-primary fw-bold mb-2">{{ $job->title }}</h5>
                                        <div class="d-flex gap-3 text-muted small mb-3">
                                            <span><i class="fas fa-calendar-alt me-1"></i>
                                                {{ $job->application_deadline ? $job->application_deadline->format('M j, Y') : 'Open Application' }}
                                            </span>
                                            <span><i class="fas fa-users me-1"></i>
                                                {{ $job->vacancies }} {{ $job->vacancies == 1 ? 'position' : 'positions' }}
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#job-{{ $job->id }}">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                
                                <div class="collapse" id="job-{{ $job->id }}">
                                    <div class="border-top pt-3 mt-3">
                                        <div class="mb-3">{!! $job->description !!}</div>
                                        <a href="{{ route('job-requisitions.show', $job->slug_uuid) }}" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search display-1 text-muted opacity-50"></i>
                    </div>
                    <h3 class="text-primary">No Current Openings</h3>
                    <p class="text-muted mb-4">Please check back again soon for new openings</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Application Process -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-3">Simple Application Process</h2>
                    <p class="lead text-muted">From application to welcome - here's how it works</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">1</span>
                        </div>
                        <h5 class="fw-bold text-primary">Submit Application</h5>
                        <p class="text-muted small">Complete our online application form with your details and resume</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">2</span>
                        </div>
                        <h5 class="fw-bold text-primary">Initial Review</h5>
                        <p class="text-muted small">Our HR team reviews your application within 3-5 business days</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">3</span>
                        </div>
                        <h5 class="fw-bold text-primary">Interview Process</h5>
                        <p class="text-muted small">Meet with the hiring manager and potential team members</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">4</span>
                        </div>
                        <h5 class="fw-bold text-primary">Welcome Aboard</h5>
                        <p class="text-muted small">Join our team and begin your journey with comprehensive onboarding</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-5 position-relative text-white">
        <!-- Background -->
        <div class="hero-bg position-absolute top-0 start-0 w-100 h-100" 
             style="background: url('{{ asset('assets/img/careers-cta.jpg') }}') center/cover no-repeat;">
        </div>
        <div class="overlay position-absolute top-0 start-0 w-100 h-100" 
             style="background: rgba(5, 6, 52, 0.9);">
        </div>

        <div class="container position-relative z-2">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">Ready to Make an Impact?</h2>
                    <p class="lead mb-4 opacity-90">
                        Don't see the perfect match? We're always interested in connecting with talented individuals who share our vision.
                    </p>
                    
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-arrow-right me-2"></i>Join Our Team
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Custom CSS Variables */
:root {
    --bs-primary: #050634;
    --bs-warning: #dba801;
}

/* Hero + Overlay layers */
.hero-bg {
    z-index: 0;
}
.overlay {
    z-index: 1;
}
.hero .container,
section .container {
    position: relative;
    z-index: 2;
}

/* Auth links top-right */
.z-3 {
    z-index: 3 !important;
}

/* Custom Utilities */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.min-vh-100 {
    min-height: 100vh;
}

/* Job filtering animations */
.job-item {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.3s ease;
}
.job-item.hidden {
    opacity: 0;
    transform: translateY(20px);
    pointer-events: none;
}

/* Nav pills custom styling */
.nav-pills .nav-link {
    color: var(--bs-gray-600);
    font-weight: 500;
    transition: all 0.2s ease;
}
.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
    color: white;
}
.nav-pills .nav-link:not(.active):hover {
    background-color: var(--bs-gray-200);
    color: var(--bs-primary);
}

/* Badge adjustments */
.badge {
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 2.5rem;
    }
    .nav-pills {
        flex-direction: column;
        gap: 0.25rem;
    }
    .nav-pills .nav-link {
        text-align: center;
        width: 100%;
    }
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".nav-pills .nav-link");
    const jobItems = document.querySelectorAll(".job-item");

    // Tab filtering functionality
    tabs.forEach(tab => {
        tab.addEventListener("click", (e) => {
            e.preventDefault();
            
            // Update active tab
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const filter = tab.getAttribute("data-filter");

            // Filter jobs with smooth animation
            jobItems.forEach(item => {
                const department = item.getAttribute("data-department");
                
                if (filter === "all" || department === filter) {
                    item.classList.remove("hidden");
                    item.style.display = "block";
                } else {
                    item.classList.add("hidden");
                    setTimeout(() => {
                        if (item.classList.contains("hidden")) {
                            item.style.display = "none";
                        }
                    }, 300);
                }
            });
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add scroll animation to job items
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-on-scroll');
            }
        });
    }, { threshold: 0.1 });

    jobItems.forEach(item => {
        observer.observe(item);
    });

    // Collapse icon rotation
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            setTimeout(() => {
                const target = document.querySelector(this.getAttribute('data-bs-target'));
                if (target.classList.contains('show')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }, 10);
        });
    });
});
</script>
@endsection