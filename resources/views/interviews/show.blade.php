@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Interview Details</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Applicant: {{ $interview->jobApplication->user->name ?? 'N/A' }}</h5>
            <p><strong>Job Application ID:</strong> {{ $interview->job_application_id }}</p>
            <p><strong>Interview Date & Time:</strong> {{ \Carbon\Carbon::parse($interview->interview_date)->format('d M Y, h:i A') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($interview->status) }}</p>
        </div>
    </div>

    <a href="{{ route('interviews.index') }}" class="btn btn-secondary mt-3">Back to Interviews</a>
</div>
@endsection
