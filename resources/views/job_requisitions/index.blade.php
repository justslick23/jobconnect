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
                        <i class="fas fa-plus"></i> New Requisition
                    </a>
                @endif
            </div>
            <div>
                <span class="badge bg-info text-uppercase me-2">Active Listings</span>
                <span class="badge bg-secondary text-uppercase">{{ $requisitions->where('approval_status', 'approved')->count() }} Approved</span>
            </div>
        </div>

        @if($requisitions->isEmpty())
            <div class="text-center p-5 text-muted">
                <i class="bi bi-inbox fs-1 opacity-50 mb-3"></i>
                <h4>No Job Requisitions Found</h4>
                <p>There are no job requisitions to display.</p>
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
                <div class="col-md-4">
                    <select id="approvalFilter" class="form-select">
                        <option value="">Filter by Approval</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
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
                            <th>Approval</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitions as $req)
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
                                <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('job-requisitions.show', $req->uuid) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if(auth()->user()->isHrAdmin() && $req->approval_status === 'pending')
                                            <form method="POST" action="{{ route('job-requisitions.approve', $req) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('job-requisitions.reject', $req) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if(auth()->user()->isHrAdmin() || auth()->user()->isManager())
                                            <a href="{{ route('job-applications.index', ['job_requisition_id' => $req->id]) }}" class="btn btn-sm btn-outline-secondary" title="View Applications">
                                                <i class="bi bi-people-fill"></i>
                                            </a>
                                        @endif

                                        @if(auth()->user()->isApplicant() && $req->approval_status === 'approved' && $req->job_status === 'active')
                                            <a href="{{ route('job-applications.create', ['job_requisition_id' => $req->id]) }}" class="btn btn-sm btn-outline-primary" title="Apply">
                                                <i class="bi bi-send"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
            let table = $('#requisitionsTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[4, 'desc']],
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'csvHtml5', className: 'btn btn-outline-secondary btn-sm me-1', text: 'Export CSV' },
                    { extend: 'excelHtml5', className: 'btn btn-outline-success btn-sm me-1', text: 'Export Excel' },
                    { extend: 'pdfHtml5', className: 'btn btn-outline-danger btn-sm me-1', text: 'Export PDF' },
                    { extend: 'print', className: 'btn btn-outline-dark btn-sm', text: 'Print' }
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ requisitions",
                    emptyTable: "No job requisitions found",
                    zeroRecords: "No matching requisitions found"
                }
            });

            // Filter logic
            $('#statusFilter').on('change', function () {
                table.column(2).search($(this).val()).draw();
            });

            $('#approvalFilter').on('change', function () {
                table.column(3).search($(this).val()).draw();
            });
        });

        function resetFilters() {
            $('#statusFilter, #approvalFilter').val('').trigger('change');
        }
    </script>
@endsection