@extends('layouts.app')
@section('title', 'Departments - Create New')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add Department</h3>
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
                    <a href="{{ route('departments.index') }}">Departments</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Add Department</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="card-title">
                                <i class="fa fa-building me-2"></i>
                                Create New Department
                            </div>
                            <a href="{{ route('departments.index') }}" class="btn btn-secondary btn-sm ms-auto">
                                <i class="fa fa-arrow-left me-1"></i>
                                Back to Departments
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('departments.store') }}" method="POST" id="departmentForm">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="name" class="form-label">
                                            Department Name <span class="text-danger">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            class="form-control @error('name') is-invalid @enderror" 
                                            id="name" 
                                            name="name" 
                                            value="{{ old('name') }}" 
                                            placeholder="Enter department name"
                                            required
                                        >
                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Enter a unique name for the department (e.g., Human Resources, Finance, IT)
                                        </small>
                                    </div>
                                </div>
                            </div>

                      

                            <div class="card-action">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check me-2"></i>
                                    Create Department
                                </button>
                                <a href="{{ route('departments.index') }}" class="btn btn-danger">
                                    <i class="fa fa-times me-2"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card card-info">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa fa-info-circle me-2"></i>
                            Department Guidelines
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Naming Conventions:</h6>
                                <ul class="mb-3">
                                    <li>Use clear, descriptive names</li>
                                    <li>Avoid abbreviations when possible</li>
                                    <li>Use title case (e.g., "Human Resources")</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Best Practices:</h6>
                                <ul class="mb-3">
                                    <li>Assign a department head when possible</li>
                                    <li>Keep descriptions concise but informative</li>
                                    <li>Set status to "Active" for operational departments</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Form validation
        $('#departmentForm').on('submit', function(e) {
            var name = $('#name').val().trim();
            
            if (name === '') {
                e.preventDefault();
                $('#name').addClass('is-invalid');
                
                // Show error message if not already present
                if (!$('#name').next('.invalid-feedback').length) {
                    $('#name').after('<div class="invalid-feedback">Department name is required.</div>');
                }
                
                // Focus on the name field
                $('#name').focus();
                
                return false;
            }
        });
        
        // Remove validation error on input
        $('#name').on('input', function() {
            if ($(this).val().trim() !== '') {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });
        
        // Auto-capitalize department name
        $('#name').on('input', function() {
            var words = $(this).val().split(' ');
            for (var i = 0; i < words.length; i++) {
                if (words[i].length > 0) {
                    words[i] = words[i][0].toUpperCase() + words[i].substr(1).toLowerCase();
                }
            }
            $(this).val(words.join(' '));
        });
    });
</script>
@endsection