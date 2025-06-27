@extends('layouts.app')

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .reports-container {
        padding: 2rem 0;
    }

    .report-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
        transition: transform 0.2s ease;
    }

    .report-card:hover {
        transform: translateY(-2px);
    }

    .report-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }

    .metric-item {
        text-align: center;
        padding: 1rem;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 8px;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }

    .metric-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 0.25rem;
    }

    .metric-label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 500;
    }

    .chart-container {
        padding: 1.5rem;
        height: 300px;
    }

    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
    }

    .table-modern {
        margin-bottom: 0;
    }

    .table-modern th {
        background: #f8fafc;
        border: none;
        font-weight: 600;
        color: #374151;
        padding: 1rem;
        font-size: 0.875rem;
    }

    .table-modern td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }

    .status-active { background: #10b981; }
    .status-closed { background: #ef4444; }
    .status-pending { background: #f59e0b; }

    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

@section('content')
<div class="container-fluid reports-container">
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1" style="color: #374151; font-weight: 700;">📊 Reports & Analytics</h2>
            <p class="text-muted mb-0">Comprehensive insights into your recruitment performance</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="exportPdf()">
                <i class="fas fa-download me-2"></i>Export PDF
            </button>
            <button class="btn btn-outline-secondary" onclick="exportCsv()">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customRangeModal">
                <i class="fas fa-calendar me-2"></i>Custom Range
            </button>
        </div>
    </div>

    <!-- Filters -->
    <form id="filterForm" class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <select class="form-select" name="date_range" id="dateRange">
                    <option value="last_7_days" {{ $filters['date_range'] == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="last_30_days" {{ $filters['date_range'] == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="last_90_days" {{ $filters['date_range'] == 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="last_6_months" {{ $filters['date_range'] == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                    <option value="last_year" {{ $filters['date_range'] == 'last_year' ? 'selected' : '' }}>Last Year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select class="form-select" name="department_id" id="departmentFilter">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $filters['department_id'] == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Job Status</label>
                <select class="form-select" name="job_status" id="jobStatusFilter">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $filters['job_status'] == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="closed" {{ $filters['job_status'] == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="draft" {{ $filters['job_status'] == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Key Metrics Overview -->
    <div class="report-card" id="metricsCard">
        <div class="report-header">
            <i class="fas fa-chart-bar me-2"></i>Key Performance Indicators
        </div>
        <div class="metric-grid">
            <div class="metric-item">
                <div class="metric-number">{{ $totalApplications ?? 0 }}</div>
                <div class="metric-label">Total Applications</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">{{ $averageTimeToHire ?? 0 }}</div>
                <div class="metric-label">Avg. Time to Hire (days)</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">{{ $conversionRate ?? 0 }}%</div>
                <div class="metric-label">Application to Hire Rate</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">{{ $activeRecruiters ?? 0 }}</div>
                <div class="metric-label">Active Recruiters</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">${{ number_format($averageSalary ?? 0) }}</div>
                <div class="metric-label">Average Salary Offered</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">{{ $sourceEffectiveness ?? 0 }}%</div>
                <div class="metric-label">Source Effectiveness</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recruitment Funnel -->
        <div class="col-lg-6">
            <div class="report-card" id="funnelCard">
                <div class="report-header">
                    <i class="fas fa-funnel-dollar me-2"></i>Recruitment Funnel Analysis
                </div>
                <div class="p-4">
                    @if(isset($funnelData) && count($funnelData) > 0)
                        @foreach($funnelData as $stage)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $stage['stage'] }}</span>
                                <span class="text-muted">{{ $stage['count'] }} ({{ $stage['percentage'] }}%)</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: {{ $stage['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No funnel data available for the selected period.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performing Jobs -->
        <div class="col-lg-6">
            <div class="report-card" id="topJobsCard">
                <div class="report-header">
                    <i class="fas fa-trophy me-2"></i>Top Performing Job Postings
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Applications</th>
                                <th>Conversion</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPerformingJobs as $job)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $job['title'] }}</div>
                                    <small class="text-muted">{{ $job['department'] }}</small>
                                </td>
                                <td><span class="fw-bold text-primary">{{ $job['applications_count'] }}</span></td>
                                <td><span class="text-success">{{ $job['conversion_rate'] }}%</span></td>
                                <td>
                                    <span class="status-indicator status-{{ $job['status'] == 'active' ? 'active' : ($job['status'] == 'closed' ? 'closed' : 'pending') }}"></span>
                                    {{ ucfirst($job['status']) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No job data available for the selected period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance -->
    <div class="report-card" id="departmentCard">
        <div class="report-header">
            <i class="fas fa-building me-2"></i>Department Performance Breakdown
        </div>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Open Positions</th>
                        <th>Total Applications</th>
                        <th>Applications/Position</th>
                        <th>Avg. Time to Hire</th>
                        <th>Hire Rate</th>
                        <th>Budget Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departmentStats as $dept)
                    <tr>
                        <td class="fw-medium">{{ $dept['name'] }}</td>
                        <td>{{ $dept['open_positions'] }}</td>
                        <td>{{ $dept['total_applications'] }}</td>
                        <td>{{ $dept['applications_per_position'] }}</td>
                        <td>{{ $dept['avg_time_to_hire'] }}</td>
                        <td><span class="text-success">{{ $dept['hire_rate'] }}%</span></td>
                        <td>
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: {{ $dept['budget_utilization'] }}%"></div>
                            </div>
                            <small class="text-muted">{{ $dept['budget_utilization'] }}%</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No department data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Application Sources & Trends -->
    <div class="row">
        <div class="col-lg-8">
            <div class="report-card">
                <div class="report-header">
                    <i class="fas fa-chart-line me-2"></i>Application Trends (Last 6 Months)
                </div>
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="report-card">
                <div class="report-header">
                    <i class="fas fa-share-alt me-2"></i>Application Sources
                </div>
                <div class="p-4">
                    @if(isset($sourcesData) && count($sourcesData) > 0)
                        @foreach($sourcesData as $source)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ $source['name'] }}</span>
                                <span class="text-muted">{{ $source['count'] }}</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: {{ $source['percentage'] }}%"></div>
                            </div>
                            <small class="text-muted">{{ $source['percentage'] }}% of total</small>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No source data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- AI Recommendations -->
    <div class="report-card">
        <div class="report-header">
            <i class="fas fa-lightbulb me-2"></i>AI-Powered Recommendations
        </div>
        <div class="p-4">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>What's Working Well</h6>
                    <ul class="list-unstyled">
                        @if($departmentStats->isNotEmpty())
                            @php
                                $bestPerforming = $departmentStats->sortByDesc('hire_rate')->first();
                                $fastestFilling = $departmentStats->sortBy(function($dept) {
                                    return (int) str_replace(' days', '', $dept['avg_time_to_hire']);
                                })->first();
                            @endphp
                            <li class="mb-2">✅ {{ $bestPerforming['name'] }} department has the highest conversion rate ({{ $bestPerforming['hire_rate'] }}%)</li>
                            <li class="mb-2">✅ {{ $fastestFilling['name'] }} positions fill the fastest ({{ $fastestFilling['avg_time_to_hire'] }})</li>
                        @endif
                        <li class="mb-2">✅ Company website is your top application source</li>
                        <li class="mb-2">✅ Employee referrals show high-quality candidates</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Areas for Improvement</h6>
                    <ul class="list-unstyled">
                        @if($departmentStats->isNotEmpty())
                            @php
                                $slowestFilling = $departmentStats->sortByDesc(function($dept) {
                                    return (int) str_replace(' days', '', $dept['avg_time_to_hire']);
                                })->first();
                                $lowestConversion = $departmentStats->sortBy('hire_rate')->first();
                            @endphp
                            <li class="mb-2">⚠️ {{ $slowestFilling['name'] }} positions take too long to fill ({{ $slowestFilling['avg_time_to_hire'] }})</li>
                            <li class="mb-2">⚠️ {{ $lowestConversion['name'] }} department has low conversion ({{ $lowestConversion['hire_rate'] }}%)</li>
                        @endif
                        <li class="mb-2">⚠️ Consider increasing job board posting budget</li>
                        <li class="mb-2">⚠️ Some departments may need salary adjustments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Custom Date Range Modal -->
<div class="modal fade" id="customRangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customDateForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyCustomRange()">Apply Range</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Global variables
let trendsChart = null;

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeTrendsChart();
});

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    applyFilters();
});

// Auto-apply filters on select change
document.querySelectorAll('#filterForm select').forEach(select => {
    select.addEventListener('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    showLoading();
    
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`{{ route('reports.data') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateMetrics(data);
            updateFunnel(data.funnelData);
            updateTopJobs(data.topPerformingJobs);
            updateDepartmentStats(data.departmentStats);
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('Error loading data. Please try again.');
        });
}

function updateMetrics(data) {
    document.querySelector('.metric-number').textContent = data.totalApplications || 0;
    document.querySelectorAll('.metric-number')[2].textContent = (data.conversionRate || 0) + '%';
}

function updateFunnel(funnelData) {
    const funnelContainer = document.querySelector('#funnelCard .p-4');
    if (!funnelData || funnelData.length === 0) {
        funnelContainer.innerHTML = '<p class="text-muted text-center">No funnel data available for the selected period.</p>';
        return;
    }
    
    let html = '';
    funnelData.forEach(stage => {
        html += `
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-medium">${stage.stage}</span>
                    <span class="text-muted">${stage.count} (${stage.percentage}%)</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: ${stage.percentage}%"></div>
                </div>
            </div>
        `;
    });
    funnelContainer.innerHTML = html;
}

function updateTopJobs(topJobs) {
    const tbody = document.querySelector('#topJobsCard tbody');
    if (!topJobs || topJobs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No job data available for the selected period.</td></tr>';
        return;
    }
    
    let html = '';
    topJobs.forEach(job => {
        const statusClass = job.status === 'active' ? 'active' : (job.status === 'closed' ? 'closed' : 'pending');
        html += `
            <tr>
                <td>
                    <div class="fw-medium">${job.title}</div>
                    <small class="text-muted">${job.department}</small>
                </td>
                <td><span class="fw-bold text-primary">${job.applications_count}</span></td>
                <td><span class="text-success">${job.conversion_rate}%</span></td>
                <td>
                    <span class="status-indicator status-${statusClass}"></span>
                    ${job.status.charAt(0).toUpperCase() + job.status.slice(1)}
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function updateDepartmentStats(deptStats) {
    const tbody = document.querySelector('#departmentCard tbody');
    if (!deptStats || deptStats.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No department data available.</td></tr>';
        return;
    }
    
    let html = '';
    deptStats.forEach(dept => {
        html += `
            <tr>
                <td class="fw-medium">${dept.name}</td>
                <td>${dept.open_positions}</td>
                <td>${dept.total_applications}</td>
                <td>${dept.applications_per_position}</td>
                <td>${dept.avg_time_to_hire}</td>
                <td><span class="text-success">${dept.hire_rate}%</span></td>
                <td>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: ${dept.budget_utilization}%"></div>
                    </div>
                    <small class="text-muted">${dept.budget_utilization}%</small>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart');
    if (!ctx) return;
    
    const trendsData = @json($applicationTrends ?? []);
    
    if (trendsData.length === 0) {
        ctx.parentElement.innerHTML = '<p class="text-muted text-center">No trend data available.</p>';
        return;
    }
    
    trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => item.month),
            datasets: [{
                label: 'Applications',
                data: trendsData.map(item => item.applications),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Hires',
                data: trendsData.map(item => item.hires),
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

function applyCustomRange() {
    const form = document.getElementById('customDateForm');
    const formData = new FormData(form);
    
    if (!formData.get('start_date') || !formData.get('end_date')) {
        alert('Please select both start and end dates.');
        return;
    }
    
    // Add custom date range to main filter form
    const filterForm = document.getElementById('filterForm');
    const startInput = document.createElement('input');
    startInput.type = 'hidden';
    startInput.name = 'start_date';
    startInput.value = formData.get('start_date');
    
    const endInput = document.createElement('input');
    endInput.type = 'hidden';
    endInput.name = 'end_date';
    endInput.value = formData.get('end_date');
    
    filterForm.appendChild(startInput);
    filterForm.appendChild(endInput);
    
    // Set date range selector to custom
    document.getElementById('dateRange').value = 'custom';
    
    // Close modal and apply filters
    const modal = bootstrap.Modal.getInstance(document.getElementById('customRangeModal'));
    modal.hide();
    
    applyFilters();
}

function exportPdf() {
    showLoading();
    fetch('{{ route('reports.export.pdf') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'recruitment-report.pdf';
        a.click();
        hideLoading();
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('Export failed. Please try again.');
    });
}

function exportCsv() {
    showLoading();
    fetch('{{ route('reports.export.csv') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'recruitment-report.csv';
        a.click();
        hideLoading();
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('Export failed. Please try again.');
    });
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}
</script>
@endsection