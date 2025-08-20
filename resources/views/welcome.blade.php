@extends('layouts.auth')

@section('content')
<div class="careers-page">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-pattern"></div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-icon">🚀</span>
                    <span>Join Our Team</span>
                </div>
                <h1 class="hero-title">
                    Build Your Future at
                    <br><span class="highlight">Computer Business Solutions</span>
                </h1>
                <p class="hero-subtitle">
                    Where innovation meets opportunity. Join our team of passionate professionals 
                    creating tomorrow's digital solutions today.
                </p>
                
                <div class="hero-metrics">
                    <div class="metric">
                        <div class="metric-value">{{ $jobs->count() }}</div>
                        <div class="metric-label">Open Positions</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $departments->count() }}</div>
                        <div class="metric-label">Departments</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">65+</div>
                        <div class="metric-label">Team Members</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">25+</div>
                        <div class="metric-label">Years Experience</div>
                    </div>
                </div>
                
                <div class="hero-cta">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <span>Start Your Journey</span>
                            <i class="fas fa-rocket"></i>
                        </a>
                        <a href="#openings" class="btn btn-outline">
                            <span>View Openings</span>
                            <i class="fas fa-arrow-down"></i>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <span>Your Dashboard</span>
                            <i class="fas fa-tachometer-alt"></i>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

 

    <!-- Job Openings Section -->
    <section id="openings" class="openings">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Current Opportunities</h2>
                <p class="section-subtitle">Find your perfect role and start your journey with us</p>
            </div>
            
            @if($jobs->count())
                <div class="filter-tabs">
                    <button class="tab active" data-filter="all">
                        <span>All Positions</span>
                        <span class="tab-count">{{ $jobs->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Software Development">
                        <span>Development</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Software Development')->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Infrastructure Projects and Services">
                        <span>Infrastructure</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Infrastructure Projects and Services')->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Marketing">
                        <span>Marketing</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Marketing')->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Sales">
                        <span>Sales</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Sales')->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Training">
                        <span>Training</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Training')->count() }}</span>
                    </button>
                    <button class="tab" data-filter="Finance and Administration">
                        <span>Finance</span>
                        <span class="tab-count">{{ $jobs->where('department.name', 'Finance and Administration')->count() }}</span>
                    </button>
                </div>

                <div class="jobs-container">
                    @foreach($jobs as $job)
                        <div class="job-item mb-3" data-department="{{ $job->department->name }}">
                            <div class="job-content d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="job-title mb-1">{{ $job->title }}</h3>
                                    <div class="job-meta text-muted small">
                                        <span class="job-department">{{ $job->department->name }}</span> &bullet; 
                                        <span class="job-type {{ strtolower($job->employment_type) }}">{{ ucfirst($job->employment_type) }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#job-{{ $job->id }}">
                                    <i class="fas fa-chevron-down"></i> Details
                                </button>
                            </div>
                
                            <div class="collapse mt-2" id="job-{{ $job->id }}">
                                <div class="job-details p-3 border rounded">
                                    <div class="job-detail mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <span>{{ $job->application_deadline ? $job->application_deadline->format('M j, Y') : 'Open Application' }}</span>
                                    </div>
                                    <div class="job-detail mb-2">
                                        <i class="fas fa-users me-1"></i>
                                        <span>{{ $job->vacancies }} {{ $job->vacancies == 1 ? 'position' : 'positions' }}</span>
                                    </div>
                                    <div class="job-detail mb-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span>{!! $job->description !!}</span>
                                    </div>
                                    
                                    <a href="{{ route('job-requisitions.show', $job->slug_uuid) }}" class="btn btn-primary btn-sm mt-2">
                                        Apply Now <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
            @else
                <div class="no-openings">
                    <div class="no-openings-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Current Openings</h3>
                    <p>We're always looking for exceptional talent. Submit your resume and we'll reach out when the right opportunity arises.</p>
                    <a href="#" class="btn btn-outline">
                        <span>Submit Resume</span>
                        <i class="fas fa-file-upload"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Application Process -->
    <section class="process">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Simple Application Process</h2>
                <p class="section-subtitle">From application to welcome - here's how it works</p>
            </div>
            
            <div class="process-timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="step-number">1</span>
                    </div>
                    <div class="timeline-content">
                        <h4>Submit Application</h4>
                        <p>Complete our online application form with your details and resume</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="step-number">2</span>
                    </div>
                    <div class="timeline-content">
                        <h4>Initial Review</h4>
                        <p>Our HR team reviews your application within 3-5 business days</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="step-number">3</span>
                    </div>
                    <div class="timeline-content">
                        <h4>Interview Process</h4>
                        <p>Meet with the hiring manager and potential team members</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <span class="step-number">4</span>
                    </div>
                    <div class="timeline-content">
                        <h4>Welcome Aboard</h4>
                        <p>Join our team and begin your journey with comprehensive onboarding</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Make an Impact?</h2>
                <p>Don't see the perfect match? We're always interested in connecting with talented individuals who share our vision.</p>
                
                <div class="cta-actions">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <span>Join Our Team</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <span>Go to Dashboard</span>
                            <i class="fas fa-tachometer-alt"></i>
                        </a>
                    @endguest
                    
                
                </div>
            </div>
        </div>
    </section>
</div>
<style>
    :root {
        --primary: #050634;
        --gold: #dba801;
        --red: #ef4444;
        --white: #ffffff;
        --gray: #6b7280;
        --light-gray: #f8fafc;
        --dark-gray: #374151;
        --border: #e5e7eb;
        
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .careers-page {
        background: var(--white);
        color: var(--dark-gray);
        font-family: 'Inter', sans-serif;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    /* Hero Section */
    .hero {
        background: var(--primary);
        color: var(--white);
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .hero-pattern {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 30%, var(--gold) 0%, transparent 40%),
                    radial-gradient(circle at 80% 70%, var(--red) 0%, transparent 40%),
                    radial-gradient(circle at 60% 20%, rgba(255,255,255,0.1) 0%, transparent 30%);
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.15);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        margin-bottom: 2rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        font-weight: 600;
    }

    .badge-icon {
        font-size: 1.1rem;
    }

    .hero-title {
        font-size: clamp(2.5rem, 6vw, 4.5rem);
        font-weight: 900;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
    }

    .highlight {
        background: linear-gradient(135deg, var(--gold) 0%, var(--red) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 3rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .metric {
        text-align: center;
        padding: 1.5rem 1rem;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.15);
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--gold);
        line-height: 1;
    }

    .metric-label {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    .hero-cta {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--gold);
        color: var(--primary);
    }

    .btn-primary:hover {
        background: #f1c40f;
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-outline {
        background: transparent;
        color: var(--white);
        border-color: rgba(255,255,255,0.3);
    }

    .btn-outline:hover {
        background: rgba(255,255,255,0.1);
        border-color: var(--white);
    }

    /* Section Headers */
    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .section-subtitle {
        font-size: 1.125rem;
        color: var(--gray);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Benefits Section */
    .benefits {
        padding: 6rem 0;
        background: var(--light-gray);
    }

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
    }

    .benefit-card {
        background: var(--white);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .benefit-icon {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        color: var(--white);
    }

    .benefit-icon.primary { background: var(--primary); }
    .benefit-icon.gold { background: var(--gold); }
    .benefit-icon.red { background: var(--red); }

    .benefit-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .benefit-card p {
        color: var(--gray);
        margin-bottom: 1.5rem;
        line-height: 1.7;
    }

    .benefit-features {
        list-style: none;
    }

    .benefit-features li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .benefit-features li:before {
        content: "✓";
        color: var(--gold);
        font-weight: bold;
        width: 16px;
    }

    /* Openings Section */
    .openings {
        padding: 6rem 0;
    }

    .filter-tabs {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
        background: var(--light-gray);
        padding: 0.5rem;
        border-radius: 16px;
        max-width: fit-content;
        margin-left: auto;
        margin-right: auto;
    }

    .tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: transparent;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        color: var(--gray);
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .tab.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .tab:hover:not(.active) {
        background: rgba(255,255,255,0.7);
        color: var(--primary);
    }

    .tab-count {
        background: var(--gold);
        color: var(--primary);
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        min-width: 20px;
        text-align: center;
    }

    .jobs-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .job-item {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        gap: 2rem;
    }

    .job-item:hover {
        border-color: var(--gold);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .job-content {
        flex: 1;
    }

    .job-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .job-department {
        background: var(--primary);
        color: var(--white);
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .job-type {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .job-type.full-time {
        background: var(--gold);
        color: var(--primary);
    }

    .job-type.part-time {
        background: var(--red);
        color: var(--white);
    }

    .job-type.contract {
        background: var(--gray);
        color: var(--white);
    }

    .job-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .job-details {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .job-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--gray);
        font-size: 0.9rem;
    }

    .job-detail i {
        color: var(--gold);
        width: 16px;
    }

    .job-action {
        flex-shrink: 0;
    }

    .job-apply-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary);
        color: var(--white);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .job-apply-btn:hover {
        background: var(--gold);
        color: var(--primary);
        transform: translateX(5px);
    }

    /* No Openings State */
    .no-openings {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--light-gray);
        border-radius: 20px;
    }

    .no-openings-icon {
        width: 80px;
        height: 80px;
        background: var(--primary);
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 2rem;
    }

    .no-openings h3 {
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .no-openings p {
        color: var(--gray);
        margin-bottom: 2rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Process Section */
    .process {
        padding: 6rem 0;
        background: var(--light-gray);
    }

    .process-timeline {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .process-timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border);
        transform: translateX(-50%);
    }

    .timeline-item {
        display: flex;
        align-items: center;
        margin-bottom: 4rem;
        position: relative;
    }

    .timeline-item:nth-child(odd) {
        flex-direction: row;
    }

    .timeline-item:nth-child(even) {
        flex-direction: row-reverse;
    }

    .timeline-marker {
        width: 60px;
        height: 60px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }

    .step-number {
        color: var(--white);
        font-weight: 800;
        font-size: 1.25rem;
    }

    .timeline-content {
        flex: 1;
        padding: 2rem;
        background: var(--white);
        border-radius: 16px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        margin: 0 2rem;
        max-width: 300px;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        margin-left: 2rem;
        margin-right: 0;
    }

    .timeline-item:nth-child(even) .timeline-content {
        margin-right: 2rem;
        margin-left: 0;
    }

    .timeline-content h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .timeline-content p {
        color: var(--gray);
        line-height: 1.6;
    }

    /* Final CTA */
    .final-cta {
        padding: 6rem 0;
        background: linear-gradient(135deg, var(--primary) 0%, var(--gold) 100%);
        color: var(--white);
    }

    .cta-content {
        text-align: center;
        max-width: 700px;
        margin: 0 auto;
    }

    .cta-content h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .cta-content p {
        font-size: 1.125rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        line-height: 1.7;
    }

    .cta-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .final-cta .btn-primary {
        background: var(--white);
        color: var(--primary);
    }

    .final-cta .btn-primary:hover {
        background: var(--light-gray);
    }

    .final-cta .btn-outline {
        border-color: rgba(255,255,255,0.5);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-stats {
            flex-direction: column;
            gap: 1rem;
        }
        
        .jobs-grid {
            grid-template-columns: 1fr;
        }
        
        .process-steps {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .section-title {
            font-size: 2rem;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tabs = document.querySelectorAll(".filter-tabs .tab");
        const jobItems = document.querySelectorAll(".job-item");
        const jobsContainer = document.querySelector(".jobs-container");

        // Enhanced filtering with smooth animations
        tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                // Remove 'active' class from all tabs
                tabs.forEach(t => t.classList.remove("active"));
                tab.classList.add("active");

                const filter = tab.getAttribute("data-filter");

                // Add fade-out animation
                jobsContainer.style.opacity = "0.5";
                jobsContainer.style.transform = "translateY(10px)";

                setTimeout(() => {
                    let visibleCount = 0;

                    jobItems.forEach((item, index) => {
                        const department = item.getAttribute("data-department");

                        if (filter === "all" || department === filter) {
                            item.style.display = "block";
                            // Stagger the fade-in animation
                            setTimeout(() => {
                                item.style.opacity = "1";
                                item.style.transform = "translateY(0)";
                            }, index * 50);
                            visibleCount++;
                        } else {
                            item.style.display = "none";
                            item.style.opacity = "0";
                            item.style.transform = "translateY(20px)";
                        }
                    });

                    // Restore container animation
                    jobsContainer.style.opacity = "1";
                    jobsContainer.style.transform = "translateY(0)";

                    // Update no results message
                    updateNoResultsMessage(visibleCount, filter);
                }, 150);
            });
        });

        // Initialize job items with transition styles
        jobItems.forEach(item => {
            item.style.transition = "opacity 0.3s ease, transform 0.3s ease";
            item.style.opacity = "1";
            item.style.transform = "translateY(0)";
        });

        // Add transition to jobs container
        if (jobsContainer) {
            jobsContainer.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        }

        // Function to handle no results message
        function updateNoResultsMessage(visibleCount, filter) {
            let noResultsMsg = document.querySelector('.no-results-message');
            
            if (visibleCount === 0 && filter !== 'all') {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div class="no-results-content">
                            <i class="fas fa-search"></i>
                            <h3>No positions found</h3>
                            <p>No current openings in the ${filter} department. Check back soon or browse other departments.</p>
                        </div>
                    `;
                    jobsContainer.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }

        // Add hover effects for job items
        jobItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
                this.style.boxShadow = '0 15px 35px rgba(0,0,0,0.1)';
            });

            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.05)';
            });
        });

        // Add click animation for apply buttons
        const applyButtons = document.querySelectorAll('.job-apply-btn');
        applyButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Create ripple effect
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key >= '1' && e.key <= '7') {
                const tabIndex = parseInt(e.key) - 1;
                if (tabs[tabIndex]) {
                    tabs[tabIndex].click();
                }
            }
        });

        // Add search functionality if search input exists
        const searchInput = document.querySelector('.job-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let visibleCount = 0;

                jobItems.forEach(item => {
                    const title = item.querySelector('.job-title').textContent.toLowerCase();
                    const department = item.getAttribute('data-department').toLowerCase();
                    const isVisible = title.includes(searchTerm) || department.includes(searchTerm);

                    if (isVisible) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                updateNoResultsMessage(visibleCount, 'search');
            });
        }

        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observe job items for scroll animations
        jobItems.forEach(item => {
            observer.observe(item);
        });

        // Add CSS for animations dynamically
        const style = document.createElement('style');
        style.textContent = `
            .job-item {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                transform: translateY(20px);
                opacity: 0;
            }

            .job-item.animate-in {
                transform: translateY(0);
                opacity: 1;
            }

            .job-apply-btn {
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
                margin-top: 2rem;
            }

            .ripple-effect {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                animation: ripple 0.6s linear;
                pointer-events: none;
            }

            @keyframes ripple {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }

            .no-results-message {
                text-align: center;
                padding: 4rem 2rem;
                color: #6b7280;
                grid-column: 1 / -1;
            }

            .no-results-content i {
                font-size: 3rem;
                margin-bottom: 1rem;
                opacity: 0.5;
            }

            .no-results-content h3 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
                color: #374151;
            }

            .filter-tabs .tab {
                transition: all 0.3s ease;
                position: relative;
            }

            .filter-tabs .tab:hover {
                transform: translateY(-2px);
            }

            .filter-tabs .tab.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(45deg, #667eea, #764ba2);
                border-radius: 2px;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    transform: scaleX(0);
                }
                to {
                    transform: scaleX(1);
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize with staggered animation
        setTimeout(() => {
            jobItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('animate-in');
                }, index * 100);
            });
        }, 200);
    });
</script>


@endsection