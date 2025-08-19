@extends('layouts.app')

@section('styles')
    <!-- Kaiadmin DataTables CSS already included in main layout -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-inner">
        <!-- Page Header -->
        <div class="page-header">
            <h3 class="fw-bold mb-3">User Management</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Users</a>
                </li>
            </ul>
        </div>

        <!-- Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">Users</h4>
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i> Add User
                            </a>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        @if($users->isEmpty())
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-user-slash fa-5x text-muted mb-3"></i>
                                    <h4 class="text-muted">No Users Found</h4>
                                    <p class="text-muted">There are currently no users in the system.</p>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table id="usersTable" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role(s)</th>
                                            <th>Departments (Manager)</th>
                                            <th>Review Applications</th>
                                            <th style="width: 15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email ?? '-' }}</td>
                                                <td>{{ $user->role->name ?? '-' }}</td>
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
                                                    <div class="form-button-action">
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-link btn-primary btn-sm" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </a>

                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-link btn-danger btn-sm" title="Delete">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('users.resendPasswordReset', $user->id) }}" method="POST" class="d-inline-block">
                                                            @csrf
                                                            <button type="submit" class="btn btn-link btn-secondary btn-sm" onclick="return confirm('Send password reset link to this user?')" title="Reset Password">
                                                                <i class="fa fa-envelope-open-text"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- table-responsive -->
                        @endif
                    </div> <!-- card-body -->
                </div> <!-- card -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- jQuery (if not already loaded by Kaiadmin) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS (if not already loaded by Kaiadmin) -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            if (!$.fn.DataTable.isDataTable('#usersTable')) {
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
            }
        });
    </script>
@endsection
