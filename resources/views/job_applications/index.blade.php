@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
@endsection
@section('title',  'Job Applications')

@section('content')
<div class="container-fluid">

    <div class="page-inner">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Job Applications Management</h1>
            <button class="btn btn-outline-primary" id="exportAllBtn">
                <i class="bi bi-download"></i> Export All
            </button>
        </div>
        @include('partials.alerts')


        <!-- Overall Statistics -->
        @if(auth()->user()->isHrAdmin() || auth()->user()->isManager())

        @if(!$jobRequisitions->isEmpty())
            <div class="card card-stats card-round mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="numbers">
                                <p class="card-category">Active Jobs</p>
                                <h4 class="card-title">{{ $jobRequisitions->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="numbers">
                                <p class="card-category">Total Applications</p>
                                <h4 class="card-title">{{ $applications->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="numbers">
                                <p class="card-category">Shortlisted</p>
                                <h4 class="card-title">{{ $applications->where('score', '>=', 70)->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="numbers">
                                <p class="card-category">Hired</p>
                                <h4 class="card-title">{{ $applications->where('status', 'hired')->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="numbers">
                                <p class="card-category">Rejected</p>
                                <h4 class="card-title">{{ $applications->where('status', 'rejected')->count() }}</h4>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        @endif
        @endif

        <!-- Jobs List/Pagination -->
        @if($jobRequisitions->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>No Job Posts Available</h4>
                    <p class="text-muted">There are no job posts published yet.</p>
                </div>
            </div>
        @else
            <!-- Search and Filter for Jobs -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="jobSearch" placeholder="Search jobs by title or department...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="departmentFilter">
                                <option value="">All Departments</option>
                                @foreach($jobRequisitions->groupBy('department.name') as $dept => $jobs)
                                    <option value="{{ $dept }}">{{ $dept ?? 'General' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary" id="clearJobFilters">Clear Filters</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion" id="applicationsAccordion">
                @foreach($jobRequisitions as $req)
                    @php
                        $group = $applications->where('job_requisition_id', $req->id);
                        $shortlistedCount = $group->where('status','shortlisted')->count();
                        $hiredCount = $group->where('status', 'hired')->count();
                        $rejectedCount = $group->where('status', 'rejected')->count();
                    @endphp

                    <div class="accordion-item job-item" data-title="{{ strtolower($req->title) }}" data-department="{{ strtolower($req->department->name ?? 'general') }}">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $req->id }}">
                                <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                    <div>
                                        <strong>{{ $req->title }}</strong>
                                        <small class="text-muted d-block">{{ $req->department->name ?? 'General' }} • {{ $req->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if(auth()->user()->isHrAdmin() || auth()->user()->isManager())

                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge badge-primary">{{ $group->count() }} Total</span>
                                        @if($shortlistedCount > 0)
                                            <span class="badge badge-success">{{ $shortlistedCount }} Shortlisted</span>
                                        @endif
                                      
                                        @if($hiredCount > 0)
                                            <span class="badge badge-info">{{ $hiredCount }} Hired</span>
                                        @endif
                                        @if($rejectedCount > 0)
                                            <span class="badge badge-danger">{{ $rejectedCount }} Rejected</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-{{ $req->id }}" class="accordion-collapse collapse" data-bs-parent="#applicationsAccordion">
                            <div class="accordion-body">
                                @if($group->isEmpty())
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                        <h5>No Applications Yet</h5>
                                        <p class="text-muted">This job posting hasn't received any applications.</p>
                                    </div>
                                @else
                                    <!-- Application Filters -->
                                    <div class="card mb-3">
                                        <div class="card-body py-2">
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm filter-status" data-job-id="{{ $req->id }}">
                                                        <option value="">All Statuses</option>
                                                        <option value="submitted">Submitted</option>
                                                        <option value="shortlisted">Shortlisted</option>
                                                        <option value="hired">Hired</option>
                                                        <option value="rejected">Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm filter-score" data-job-id="{{ $req->id }}">
                                                        <option value="">All Scores</option>
                                                        <option value="70-100">70+ (Shortlisted)</option>
                                                        <option value="50-69">50-69 (Average)</option>
                                                        <option value="0-49">Below 50</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-secondary reset-filters" data-job-id="{{ $req->id }}">
                                                            Reset Filters
                                                        </button>
                                                        @unless(@auth()->user()->isApplicant())
                                                        <button class="btn btn-success export-btn" data-job-id="{{ $req->id }}">
                                                            <i class="fas fa-download"></i> Export
                                                        </button>
                                                        @endunless
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bulk Actions -->
                                                            <!-- Bulk Actions -->
                                <div class="alert alert-warning bulk-actions" data-job-id="{{ $req->id }}" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="bulk-count fw-semibold">0 selected</span>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success bulk-action" data-action="shortlist" data-job-id="{{ $req->id }}">
                                                <i class="fas fa-check-circle me-1"></i> Shortlist
                                            </button>
                                            <button class="btn btn-danger bulk-action" data-action="reject" data-job-id="{{ $req->id }}">
                                                <i class="fas fa-times-circle me-1"></i> Reject
                                            </button>
                                            <button class="btn btn-primary bulk-action" data-action="offer_sent" data-job-id="{{ $req->id }}">
                                                <i class="fas fa-envelope me-1"></i> Offer Sent
                                            </button>
                                            <button class="btn btn-dark bulk-action" data-action="hired" data-job-id="{{ $req->id }}">
                                                <i class="fas fa-user-check me-1"></i> Mark as Hired
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                    <!-- Applications Table -->
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <table id="table-{{ $req->id }}" class="table table-striped table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="40">
                                                            <input type="checkbox" class="form-check-input select-all" data-job-id="{{ $req->id }}">
                                                        </th>
                                                        @if(auth()->user()->isHrAdmin())
                                                            <th>Applicant</th>
                                                        @endif
                                                        <th>Status</th>
                                                        @if(auth()->user()->isHrAdmin())
                                                        <th>Application Score</th>
                                                        @endif
                                                        <th>Date</th>
                                                        @if(auth()->user()->isHrAdmin())
                                                        <th>Interview Score</th>
                                                        @endif

                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($group as $app)
                                                
                                                        <tr data-job-id="{{ $req->id }}" data-status="{{ strtolower($app->status) }}" data-score="{{ $app->score->total_score ?? 0 }}">
                                                            <td>
                                                                <input type="checkbox" class="form-check-input row-select" value="{{ $app->id }}" data-job-id="{{ $req->id }}">
                                                            </td>
                                                            @if(auth()->user()->isHrAdmin())
                                                                <td>
                                                                    <div>
                                                                        <strong>{{ $app->user->name }}</strong>
                                                                        <small class="text-muted d-block">{{ $app->user->email }}</small>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td>
                                                                @switch(strtolower($app->status))
                                                                    @case('submitted') <span class="badge badge-info">Submitted</span> @break
                                                                    @case('shortlisted') <span class="badge badge-warning">Shortlisted</span> @break
                                                                    @case('hired') <span class="badge badge-success">Hired</span> @break
                                                                    @case('rejected') <span class="badge badge-danger">Rejected</span> @break
                                                                    @default <span class="badge badge-secondary">{{ ucfirst($app->status) }}</span>
                                                                @endswitch
                                                            </td>
                                                            @if(auth()->user()->isHrAdmin())
                                                            <td>
                                                          
                                                                @if($app->score && $app->score->total_score !== null)
                                                                
                                                                    <span class="fw-bold">{{ number_format($app->score->total_score, 2) }}/100</span>
                                                                    @if($app->score->total_score >= 70)
                                                                        <span class="badge badge-success ms-1">Auto-Shortlisted</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">Not scored</span>
                                                                @endif
                                                            </td>
                                                            
                                                            @endif
                                                            <td>{{ $app->created_at->format('M j, Y') }}</td>
                                                            @if(auth()->user()->isHrAdmin())
                                                            <td>
                                                                @if($app->interviews && $app->interviews->averageScore() !== null)
                                                                    <span class="fw-bold">{{ $app->interviews->averageScore() }}/5</span>
                                                                @else
                                                                    <span class="text-muted">Not scored</span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                            
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('job-applications.show', $app->uuid) }}" class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    @if(auth()->user()->isHrAdmin())
                                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                                            <i class="fas fa-ellipsis-v"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $app->id }}, 'shortlisted')">
                                                                                <i class="fas fa-check text-success"></i> Shortlist</a></li>
                                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $app->id }}, 'rejected')">
                                                                                <i class="fas fa-times text-danger"></i> Reject</a></li>
                                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $app->id }}, 'hired')">
                                                                                <i class="fas fa-user-check text-info"></i> Mark as Hired</a></li>
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    const dataTables = {};
    const initializedTables = new Set();

    function initDataTable(jobId) {
        // Prevent multiple initialization
        if (initializedTables.has(jobId)) {
            return;
        }

        const table = $(`#table-${jobId}`);
        if (table.length === 0) return;

        // Check if DataTable is already initialized
        if ($.fn.dataTable.isDataTable(table[0])) {
            dataTables[jobId] = table.DataTable();
            return;
        }

        try {
            dataTables[jobId] = table.DataTable({
                pageLength: 10,
                responsive: true,
                order: [[4, 'desc']], // Sort by date column
                columnDefs: [
                    { orderable: false, targets: [0, -1] },
                    { className: 'text-center', targets: [0] }
                ],
                language: {
                    search: "Search applications:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ applications",
                    emptyTable: "No applications found",
                    zeroRecords: "No matching applications found"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
            
            initializedTables.add(jobId);
        } catch (error) {
            console.error('Error initializing DataTable for job', jobId, error);
        }
    }

    function destroyDataTable(jobId) {
        if (dataTables[jobId] && $.fn.dataTable.isDataTable(`#table-${jobId}`)) {
            dataTables[jobId].destroy();
            delete dataTables[jobId];
            initializedTables.delete(jobId);
        }
    }

    function updateBulkActions(jobId) {
        const selected = $(`.row-select[data-job-id="${jobId}"]:checked`).length;
        const bulkActions = $(`.bulk-actions[data-job-id="${jobId}"]`);

        if (selected > 0) {
            bulkActions.show();
            bulkActions.find('.bulk-count').text(`${selected} selected`);
        } else {
            bulkActions.hide();
        }
    }

    function applyFilters(jobId) {
        if (!dataTables[jobId]) return;

        const statusFilter = $(`.filter-status[data-job-id="${jobId}"]`).val();
        const scoreFilter = $(`.filter-score[data-job-id="${jobId}"]`).val();

        // Clear existing search
        dataTables[jobId].search('').columns().search('').draw();

        // Apply status filter
        if (statusFilter) {
            const statusColumnIndex = @if(auth()->user()->isHrAdmin()) 2 @else 1 @endif;
            dataTables[jobId].column(statusColumnIndex).search(statusFilter, true, false);
        }

        // Apply score filter using custom search
        if (scoreFilter) {
            const [min, max] = scoreFilter.split('-').map(Number);
            
            dataTables[jobId].columns().every(function() {
                const column = this;
                if (column.index() === (@if(auth()->user()->isHrAdmin()) 3 @else 2 @endif)) { // Score column
                    column.search('', true, false);
                }
            });

            // Custom search function for score range
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== `table-${jobId}`) return true;
                
                const row = $(settings.nTable).find('tbody tr').eq(dataIndex);
                const score = parseInt(row.attr('data-score')) || 0;
                
                return score >= min && score <= max;
            });
        }

        dataTables[jobId].draw();

        // Clean up custom search function
        if (scoreFilter) {
            setTimeout(() => {
                const index = $.fn.dataTable.ext.search.length - 1;
                if (index >= 0) {
                    $.fn.dataTable.ext.search.splice(index, 1);
                }
            }, 100);
        }
    }

    // Job search functionality
    $('#jobSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterJobs();
    });

    $('#departmentFilter').on('change', function() {
        filterJobs();
    });

    $('#clearJobFilters').on('click', function() {
        $('#jobSearch').val('');
        $('#departmentFilter').val('');
        filterJobs();
    });

    function filterJobs() {
        const searchTerm = $('#jobSearch').val().toLowerCase();
        const department = $('#departmentFilter').val().toLowerCase();

        $('.job-item').each(function() {
            const title = $(this).data('title');
            const dept = $(this).data('department');
            
            const matchesSearch = !searchTerm || title.includes(searchTerm);
            const matchesDept = !department || dept === department;
            
            if (matchesSearch && matchesDept) {
                $(this).show();
            } else {
                $(this).hide();
                // Close accordion if it's open
                $(this).find('.accordion-collapse').removeClass('show');
            }
        });
    }

    // Initialize DataTables when accordion opens
    $(document).on('shown.bs.collapse', '.accordion-collapse', function() {
        const jobId = $(this).attr('id').replace('collapse-', '');
        setTimeout(() => initDataTable(jobId), 100);
    });

    // Clean up when accordion closes
    $(document).on('hidden.bs.collapse', '.accordion-collapse', function() {
        const jobId = $(this).attr('id').replace('collapse-', '');
        // Don't destroy immediately, just mark as ready for cleanup
        setTimeout(() => {
            if (!$(this).hasClass('show')) {
                destroyDataTable(jobId);
            }
        }, 1000);
    });

    // Select all functionality
    $(document).on('change', '.select-all', function() {
        const jobId = $(this).data('job-id');
        const checked = $(this).is(':checked');
        
        if (dataTables[jobId]) {
            // Handle both visible and filtered rows
            dataTables[jobId].$('.row-select').prop('checked', checked);
        } else {
            $(`.row-select[data-job-id="${jobId}"]`).prop('checked', checked);
        }
        
        updateBulkActions(jobId);
    });

    // Single row selection
    $(document).on('change', '.row-select', function() {
        const jobId = $(this).data('job-id');
        updateBulkActions(jobId);
        
        // Update select-all state
        const total = $(`.row-select[data-job-id="${jobId}"]`).length;
        const checked = $(`.row-select[data-job-id="${jobId}"]:checked`).length;
        $(`.select-all[data-job-id="${jobId}"]`).prop('checked', total === checked);
    });

    // Filter handlers
    $(document).on('change', '.filter-status, .filter-score', function() {
        const jobId = $(this).data('job-id');
        applyFilters(jobId);
    });

    // Reset filters
    $(document).on('click', '.reset-filters', function() {
        const jobId = $(this).data('job-id');
        $(`.filter-status[data-job-id="${jobId}"], .filter-score[data-job-id="${jobId}"]`).val('');
        
        // Clear custom search functions
        $.fn.dataTable.ext.search = [];
        
        if (dataTables[jobId]) {
            dataTables[jobId].search('').columns().search('').draw();
        }
    });

    // Bulk actions
