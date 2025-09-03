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
                                                        <option value="review">Under Review</option>
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
                                                                        <strong>{{ $app->user->profile->first_name }} {{ $app->user->profile->last_name }}</strong>
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
                                                                    @case('interview scheduled') <span class="badge badge-warning">Interview Scheduled</span> @break
                                                                    @case('review') <span class="badge badge-warning">Under Review</span> @break

                                                                    @default <span class="badge badge-secondary">{{ ucfirst($app->status) }}</span>
                                                                @endswitch
                                                            </td>
                                                            @if(auth()->user()->isHrAdmin())
                                                            <td>
                                                          
                                                                @if($app->score && $app->score->total_score !== null)
                                                                
                                                                    <span class="fw-bold">{{ number_format($app->score->total_score, 2) }}</span>
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
    const exportStates = new Map();

    // Initialize DataTable
    function initDataTable(jobId) {
        if (initializedTables.has(jobId)) return;

        const table = $(`#table-${jobId}`);
        if (!table.length) return;

        if ($.fn.dataTable.isDataTable(table[0])) {
            dataTables[jobId] = table.DataTable();
            return;
        }

        try {
            dataTables[jobId] = table.DataTable({
                pageLength: 10,
                responsive: true,
                order: [[4, 'desc']],
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
    const table = dataTables[jobId];
    if (!table) return;

    const statusFilter = $(`.filter-status[data-job-id="${jobId}"]`).val();
    const scoreFilter = $(`.filter-score[data-job-id="${jobId}"]`).val();

    // Reset previous column searches
    table.columns().search('');

    // Status filter (applies only to applications of this job)
    if (statusFilter) {
        const statusCol = 2; // adjust to the column index of status in applications table
        table.column(statusCol).search(statusFilter, true, false);
    }

    // Score filter
    if (scoreFilter) {
        const [min, max] = scoreFilter.split('-').map(Number);

        // Use column index instead of data-score attribute
        const scoreCol = 3; // <-- set to the column index of the score (0-based)

        // Custom search function
        const filterFn = function(settings, data, dataIndex) {
            if (settings.nTable.id !== `table-${jobId}`) return true; // only filter this table
            const score = parseInt(data[scoreCol]) || 0;
            return score >= min && score <= max;
        };
        filterFn._jobId = jobId;

        // Remove any previous filter for this table first
        $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => !fn._jobId || fn._jobId !== jobId);
        $.fn.dataTable.ext.search.push(filterFn);
    }

    table.draw();
}


    // Job search & filter
    function filterJobs() {
        const searchTerm = $('#jobSearch').val().toLowerCase();
        const department = $('#departmentFilter').val().toLowerCase();

        $('.job-item').each(function() {
            const title = $(this).data('title').toLowerCase();
            const dept = $(this).data('department').toLowerCase();
            const visible = (!searchTerm || title.includes(searchTerm)) &&
                            (!department || dept === department);
            $(this).toggle(visible);
            if (!visible) $(this).find('.accordion-collapse').removeClass('show');
        });
    }

    $('#jobSearch').on('keyup', filterJobs);
    $('#departmentFilter').on('change', filterJobs);
    $('#clearJobFilters').on('click', function() {
        $('#jobSearch, #departmentFilter').val('');
        filterJobs();
    });

    // Accordion events
    $(document).on('shown.bs.collapse', '.accordion-collapse', function() {
        const jobId = $(this).attr('id').replace('collapse-', '');
        setTimeout(() => initDataTable(jobId), 100);
    });
    
    $(document).on('hidden.bs.collapse', '.accordion-collapse', function() {
        const jobId = $(this).attr('id').replace('collapse-', '');
        setTimeout(() => {
            if (!$(this).hasClass('show')) destroyDataTable(jobId);
        }, 500);
    });

    // Selection & bulk actions
    $(document).on('change', '.select-all', function() {
        const jobId = $(this).data('job-id');
        const checked = $(this).is(':checked');
        $(`.row-select[data-job-id="${jobId}"]`).prop('checked', checked);
        updateBulkActions(jobId);
    });
    
    $(document).on('change', '.row-select', function() {
        const jobId = $(this).data('job-id');
        updateBulkActions(jobId);
        const total = $(`.row-select[data-job-id="${jobId}"]`).length;
        const checked = $(`.row-select[data-job-id="${jobId}"]:checked`).length;
        $(`.select-all[data-job-id="${jobId}"]`).prop('checked', total === checked);
    });

    // Filter handlers
    $(document).on('change', '.filter-status, .filter-score', function() {
        const jobId = $(this).data('job-id');
        applyFilters(jobId);
    });
    
    $(document).on('click', '.reset-filters', function() {
        const jobId = $(this).data('job-id');
        $(`.filter-status[data-job-id="${jobId}"], .filter-score[data-job-id="${jobId}"]`).val('');
        if (dataTables[jobId]) dataTables[jobId].search('').columns().search('').draw();
    });

    // CSRF setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Quick Action Handler (Single Application)
    window.updateStatus = function(applicationId, action) {
        const actionLabels = {
            'shortlist': 'shortlist',
            'reject': 'reject', 
            'offer_sent': 'mark as offer sent',
            'hired': 'mark as hired'
        };
        
        const label = actionLabels[action] || action.replace('_', ' ');

        if (!confirm(`Are you sure you want to ${label} this application?`)) {
            return;
        }

        // Show loading state
        showToast('info', 'Updating application status...');

        $.ajax({
            url: `/job-applications/${applicationId}/quick-action`,
            method: 'POST',
            data: {
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    updateStatusBadgeInTable(response.application_id, response.new_status, response.status_label);
                    
                    // Update the row's data attribute
                    const checkbox = $(`input.row-select[value="${response.application_id}"]`);
                    const row = checkbox.closest('tr');
                    row.attr('data-status', response.new_status.toLowerCase().replace(' ', '_'));
                    
                    // Uncheck the checkbox after successful update
                    checkbox.prop('checked', false);
                    updateBulkActions(checkbox.data('job-id'));
                } else {
                    showToast('error', response.message || 'Failed to update status');
                }
            },
            error: function(xhr) {
                let message = 'Error updating status. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    message = 'You do not have permission to perform this action.';
                } else if (xhr.status === 404) {
                    message = 'Application not found.';
                } else if (xhr.status === 422) {
                    message = 'Invalid request data.';
                }
                
                showToast('error', message);
                console.error('Quick action error:', xhr);
            }
        });
    };

    // Bulk Action Handler
    $(document).on('click', '.bulk-action', function() {
        const action = $(this).data('action');
        const jobId = $(this).data('job-id');
        const selectedIds = $(`.row-select[data-job-id="${jobId}"]:checked`).map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showToast('warning', 'Please select at least one application.');
            return;
        }

        const actionLabels = {
            'shortlist': 'shortlist',
            'reject': 'reject',
            'offer_sent': 'mark as offer sent', 
            'hired': 'mark as hired'
        };
        
        const label = actionLabels[action] || action.replace('_', ' ');
        const count = selectedIds.length;

        if (!confirm(`Are you sure you want to ${label} ${count} selected application(s)?`)) {
            return;
        }

        // Disable bulk action buttons
        $(`.bulk-action[data-job-id="${jobId}"]`).prop('disabled', true);
        showToast('info', `Updating ${count} application(s)...`);

        $.ajax({
            url: '/job-applications/bulk-action',
            method: 'POST',
            data: {
                action: action,
                applications: selectedIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    
                    // Update each affected row
                    response.application_ids.forEach(function(appId) {
                        updateStatusBadgeInTable(appId, response.new_status, response.status_label);
                        
                        // Update row data attribute
                        const checkbox = $(`input.row-select[value="${appId}"]`);
                        const row = checkbox.closest('tr');
                        row.attr('data-status', response.new_status.toLowerCase().replace(' ', '_'));
                        
                        // Uncheck the checkbox
                        checkbox.prop('checked', false);
                    });
                    
                    // Update bulk actions and select-all checkbox
                    updateBulkActions(jobId);
                    $(`.select-all[data-job-id="${jobId}"]`).prop('checked', false);
                    
                } else {
                    showToast('error', response.message || 'Failed to update applications');
                }
            },
            error: function(xhr) {
                let message = 'Error updating applications. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    message = 'You do not have permission to perform this action.';
                } else if (xhr.status === 422) {
                    message = 'Invalid request data.';
                }
                
                showToast('error', message);
                console.error('Bulk action error:', xhr);
            },
            complete: function() {
                // Re-enable bulk action buttons
                $(`.bulk-action[data-job-id="${jobId}"]`).prop('disabled', false);
            }
        });
    });

    function updateStatusBadgeInTable(applicationId, status, statusLabel) {
        const checkbox = $(`input.row-select[value="${applicationId}"]`);
        const row = checkbox.closest('tr');
        
        if (!row.length) return;

        // Determine which column contains the status badge
        const isHrAdmin = $('body').data('user-role') === 'hr_admin';
        const statusColumnIndex = isHrAdmin ? 2 : 1;
        
        // Badge class mapping
        const badgeClassMap = {
            'submitted': 'badge-info',
            'shortlisted': 'badge-warning', 
            'rejected': 'badge-danger',
            'offer sent': 'badge-primary',
            'offer_sent': 'badge-primary',
            'hired': 'badge-success'
        };
        
        const badgeClass = badgeClassMap[status] || 'badge-secondary';
        
        // Update the status badge
        row.find('td').eq(statusColumnIndex).html(
            `<span class="badge ${badgeClass}">${statusLabel}</span>`
        );
        
        // Update row data attribute
        row.attr('data-status', status.toLowerCase().replace(' ', '_'));
    }

    // Toast notification function
    function showToast(type, message, duration = 5000) {
        // Remove any existing toasts
        $('.toast-notification').remove();
        
        const typeClasses = {
            'success': 'alert-success',
            'error': 'alert-danger', 
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const typeIcons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle', 
            'info': 'fa-info-circle'
        };
        
        const alertClass = typeClasses[type] || 'alert-info';
        const iconClass = typeIcons[type] || 'fa-info-circle';
        
        // Sanitize message
        const safeMessage = $('<div>').text(message).html();
        
        const toast = $(`
            <div class="toast-notification alert ${alertClass} alert-dismissible fade show position-fixed shadow-lg"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;">
                <i class="fas ${iconClass} me-2"></i>${safeMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        $('body').append(toast);
        
        // Auto-remove after duration
        setTimeout(function() {
            toast.fadeOut(500, function() {
                toast.remove();
            });
        }, duration);
    }

    // Export handlers
    function handleExport(jobId, all = false) {
        const btn = all ? $('#exportAllBtn') : $(`.export-btn[data-job-id="${jobId}"]`);
        const exportKey = all ? 'all' : jobId;
        
        if (exportStates.get(exportKey)) {
            showToast('warning', 'Export already in progress');
            return;
        }
        
        exportStates.set(exportKey, true);
        const originalText = btn.html();
        btn.html(`<i class="fas fa-spinner fa-spin"></i> Exporting...`).prop('disabled', true);
        
        try {
            const exportUrl = all ? '/job-applications/export-all' : `/job-applications/export/${jobId}`;
            window.location.href = exportUrl;
            
            setTimeout(() => {
                btn.html(originalText).prop('disabled', false);
                exportStates.delete(exportKey);
                showToast('success', 'Export started!');
            }, all ? 3000 : 2000);
            
        } catch (error) {
            console.error('Export error:', error);
            btn.html(originalText).prop('disabled', false);
            exportStates.delete(exportKey);
            showToast('error', 'Export failed. Please try again.');
        }
    }

    $(document).on('click', '.export-btn', function(e) {
        e.preventDefault();
        handleExport($(this).data('job-id'));
    });
    
    $('#exportAllBtn').on('click', function(e) {
        e.preventDefault(); 
        handleExport(null, true);
    });
});
    </script>
    
@endsection