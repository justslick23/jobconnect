@extends('layouts.app')

@section('title', 'Reports & Analytics')

@push('styles')
<style>
    :root {
        --primary: #1572e8;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }
    .stat-card { text-align: center; padding: 1.5rem; }
    .stat-number { font-size: 2rem; font-weight: 700; color: var(--primary); }
    .stat-label { font-size: 0.875rem; color: #6c757d; margin-top: 0.5rem; }
    .chart-container { position: relative; height: 300px; }
    .progress-thin { height: 6px; border-radius: 3px; }
    .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.04); }
</style>
@endpush

@section('content')
<div class="page-inner">
    <!-- Header -->
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Reports & Analytics</h3>
            <h6 class="op-7 mb-2">Track your recruitment performance</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <div class="btn-group">
                <button class="btn btn-primary btn-sm">
                    <i class="fa fa-download me-2"></i>Export PDF
                </button>
                <button class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-file-csv me-2"></i>Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" name="date_range">
                                <option value="last_7_days">Last 7 Days</option>
                                <option value="last_30_days" selected>Last 30 Days</option>
                                <option value="last_90_days">Last 90 Days</option>
                                <option value="last_6_months">Last 6 Months</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="job_status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body stat-card">
                    <div class="stat-number">{{ $totalApplications ?? 0 }}</div>
                    <div class="stat-label">Total Applications</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body stat-card">
                    <div class="stat-number">{{ $averageTimeToHire ?? 0 }}</div>
                    <div class="stat-label">Avg. Time to Hire (days)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body stat-card">
                    <div class="stat-number">{{ $conversionRate ?? 0 }}%</div>
                    <div class="stat-label">Conversion Rate</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body stat-card">
                    <div class="stat-number">{{ $activeRecruiters ?? 0 }}</div>
                    <div class="stat-label">Active Recruiters</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recruitment Funnel -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-funnel-dollar me-2"></i>Recruitment Funnel
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($funnelData) && count($funnelData) > 0)
                        @foreach($funnelData as $stage)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium">{{ $stage['stage'] }}</span>
                                <span class="text-muted">{{ $stage['count'] }} ({{ $stage['percentage'] }}%)</span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ $stage['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-3 opacity-25"></i>
                            <p>No funnel data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Application Sources -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-share-alt me-2"></i>Application Sources
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($sourcesData) && count($sourcesData) > 0)
                        @foreach($sourcesData as $source)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-medium">{{ $source['name'] }}</small>
                                <small class="text-muted">{{ $source['count'] }}</small>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $source['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-share-alt fa-2x mb-3 opacity-25"></i>
                            <p>No source data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mt-4">
        <!-- Trends Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-chart-line me-2"></i>Application Trends
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Jobs -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-trophy me-2"></i>Top Jobs
                    </div>
                </div>
                <div class="card-body">
                    @forelse($topPerformingJobs as $job)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-medium">{{ Str::limit($job['title'], 20) }}</div>
                            <small class="text-muted">{{ $job['department'] }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">{{ $job['applications_count'] }}</div>
                            <small class="text-success">{{ $job['conversion_rate'] }}%</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-trophy fa-2x mb-3 opacity-25"></i>
                        <p>No job data available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-building me-2"></i>Department Performance
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Open Positions</th>
                                    <th>Applications</th>
                                    <th>Avg. Time to Hire</th>
                                    <th>Hire Rate</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departmentStats as $dept)
                                <tr>
                                    <td class="fw-medium">{{ $dept['name'] }}</td>
                                    <td>{{ $dept['open_positions'] }}</td>
                                    <td>{{ $dept['total_applications'] }}</td>
                                    <td>{{ $dept['avg_time_to_hire'] }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $dept['hire_rate'] }}%</span>
                                    </td>
                                    <td>
                                        <div class="progress progress-thin" style="width: 60px;">
                                            <div class="progress-bar bg-primary" 
                                                 style="width: {{ $dept['budget_utilization'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No department data available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Initialize trends chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendsChart');
    if (ctx) {
        const trendsData = @json($applicationTrends ?? []);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendsData.map(item => item.month || 'N/A'),
                datasets: [{
                    label: 'Applications',
                    data: trendsData.map(item => item.applications || 0),
                    borderColor: '#1572e8',
                    backgroundColor: 'rgba(21, 114, 232, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Hires',
                    data: trendsData.map(item => item.hires || 0),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});

// Filter form handling
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual endpoint)
    setTimeout(() => {
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // In real implementation, update the page content with new data
        console.log('Filters applied:', Object.fromEntries(params));
    }, 1000);
});

// Auto-apply filters on select change
document.querySelectorAll('#filterForm select').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').dispatchEvent(new Event('submit'));
    });
});
</script>
@endpush