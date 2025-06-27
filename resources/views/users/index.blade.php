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
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
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
                <h1 class="mb-2 text-white"><i class="bi bi-people me-2"></i>User Management</h1>
                <p class="mb-0 opacity-75">Manage system users and their roles</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('users.create') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i> Add User
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @if($users->isEmpty())
        <div class="table-container">
            <div class="empty-state">
                <i class="bi bi-person-x"></i>
                <h4>No Users Found</h4>
                <p class="mb-0">There are currently no users in the system.</p>
            </div>
        </div>
    @else
        <div class="table-container">
            <table id="usersTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="bi bi-person me-2"></i>Name</th>
                        <th><i class="bi bi-envelope me-2"></i>Email</th>
                        <th><i class="bi bi-person-badge me-2"></i>Role(s)</th>
                        <th><i class="bi bi-building me-2"></i>Departments (if Manager)</th>
                        <th><i class="bi bi-journal-text me-2"></i>Review Applications (if Reviewer)</th>
                        <th><i class="bi bi-gear me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="fw-bold">{{ $user->name }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>
                                {{ $user->role->name ?? '-' }}
                            </td>
                            <td>
                                @if($user->isManager())
                                    {{ $user->departments->pluck('name')->implode(', ') ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($user->isReviewer())
                                    @php
                                        $apps = $user->reviewedApplications->pluck('id')->map(fn($id) => "App #$id")->implode(', ');
                                    @endphp
                                    {{ $apps ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>

                                <form action="{{ route('users.resendPasswordReset', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Send password reset link to this user?')">
                                        Resend Reset Link
                                    </button>
                                </form>
                                
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
            $('#usersTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    emptyTable: "No users found",
                    zeroRecords: "No matching users"
                }
            });
        });
    </script>
@endsection
