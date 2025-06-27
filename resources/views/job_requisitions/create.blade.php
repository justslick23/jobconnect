@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Create Job Requisition</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Please correct the following errors:
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('job-requisitions.store') }}" method="POST">
                @csrf

                <!-- Job Title -->
                <div class="mb-3">
                    <label for="title" class="form-label">Job Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <!-- Department -->
                <div class="mb-3">
                    <label for="department_id" class="form-label">Department *</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Job Description</label>
                    <textarea name="description" id="editor">{{ old('description') }}</textarea>
                </div>

                <!-- Notes/Extra Requirements -->
                <div class="mb-3">
                    <label for="requirements" class="form-label">Additional Notes or Requirements</label>
                    <textarea name="requirements" class="form-control" rows="3">{{ old('requirements') }}</textarea>
                </div>

                <!-- Vacancies -->
                <div class="mb-3">
                    <label for="vacancies" class="form-label">Number of Vacancies *</label>
                    <input type="number" name="vacancies" class="form-control" value="{{ old('vacancies', 1) }}" min="1" required>
                </div>

                <!-- Location -->
                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                </div>

                <!-- Employment Type -->
                <div class="mb-3">
                    <label class="form-label">Employment Type *</label><br>
                    @foreach(['full-time', 'part-time', 'contract', 'temporary'] as $type)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="employment_type" id="type_{{ $type }}" value="{{ $type }}"
                                {{ old('employment_type', 'full-time') === $type ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_{{ $type }}">{{ ucfirst($type) }}</label>
                        </div>
                    @endforeach
                </div>

                <!-- Deadline -->
                <div class="mb-3">
                    <label for="application_deadline" class="form-label">Application Deadline</label>
                    <input type="datetime-local" name="application_deadline" class="form-control" value="{{ old('application_deadline') }}">
                </div>

                <!-- Minimum Experience -->
                <div class="mb-3">
                    <label for="min_experience" class="form-label">Minimum Experience (in years) *</label>
                    <input type="number" name="min_experience" class="form-control" value="{{ old('min_experience') }}" min="0" required>
                </div>

                <!-- Education Level -->
                <div class="mb-3">
                    <label for="education_level" class="form-label">Required Education Level *</label>
                    <select name="education_level" class="form-control" required>
                        <option value="">-- Select Level --</option>
                        @php
                        $educationLevels = [
                            'High School',
                            'Certificate',
                            'Diploma',
                            'Associate Degree',
                            'Bachelor\'s Degree',
                            'Postgraduate Diploma',
                            'Master\'s Degree',
                            'Doctorate (PhD)',
                            'Other',
                        ];
                    @endphp
                    
                    @foreach($educationLevels as $level)
                        <option value="{{ $level }}" @if(old('education_level') == $level) selected @endif>
                            {{ $level }}
                        </option>
                    @endforeach
                    
                    </select>
                </div>

                <!-- Required Skills -->
                <div class="mb-3">
                    <label for="required_skills" class="form-label">Required Skills *</label>
                    <select name="required_skills[]" id="required_skills" class="form-control" multiple required>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ collect(old('required_skills'))->contains($skill->id) ? 'selected' : '' }}>
                                {{ $skill->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Start typing to add a new skill or select existing.</small>
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Submit Requisition</button>
                    <a href="{{ route('job-requisitions.index') }}" class="btn btn-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Tom Select CDN -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
    new TomSelect('#required_skills', {
        plugins: ['remove_button'],
        create: function(input, callback) {
            // We use AJAX to save new skill
            fetch('{{ route('skills.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ name: input })
            })
            .then(response => response.json())
            .then(data => {
                callback({ value: data.id, text: data.name });
            }).catch(error => {
                alert('Failed to add skill');
                callback();
            });
        },
    });
</script>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>
@endsection
