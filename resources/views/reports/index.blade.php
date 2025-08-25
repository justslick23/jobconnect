@extends('layouts.app')
@section('title', 'Recruitment Report')

@section('content')
<div class="container-fluid">
    <!-- Report Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Recruitment Performance Report</h2>
                    <p class="text-muted mb-0">Report Period: {{ ucfirst(str_replace('_', ' ', $filters['date_range'])) }}</p>
                </div>
                <div>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('reports', ['date_range' => 'last_7_days']) }}">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['date_range' => 'last_30_days']) }}">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['date_range' => 'last_90_days']) }}">Last 90 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['date_range' => 'last_6_months']) }}">Last 6 Months</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['date_range' => 'last_year']) }}">Last Year</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-primary btn-sm me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-success btn-sm" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-info btn-sm" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Filter -->
    @if($departments->count() > 1)
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('reports') }}">
                <input type="hidden" name="date_range" value="{{ $filters['date_range'] }}">
                <div class="input-group">
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $filters['department_id'] == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ number_format($totalApplications) }}</h3>
                    <p class="mb-0">Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $conversionRate }}%</h3>
                    <p class="mb-0">Conversion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $averageTimeToHire }}</h3>
                    <p class="mb-0">Avg. Time to Hire (days)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $sourceEffectiveness }}%</h3>
                    <p class="mb-0">Source Effectiveness</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hiring Funnel Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hiring Funnel Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Stage</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                    <th>Conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($funnelData as $stage)
                                <tr>
                                    <td><strong>{{ $stage['stage'] }}</strong></td>
                                    <td>{{ number_format($stage['count']) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $stage['percentage'] }}%">
                                                {{ $stage['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($loop->index > 0 && $funnelData[$loop->index - 1]['count'] > 0)
                                        {{ round(($stage['count'] / $funnelData[$loop->index - 1]['count']) * 100, 1) }}%
                                    @else
                                            100%
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Department Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Open Positions</th>
                                    <th>Total Applications</th>
                                    <th>Applications/Position</th>
                                    <th>Hire Rate</th>
                                    <th>Avg. Time to Hire</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentStats as $dept)
                                <tr>
                                    <td><strong>{{ $dept['name'] }}</strong></td>
                                    <td>{{ $dept['open_positions'] }}</td>
                                    <td>{{ number_format($dept['total_applications']) }}</td>
                                    <td>{{ $dept['applications_per_position'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $dept['hire_rate'] > 5 ? 'success' : ($dept['hire_rate'] > 2 ? 'warning' : 'danger') }}">
                                            {{ $dept['hire_rate'] }}%
                                        </span>
                                    </td>
                                    <td>{{ $dept['avg_time_to_hire'] }}</td>
                                
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Sources & Top Jobs -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Sources</h5>
                </div>
                <div class="card-body">
                    @foreach($sourcesData as $source)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $source['name'] }}</span>
                            <span>{{ number_format($source['count']) }} ({{ $source['percentage'] }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $loop->iteration == 1 ? 'primary' : ($loop->iteration == 2 ? 'info' : ($loop->iteration == 3 ? 'warning' : 'success')) }}" 
                                 style="width: {{ $source['percentage'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Performing Jobs</h5>
                </div>
                <div class="card-body">
                    @if($topPerformingJobs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topPerformingJobs as $job)
                            <div class="list-group-item d-flex justify-content-between align-items-start px-0">
                                <div>
                                    <h6 class="mb-1">{{ $job['title'] }}</h6>
                                    <small class="text-muted">{{ $job['department'] }} • {{ ucfirst($job['status']) }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill">{{ $job['applications_count'] }} applications</span>
                                    <br><small class="text-success">{{ $job['conversion_rate'] }}% conversion</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No job data available for the selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Application Trends -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Trends (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Applications</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Applicant</th>
                                    <th>Position</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentApplications as $application)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 12px;">
                                                {{ substr($application->user->first_name ?? 'N', 0, 1) }}{{ substr($application->user->last_name ?? 'A', 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-sm">{{ $application->user->first_name ?? 'N/A' }} {{ $application->user->last_name ?? '' }}</h6>
                                                <small class="text-muted">{{ $application->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $application->jobRequisition->title ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $application->jobRequisition->department->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $application->created_at->format('M j, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $application->status === 'hired' ? 'success' : 
                                            ($application->status === 'shortlisted' ? 'info' : 
                                            ($application->status === 'offer sent' ? 'warning' : 'secondary')) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Report Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-check-circle"></i> Key Insights</h6>
                            <ul class="list-unstyled">
                                @if($conversionRate > 5)
                                <li class="mb-2"><i class="fas fa-arrow-up text-success me-2"></i>Strong conversion rate of {{ $conversionRate }}%</li>
                                @endif
                                @if($totalApplications > 100)
                                <li class="mb-2"><i class="fas fa-arrow-up text-success me-2"></i>High application volume: {{ number_format($totalApplications) }} applications</li>
                                @endif
                                @if($averageTimeToHire < 30)
                                <li class="mb-2"><i class="fas fa-arrow-up text-success me-2"></i>Efficient hiring process: {{ $averageTimeToHire }} days average</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Areas for Improvement</h6>
                            <ul class="list-unstyled">
                                @if($conversionRate < 3)
                                <li class="mb-2"><i class="fas fa-arrow-down text-warning me-2"></i>Low conversion rate: {{ $conversionRate }}% - review screening process</li>
                                @endif
                                @if($averageTimeToHire > 45)
                                <li class="mb-2"><i class="fas fa-arrow-down text-warning me-2"></i>Long hiring cycle: {{ $averageTimeToHire }} days - streamline process</li>
                                @endif
                                @if($totalApplications < 50)
                                <li class="mb-2"><i class="fas fa-arrow-down text-warning me-2"></i>Low application volume - enhance recruitment marketing</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="row">
        <div class="col-12">
            <div class="text-center text-muted">
                <hr>
                <small>
                    Report generated on {{ now()->format('F j, Y \a\t g:i A') }}<br>
                    Active Recruiters: {{ $activeRecruiters }} | Total Departments: {{ $departments->count() }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Application Trends Chart - Only initialize if element exists and data is available
        const trendsChartElement = document.getElementById('trendsChart');
        if (trendsChartElement && @json(!empty($applicationTrends))) {
            const trendsCtx = trendsChartElement.getContext('2d');
            const trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: @json(array_column($applicationTrends, 'month')),
                    datasets: [{
                        label: 'Applications',
                        data: @json(collect($applicationTrends)->pluck('applications')->toArray()),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Hires',
                        data: @json(collect($applicationTrends)->pluck('hires')->toArray()),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
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
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    }
                }
            });
        }
    });
    
    function exportReport(format) {
        const urlParams = new URLSearchParams({
            date_range: '{{ $filters["date_range"] }}',
            department_id: '{{ $filters["department_id"] ?? "" }}'
        });
        
        if (format === 'pdf') {
            window.open('{{ route("reports.export.pdf") }}?' + urlParams.toString(), '_blank');
        } else if (format === 'csv') {
            window.open('{{ route("reports.export.csv") }}?' + urlParams.toString(), '_blank');
        }
    }
    
    // Print specific styles
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('print-mode');
    });
    
    window.addEventListener('afterprint', function() {
        document.body.classList.remove('print-mode');
    });
    </script>


<style>
.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.progress {
    border-radius: 10px;
}

.table th {
    font-weight: 600;
    color: #6c757d;
}

.list-group-item {
    border: none;
    padding: 0.75rem 0;
}

.avatar {
    font-size: 12px;
    font-weight: 600;
}

@media print {
    .btn, .dropdown, .btn-group {
        display: none !important;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #dee2e6;
        break-inside: avoid;
    }
    
    .print-mode .container-fluid {
        padding: 0;
    }
    
    .row {
        page-break-inside: avoid;
    }
}
</style>
@endsection