@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h4 class="page-title">
                <i class="bi bi-calendar-check me-2"></i>Interview Management
            </h4>
            <p class="text-muted">Manage and track all scheduled interviews</p>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <h2 class="card-title">{{ $interviews->count() }}</h2>
                        <p class="card-category">Total Interviews</p>
                    </div>
                </div>
            </div>
        </div>

        @if($interviews->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Interviews Scheduled</h5>
                    <p>There are currently no interviews in the system.</p>
                </div>
            </div>
        @else
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold mb-2">Filter by Date:</label>
                            <div class="btn-group" role="group" aria-label="Date filter">
                                <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">All Dates</button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-filter="upcoming">Upcoming</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="past">Past</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="jobTitleFilter" class="form-label fw-bold mb-2">Filter by Job Title:</label>
                            <select id="jobTitleFilter" class="form-select">
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
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table id="interviewsTable" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th><i class="bi bi-person me-2"></i>Applicant</th>
                                <th><i class="bi bi-briefcase me-2"></i>Job Title</th>
                                <th><i class="bi bi-calendar-event me-2"></i>Interview Date & Time</th>
                                <th><i class="bi bi-info-circle me-2"></i>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($interviews as $interview)
                                @php
                                    $interviewDate = \Carbon\Carbon::parse($interview->interview_date);
                                    $isUpcoming = $interviewDate->isFuture();
                                    $applicantName = $interview->jobApplication->user->name ?? 'N/A';
                                    $jobTitle = $interview->jobApplication->jobRequisition->title ?? 'N/A';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex justify-content-center align-items-center">
                                                {{ strtoupper(substr($applicantName, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $applicantName }}</div>
                                                @if(isset($interview->jobApplication->user->email))
                                                    <small class="text-muted">{{ $interview->jobApplication->user->email }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $jobTitle }}</span>
                                    </td>
                                    <td data-date="{{ $interviewDate->toDateTimeString() }}">
                                        <div>
                                            <div class="fw-semibold">{{ $interviewDate->format('M d, Y') }}</div>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ $interviewDate->format('h:i A') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $isUpcoming ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $isUpcoming ? 'Upcoming' : 'Past' }}
                                        </span>
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
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(function() {
            var table = $('#interviewsTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[2, 'desc']],
                columnDefs: [{ orderable: false, targets: [3] }],
                language: {
                    search: "Search interviews:",
                    lengthMenu: "Show _MENU_ interviews per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ interviews",
                    emptyTable: "No interviews found",
                    zeroRecords: "No matching interviews found"
                }
            });

            var dateFilter = 'all';

            function applyFilters() {
                var now = new Date();
                var selectedJobTitle = $('#jobTitleFilter').val();

                $.fn.dataTable.ext.search = [];

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = $('#interviewsTable tbody tr').eq(dataIndex);
                    var interviewDateStr = row.find('td').eq(2).data('date');

                    if (!interviewDateStr) return false;

                    var interviewDate = new Date(interviewDateStr);
                    var jobTitleHtml = data[1];
                    var jobTitleText = $('<div>').html(jobTitleHtml).text().trim();

                    var datePass = true;
                    if (dateFilter === 'upcoming') {
                        datePass = interviewDate >= now;
                    } else if (dateFilter === 'past') {
                        datePass = interviewDate < now;
                    }

                    var jobPass = true;
                    if (selectedJobTitle && selectedJobTitle.trim() !== '') {
                        jobPass = jobTitleText === selectedJobTitle.trim();
                    }

                    return datePass && jobPass;
                });

                table.draw();
            }

            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                dateFilter = $(this).data('filter');
                applyFilters();
            });

            $('#jobTitleFilter').on('change', function() {
                applyFilters();
            });

            applyFilters();
        });
    </script>
@endsection
