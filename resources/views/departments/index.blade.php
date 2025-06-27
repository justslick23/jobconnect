@extends('layouts.app')

@section('styles')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.002);
            transition: all 0.2s ease;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
@endsection

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2 text-white"><i class="bi bi-diagram-3 me-2"></i>Department Management</h1>
                <p class="mb-0 opacity-75">Manage and organize your internal departments</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('departments.create') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i> Add Department
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @if($departments->isEmpty())
        <div class="table-container">
            <div class="empty-state">
                <i class="bi bi-folder-x"></i>
                <h4>No Departments Found</h4>
                <p class="mb-0">There are currently no departments in the system.</p>
            </div>
        </div>
    @else
        <div class="table-container">
            <table id="departmentsTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="bi bi-diagram-3 me-2"></i>Department Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $department)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $department->name }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#departmentsTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [],
                language: {
                    search: "Search departments:",
                    lengthMenu: "Show _MENU_ departments per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ departments",
                    emptyTable: "No departments found",
                    zeroRecords: "No matching departments"
                }
            });
        });
    </script>
@endsection
