@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">
                <i class="fas fa-calendar-check me-2"></i>Interview Management
            </h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">HR Management</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Interviews</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Total Interviews</p>
                                    <h4 class="card-title">{{ $interviews->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Upcoming</p>
                                    <h4 class="card-title">{{ $interviews->filter(function($interview) { return \Carbon\Carbon::parse($interview->interview_date)->isFuture(); })->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-warning bubble-shadow-small">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Today</p>
                                    <h4 class="card-title">{{ $interviews->filter(function($interview) { return \Carbon\Carbon::parse($interview->interview_date)->isToday(); })->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                    <i class="fas fa-history"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Completed</p>
                                    <h4 class="card-title">{{ $interviews->filter(function($interview) { return \Carbon\Carbon::parse($interview->interview_date)->isPast(); })->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($interviews->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-calendar-times" style="font-size: 4rem; color: #dee2e6;"></i>
                        </div>
                        <div class="empty-state-title mt-3">
                            <h5 class="text-muted">No Interviews Scheduled</h5>
                        </div>
                        <div class="empty-state-description">
                            <p class="text-muted">There are currently no interviews in the system.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Interview List</div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Filter by Date</label>
                            <div class="btn-group d-flex" role="group" aria-label="Date filter">
                                <input type="radio" class="btn-check" name="dateFilter" id="filterAll" value="all" checked>
                                <label class="btn btn-outline-primary" for="filterAll">All Dates</label>
                                
                                <input type="radio" class="btn-check" name="dateFilter" id="filterUpcoming" value="upcoming">
                                <label class="btn btn-outline-primary" for="filterUpcoming">Upcoming</label>
                                
                                <input type="radio" class="btn-check" name="dateFilter" id="filterPast" value="past">
                                <label class="btn btn-outline-primary" for="filterPast">Past</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jobTitleFilter" class="form-label fw-bold">Filter by Job Title</label>
                            <select id="jobTitleFilter" class="form-select form-control">
                                <option value="">All Job Titles</option>
                                @php
                                    $jobTitles = $interviews->pluck('jobApplication.jobRequisition.title')->unique()->filter()->sort()->values();
                                @endphp
                                @foreach($jobTitles as $title)
                                    <option value="{{ $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="interviewsTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Applicant</th>
                                    <th><i class="fas fa-briefcase me-2"></i>Job Title</th>
                                    <th><i class="fas fa-calendar-alt me-2"></i>Interview Date & Time</th>
                                    <th><i class="fas fa-info-circle me-2"></i>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interviews as $interview)
                                    @php
                                        $interviewDate = \Carbon\Carbon::parse($interview->interview_date);
                                        $isUpcoming = $interviewDate->isFuture();
                                        $isToday = $interviewDate->isToday();
                                        $applicantName = $interview->jobApplication->user->name ?? 'N/A';
                                        $jobTitle = $interview->jobApplication->jobRequisition->title ?? 'N/A';
                                        $applicantEmail = $interview->jobApplication->user->email ?? '';
                                    @endphp
                                    <tr data-date="{{ $interviewDate->toDateTimeString() }}" data-job-title="{{ $jobTitle }}">
                                        <td>
                                          
                                                <div class="user-info">
                                                    <div class="fw-bold">{{ $applicantName }}</div>
                                                    @if($applicantEmail)
                                                        <small class="text-muted">{{ $applicantEmail }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $jobTitle }}</span>
                                        </td>
                                        <td>
                                            <div class="interview-datetime">
                                                <div class="fw-bold">{{ $interviewDate->format('M d, Y') }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $interviewDate->format('h:i A') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($isToday)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-calendar-day me-1"></i>Today
                                                </span>
                                            @elseif($isUpcoming)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-clock me-1"></i>Upcoming
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-check me-1"></i>Completed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Check if DataTable is already initialized and destroy it
    if ($.fn.DataTable.isDataTable('#interviewsTable')) {
        $('#interviewsTable').DataTable().destroy();
    }

    // Initialize DataTable
    var table = $('#interviewsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: [3] }
        ],
        language: {
            search: "Search interviews:",
            lengthMenu: "Show _MENU_ interviews per page",
            info: "Showing _START_ to _END_ of _TOTAL_ interviews",
            emptyTable: "No interviews found",
            zeroRecords: "No matching interviews found"
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'csv',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'excel',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'pdf',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'print',
                className: 'btn btn-primary btn-sm'
            }
        ]
    });

    var dateFilter = 'all';

    // Custom filtering function
    function applyFilters() {
        var now = new Date();
        var selectedJobTitle = $('#jobTitleFilter').val();

        // Clear existing search functions
        $.fn.dataTable.ext.search = [];

        // Add custom search function
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = table.row(dataIndex).node();
            var $row = $(row);
            
            var interviewDateStr = $row.data('date');
            var jobTitle = $row.data('job-title');

            if (!interviewDateStr) return false;

            var interviewDate = new Date(interviewDateStr);

            // Date filter logic
            var datePass = true;
            if (dateFilter === 'upcoming') {
                datePass = interviewDate >= now;
            } else if (dateFilter === 'past') {
                datePass = interviewDate < now;
            }

            // Job title filter logic
            var jobPass = true;
            if (selectedJobTitle && selectedJobTitle.trim() !== '') {
                jobPass = jobTitle === selectedJobTitle.trim();
            }

            return datePass && jobPass;
        });

        table.draw();
    }

    // Date filter event handlers
    $('input[name="dateFilter"]').off('change').on('change', function() {
        dateFilter = $(this).val();
        applyFilters();
    });

    // Job title filter event handler
    $('#jobTitleFilter').off('change').on('change', function() {
        applyFilters();
    });

    // Apply initial filters
    applyFilters();

    // Add some custom styling for better UX
    $('.dataTables_wrapper .dataTables_filter input').addClass('form-control');
    $('.dataTables_wrapper .dataTables_length select').addClass('form-select');
});
</script>
@endsection