@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create New User</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Manager's Departments -->
        <div class="mb-3 d-none" id="managerDepartmentsDiv">
            <label class="form-label">Departments (for Manager)</label>
            <select name="departments[]" class="form-select" multiple>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ collect(old('departments'))->contains($department->id) ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Reviewer's Applications -->
        <div class="mb-3 d-none" id="reviewerApplicationsDiv">
            <label class="form-label">Applications to Review (for Reviewer)</label>
            <select name="applications[]" class="form-select" multiple>
                @foreach($applications as $application)
                    <option value="{{ $application->id }}" {{ collect(old('applications'))->contains($application->id) ? 'selected' : '' }}>
                        Application #{{ $application->id }} - {{ $application->job_title ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const managerDiv = document.getElementById('managerDepartmentsDiv');
        const reviewerDiv = document.getElementById('reviewerApplicationsDiv');

        function toggleFields() {
            const role = roleSelect.value.toLowerCase();
            managerDiv.classList.toggle('d-none', role !== 'manager');
            reviewerDiv.classList.toggle('d-none', role !== 'reviewer');
        }

        roleSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
@endsection
