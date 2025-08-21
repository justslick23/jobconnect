@extends('layouts.app')

@section('styles')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        .dt-button.btn {
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
    </style>
@endsection
@section('title', 'Job Requisitions')

@section('content')
<div class="container">
    <div class="page-inner">

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h3">Job Requisitions</h1>
                    <div class="text-muted small">
                        <span class="me-3"><i class="fas fa-clipboard-list"></i> Manage and track job requisitions</span>
                        <span><i class="fas fa-chart-bar"></i> Total: {{ $requisitions->count() }} requisitions</span>
                    </div>
                </div>
                @if(auth()->user()->isManager() || auth()->user()->isHrAdmin())
                    <a href="{{ route('job-requisitions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Post
                    </a>
                @endif
            </div>

            @include('partials.alerts')

            <div>
                <span class="badge bg-info text-uppercase me-2">Available Jobs</span>
                <span class="badge bg-secondary text-uppercase">{{ $requisitions->where('approval_status', 'approved')->count() }} Approved</span>
                @if(auth()->user()->isApplicant())
                    @php
                        $userApplications = auth()->user()->applications()->pluck('job_requisition_id')->toArray();
                        $availableCount = $requisitions->where('approval_status', 'approved')
                                                    ->where('job_status', 'active')
                                                    ->whereNotIn('id', $userApplications)
                                                    ->count();
                    @endphp
                    <span class="badge bg-success text-uppercase">{{ $availableCount }} Available to Apply</span>
                @endif
            </div>
        </div>

        @php
            // For applicants, filter out jobs they've already applied for
            if (auth()->user()->isApplicant()) {
                $userApplications = auth()->user()->applications()->pluck('job_requisition_id')->toArray();
                $filteredRequisitions = $requisitions->whereNotIn('id', $userApplications);
            } else {
                $filteredRequisitions = $requisitions;
            }
        @endphp

        @if($filteredRequisitions->isEmpty())
            <div class="text-center p-5 text-muted">
                <i class="bi bi-inbox fs-1 opacity-50 mb-3"></i>
                @if(auth()->user()->isApplicant())
                    <h4>No Available Job Requisitions</h4>
                    <p>You have either applied to all available positions or there are no open positions at this time.</p>
                    <small class="text-muted">Check back later for new opportunities!</small>
                @else
                    <h4>No Job Requisitions Found</h4>
                    <p>There are no job requisitions to display.</p>
                @endif
            </div>
        @else
            {{-- Filters --}}
            <div class="row mb-3 g-3 align-items-end">
                <div class="col-md-4">
                    <select id="statusFilter" class="form-select">
                        <option value="">Filter by Status</option>
                        <option value="Active">Active</option>
                        <option value="Closed">Closed</option>
                        <option value="Draft">Draft</option>
                    </select>
                </div>
                @if(!auth()->user()->isApplicant())
                    <div class="col-md-4">
                        <select id="approvalFilter" class="form-select">
                            <option value="">Filter by Approval</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                @endif
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">Reset Filters</button>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table id="requisitionsTable" class="table table-hover align-middle nowrap w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Title</th>
                            <th>Status</th>
                            @if(!auth()->user()->isApplicant())
                                <th>Approval</th>
                            @endif
                            <th>Filled</th>
                            @unless (auth()->user()->isApplicant())

                            <th>Applications Count</th>
                            @endunless
                            <th>Created</th>
                            @if(!auth()->user()->isApplicant())
                                <th class="text-center">Actions</th>
                            @else
                                <th class="text-center">Apply</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredRequisitions as $req)
                            @php
                                $isFilled = $req->applications()->where('status', 'hired')->exists();
                            @endphp
                            <tr>
                                <td>{{ $req->reference_number }}</td>
                                <td>
                                    <a href="{{ route('job-requisitions.show', $req->uuid) }}" class="text-decoration-none">
                                        {{ $req->title }}
                                    </a>
                                </td>
                                <td>
                                    @switch(strtolower($req->job_status))
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-danger">Closed</span>
                                            @break
                                        @case('draft')
                                            <span class="badge bg-secondary">Draft</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ ucfirst($req->job_status ?? 'N/A') }}</span>
                                    @endswitch
                                </td>

                                @if(!auth()->user()->isApplicant())
                                    <td>
                                        @switch(strtolower($req->approval_status))
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ ucfirst($req->approval_status ?? 'Unknown') }}</span>
                                        @endswitch
                                    </td>
                                @endif

                                <td>
                                    @if($isFilled)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                @unless (auth()->user()->isApplicant())
                                    <td>{{ $req->applications->count() }}</td>  
                                    
                                @endunless
                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    @if(!auth()->user()->isApplicant())
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('job-requisitions.show', $req->slug_uuid) }}" 
                                            class="btn btn-sm btn-outline-primary rounded" title="View">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                
                                        <a href="{{ route('job-requisitions.edit', $req->id) }}" 
                                            class="btn btn-sm btn-outline-warning rounded" title="Edit">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                
                                        <a href="{{ route('job-applications.index', ['job_requisition_id' => $req->id]) }}" 
                                            class="btn btn-sm btn-outline-secondary rounded" title="View Applications">
                                            <i class="bi bi-people-fill me-1"></i> Applications
                                        </a>
                                    </div>
                                    @else
                                        {{-- Applicants --}}
                                        @if($req->approval_status === 'approved' && $req->job_status === 'active' && !$isFilled)
                                            <a href="{{ route('job-applications.create', ['job_requisition' => $req->id]) }}" 
                                            class="btn btn-sm btn-primary rounded" title="Apply Now">
                                                <i class="bi bi-send me-1"></i> Apply Now
                                            </a>

                               
                                        @else
                                            <span class="text-muted small">
                                                @if($isFilled)
                                                    Position Filled
                                                @elseif($req->approval_status !== 'approved')
                                                    Pending Approval
                                                @elseif($req->job_status !== 'active')
                                                    Position Closed
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(auth()->user()->isApplicant() && $requisitions->count() > $filteredRequisitions->count())
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Note:</strong> {{ $requisitions->count() - $filteredRequisitions->count() }} job(s) are hidden because you have already applied for them.
                    <a href="{{ route('job-applications.index') }}" class="alert-link ms-2">View your applications</a>
                </div>
            @endif
        @endif

    </div>
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

    <script>
        $(document).ready(function () {
            if (!$.fn.DataTable.isDataTable('#requisitionsTable')) {
                let buttons = [];

                @if(!auth()->user()->isApplicant())
                    buttons = [
                        { extend: 'csvHtml5', className: 'btn btn-outline-secondary btn-sm me-1', text: 'Export CSV' },
                        { extend: 'excelHtml5', className: 'btn btn-outline-success btn-sm me-1', text: 'Export Excel' },
                        { extend: 'pdfHtml5', className: 'btn btn-outline-danger btn-sm me-1', text: 'Export PDF' },
                        { extend: 'print', className: 'btn btn-outline-dark btn-sm', text: 'Print' }
                    ];
                @endif

                let table = $('#requisitionsTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[{{ auth()->user()->isApplicant() ? 4 : 5 }}, 'desc']],
                    dom: 'Bfrtip',
                    buttons: buttons,
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ requisitions",
                        emptyTable: "No job requisitions found",
                        zeroRecords: "No matching requisitions found"
                    }
                });

                // Filters
                $('#statusFilter').on('change', function () {
                    table.column(2).search($(this).val()).draw();
                });

                @if(!auth()->user()->isApplicant())
                    $('#approvalFilter').on('change', function () {
                        table.column(3).search($(this).val()).draw();
                    });
                @endif
            }
        });

        function resetFilters() {
            $('#statusFilter, #approvalFilter').val('').trigger('change');
        }
    </script>
@endsection