$(document).on('click', '.bulk-action', function () {
    const jobId = $(this).data('job-id');
    const action = $(this).data('action');
    const selected = $(`.row-select[data-job-id="${jobId}"]:checked`).map(function () {
        return this.value;
    }).get();

    if (selected.length === 0) {
        alert('Please select at least one application.');
        return;
    }

    if (confirm(`Are you sure you want to ${action.replace('_', ' ')} ${selected.length} application(s)?`)) {
        $.ajax({
            url: '/job-applications/bulk-action',
            method: 'POST',
            data: {
                action: action,
                applications: selected,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alert(response.message || 'Bulk action completed.');
                location.reload();
            },
            error: function (xhr) {
                const errMsg = xhr.responseJSON?.message || 'Error processing bulk action. Please try again.';
                alert(errMsg);
            }
        });
    }
});


    // Export functionality
    $(document).on('click', '.export-btn', function() {
        const jobId = $(this).data('job-id');
        window.location.href = `/job-applications/export/${jobId}`;
    });

    $('#exportAllBtn').on('click', function() {
        window.location.href = '/job-applications/export-all';
    });
});

// Status update function
function updateStatus(applicationId, action) {
    const actionLabels = {
        'shortlist': 'shortlist',
        'reject': 'reject',
        'offer_sent': 'mark as offer sent',
        'hired': 'mark as hired'
    };
    
    const label = actionLabels[action] || action;
    
    if (confirm(`Are you sure you want to ${label} this application?`)) {
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        $.ajax({
            url: `/job-applications/${applicationId}/quick-action`,
            method: 'POST',
            data: {
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showToast('success', response.message);
                    
                    // Update the status badge in the UI
                    updateStatusBadge(applicationId, response.new_status, response.status_label);
                    
                    // Optionally reload the page after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', response.message || 'Failed to update status');
                    // Restore button state
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error updating status. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to perform this action.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Application not found.';
                }
                
                showToast('error', errorMessage);
                
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }
}

// Function to update status badge in the UI without full page reload
function updateStatusBadge(applicationId, status, statusLabel) {
    const row = $(`input[value="${applicationId}"]`).closest('tr');
    const statusCell = row.find('td').eq(2); // Assuming status is in the 3rd column
    
    // Status badge classes
    const badgeClasses = {
        'submitted': 'badge-info',
        'shortlisted': 'badge-warning', 
        'rejected': 'badge-danger',
        'offer_sent': 'badge-primary',
        'hired': 'badge-success'
    };
    
    const badgeClass = badgeClasses[status] || 'badge-secondary';
    statusCell.html(`<span class="badge ${badgeClass}">${statusLabel}</span>`);
}

// Improved bulk actions with better feedback
$(document).on('click', '.bulk-action', function() {
    const jobId = $(this).data('job-id');
    const action = $(this).data('action');
    const selected = $(`.row-select[data-job-id="${jobId}"]:checked`).map(function() {
        return this.value;
    }).get();

    if (selected.length === 0) {
        showToast('warning', 'Please select at least one application.');
        return;
    }

    const actionLabels = {
        'shortlist': 'shortlist',
        'reject': 'reject',
        'offer_sent': 'mark as offer sent',
        'hired': 'mark as hired'
    };
    
    const actionLabel = actionLabels[action] || action;

    if (confirm(`Are you sure you want to ${actionLabel} ${selected.length} application(s)?`)) {
        // Show loading state
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        button.prop('disabled', true);
        
        $.ajax({
            url: '/job-applications/bulk-action',
            method: 'POST',
            data: {
                action: action,
                applications: selected,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    
                    // Clear selections
                    $(`.row-select[data-job-id="${jobId}"]`).prop('checked', false);
                    $(`.select-all[data-job-id="${jobId}"]`).prop('checked', false);
                    updateBulkActions(jobId);
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', response.message || 'Failed to process bulk action');
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error processing bulk action. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to perform this action.';
                }
                
                showToast('error', errorMessage);
                button.html(originalText);
                button.prop('disabled', false);
            }
        });
    }
});

// Toast notification function
function showToast(type, message) {
    // Remove existing toasts
    $('.toast-notification').remove();
    
    const toastClass = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle', 
        'info': 'fa-info-circle'
    };
    
    const toast = $(`
        <div class="toast-notification alert ${toastClass[type]} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas ${iconClass[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}
// Enhanced export functionality with loading states and error handling

$(document).ready(function() {
    // Individual job export
    $(document).on('click', '.export-btn', function() {
        const jobId = $(this).data('job-id');
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Exporting...');
        button.prop('disabled', true);
        
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.href = `/job-applications/export/${jobId}`;
        link.download = ''; // Let the server determine filename
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button state after a delay
        setTimeout(() => {
            button.html(originalText);
            button.prop('disabled', false);
            showToast('success', 'Export started! Your download should begin shortly.');
        }, 1000);
        
        // Handle potential errors (though this is tricky with direct downloads)
        setTimeout(() => {
            button.html(originalText);
            button.prop('disabled', false);
        }, 10000); // Reset after 10 seconds regardless
    });

    // Export all applications
    $('#exportAllBtn').on('click', function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Exporting All...');
        button.prop('disabled', true);
        
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.href = '/job-applications/export-all';
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button state
        setTimeout(() => {
            button.html(originalText);
            button.prop('disabled', false);
            showToast('success', 'Export started! Your download should begin shortly.');
        }, 1000);
        
        // Safety reset
        setTimeout(() => {
            button.html(originalText);
            button.prop('disabled', false);
        }, 15000); // Longer timeout for all applications
    });

    // Alternative AJAX-based export with better error handling
    function exportWithAjax(url, buttonElement, successMessage) {
        const button = $(buttonElement);
        const originalText = button.html();
        
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Preparing Export...');
        button.prop('disabled', true);
        
        // Use AJAX to check if the export is ready, then download
        $.ajax({
            url: url,
            method: 'GET',
            xhrFields: {
                responseType: 'blob' // Important for handling binary data
            },
            success: function(data, status, xhr) {
                // Create blob link to download
                const blob = new Blob([data], {
                    type: xhr.getResponseHeader('Content-Type')
                });
                
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                
                // Get filename from Content-Disposition header
                const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                let filename = 'export.xlsx';
                if (contentDisposition) {
                    const filenameMatch = contentDisposition.match(/filename="([^"]+)"/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }
                
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(link.href);
                
                showToast('success', successMessage);
            },
            error: function(xhr) {
                let errorMessage = 'Export failed. Please try again.';
                
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to export this data.';
                } else if (xhr.status === 404) {
                    errorMessage = 'No data found to export.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred during export. Please try again later.';
                }
                
                showToast('error', errorMessage);
            },
            complete: function() {
                // Always reset button state
                button.html(originalText);
                button.prop('disabled', false);
            }
        });
    }

    // Uncomment these if you prefer AJAX-based exports:
    /*
    $(document).on('click', '.export-btn', function() {
        const jobId = $(this).data('job-id');
        exportWithAjax(
            `/job-applications/export/${jobId}`, 
            this, 
            'Job applications exported successfully!'
        );
    });

    $('#exportAllBtn').on('click', function() {
        exportWithAjax(
            '/job-applications/export-all', 
            this, 
            'All applications exported successfully!'
        );
    });
    */
});

// Enhanced toast function with better styling
function showToast(type, message, duration = 5000) {
    // Remove existing toasts
    $('.toast-notification').remove();
    
    const toastClass = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle', 
        'info': 'fa-info-circle'
    };
    
    const toast = $(`
        <div class="toast-notification alert ${toastClass[type]} alert-dismissible fade show position-fixed shadow-lg" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;">
            <i class="fas ${iconClass[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Auto remove after specified duration
    setTimeout(() => {
        toast.fadeOut(500, function() {
            $(this).remove();
        });
    }, duration);
}

// Progress indicator for large exports
function showProgressModal(title = 'Exporting Data') {
    const modal = $(`
        <div class="modal fade" id="exportProgressModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>${title}</h5>
                        <p class="text-muted mb-0">Please wait while we prepare your file...</p>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(modal);
    const modalInstance = new bootstrap.Modal(document.getElementById('exportProgressModal'), {
        backdrop: 'static',
        keyboard: false
    });
    modalInstance.show();
    
    return {
        hide: function() {
            modalInstance.hide();
            setTimeout(() => {
                $('#exportProgressModal').remove();
            }, 300);
        }
    };
}
</script>
@endsection