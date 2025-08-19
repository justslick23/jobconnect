@extends('layouts.app')

@section('styles')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" rel="stylesheet" />
@endsection
@section('title', 'Departments')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Department Management</h3>
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
                    <a href="#">Departments</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">Departments</h4>
                            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Add Department
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($departments->isEmpty())
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-folder-open fa-5x text-muted mb-3"></i>
                                    <h4 class="text-muted">No Departments Found</h4>
                                    <p class="text-muted">There are currently no departments in the system.</p>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table id="departmentsTable" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Department Name</th>
                                            <th style="width: 10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departments as $department)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-xs me-3">
                                                            <span class="avatar-title bg-primary rounded-circle">
                                                                <i class="fa fa-building"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $department->name }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-button-action">
                                                        <button type="button" class="btn btn-link btn-primary btn-lg" title="Edit Department">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-link btn-danger" title="Remove Department">
                                                            <i class="fa fa-times"></i>
                                                        </button>
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
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

@endsection