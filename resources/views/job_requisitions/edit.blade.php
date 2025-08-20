@extends('layouts.app')

@section('content')
@section('title', 'Edit Job Requisition')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Job Requisitions</h3>
            <br>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#"><i class="icon-home"></i></a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('job-requisitions.index') }}">Job Requisitions</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">Edit - {{ $jobRequisition->title }}</li>
            </ul>
        </div>
        @include('partials.alerts')

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Edit Job Requisition: {{ $jobRequisition->title }}</div>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="alert-title">
                                    <i class="fas fa-exclamation-triangle"></i> Validation Error
                                </div>
                                Please fix the following errors:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('job-requisitions.update', $jobRequisition->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Basic Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title">Job Title <span class="required-label">*</span></label>
                                        <input type="text" 
                                               name="title" 
                                               id="title"
                                               class="form-control" 
                                               value="{{ old('title', $jobRequisition->title) }}" 
                                               placeholder="Enter job title"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department_id">Department <span class="required-label">*</span></label>
                                        <select name="department_id" id="department_id" class="form-select" required>
                                            <option value="">Choose Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" 
                                                        {{ old('department_id', $jobRequisition->department_id) == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vacancies">Number of Vacancies <span class="required-label">*</span></label>
                                        <input type="number" 
                                               name="vacancies" 
                                               id="vacancies"
                                               class="form-control" 
                                               value="{{ old('vacancies', $jobRequisition->vacancies) }}" 
                                               min="1"
                                               placeholder="1"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <input type="text" 
                                               name="location" 
                                               id="location"
                                               class="form-control" 
                                               value="{{ old('location', $jobRequisition->location) }}"
                                               placeholder="Enter job location">
                                    </div>
                                </div>
                            </div>

                            <!-- Job Description -->
                            <div class="form-group">
                                <label for="description">Job Description</label>
                                <textarea name="description" 
                                          id="editor" 
                                          class="form-control"
                                          placeholder="Describe the job responsibilities and requirements">{{ old('description', $jobRequisition->description) }}</textarea>
                            </div>

                            <!-- Additional Requirements -->
                            <div class="form-group">
                                <label for="requirements">Additional Notes or Requirements</label>
                                <textarea name="requirements" 
                                          id="requirements"
                                          class="form-control" 
                                          rows="4"
                                          placeholder="Any additional requirements or notes">{{ old('requirements', $jobRequisition->requirements) }}</textarea>
                            </div>

                            <!-- Employment Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employment Type <span class="required-label">*</span></label>
                                        <div class="selectgroup selectgroup-pills">
                                            @foreach(['full-time', 'part-time', 'contract', 'temporary'] as $type)
                                                <label class="selectgroup-item">
                                                    <input type="radio" 
                                                           name="employment_type" 
                                                           value="{{ $type }}" 
                                                           class="selectgroup-input"
                                                           {{ old('employment_type', $jobRequisition->employment_type) == $type ? 'checked' : '' }}>
                                                    <span class="selectgroup-button">{{ ucwords(str_replace('-', ' ', $type)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="application_deadline">Application Deadline</label>
                                        <input type="datetime-local" 
                                               name="application_deadline" 
                                               id="application_deadline"
                                               class="form-control" 
                                               value="{{ old('application_deadline', $jobRequisition->application_deadline ? $jobRequisition->application_deadline->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Experience and Education -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_experience">Minimum Experience (Years) <span class="required-label">*</span></label>
                                        <input type="number" 
                                               name="min_experience" 
                                               id="min_experience"
                                               class="form-control" 
                                               min="0" 
                                               value="{{ old('min_experience', $jobRequisition->min_experience) }}"
                                               placeholder="0"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="education_level">Required Education Level <span class="required-label">*</span></label>
                                        <select name="education_level" id="education_level" class="form-select" required>
                                            <option value="">Choose Education Level</option>
                                            @php
                                                $educationLevels = [
                                                    'High School','Certificate','Diploma','Associate Degree',
                                                    "Bachelor's Degree",'Postgraduate Diploma',"Master's Degree",'Doctorate (PhD)','Other'
                                                ];
                                            @endphp
                                            @foreach($educationLevels as $level)
                                                <option value="{{ $level }}" 
                                                        {{ old('education_level', $jobRequisition->education_level) == $level ? 'selected' : '' }}>
                                                    {{ $level }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="required_skills">Required Skills <span class="required-label">*</span></label>
                                        <select name="required_skills[]" 
                                                id="required_skills" 
                                                class="form-control" 
                                                multiple 
                                                required>
                                            @foreach($skills as $skill)
                                                <option value="{{ $skill->id }}" 
                                                        {{ (collect(old('required_skills', $jobRequisition->skills->pluck('id')->toArray()))->contains($skill->id)) ? 'selected' : '' }}>
                                                    {{ $skill->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Hold Ctrl/Cmd to select multiple skills. Type to add new skills.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- Area of Study -->
                                    <div class="form-group">
                                        <label for="area_of_study">Area of Study <span class="required-label">*</span></label>
                                        <select name="area_of_study[]" id="area_of_study" class="form-control" multiple required>
                                            @php
                                                $areasOfStudy = [
                                                    // --- Core IT / Engineering ---
                                                    'Computer Science','Information Technology','Information Systems',
                                                    'Software Engineering','Web Development','Mobile App Development',
                                                    'Artificial Intelligence','Data Science','Machine Learning','Cybersecurity',
                                                    'Network Engineering','Cloud Computing','Systems Administration',
                                                    'Database Administration','Electronics Engineering','Electrical Engineering',
                                                    'Telecommunications Engineering','Mechanical Engineering','Civil Engineering',

                                                    // --- Business / Admin / Finance ---
                                                    'Business Administration','Finance','Accounting','Economics',
                                                    'Supply Chain Management','Operations Management','Project Management',
                                                    'Office Administration','Public Administration','Secretarial Studies',

                                                    // --- HR / Training / Education ---
                                                    'Human Resources Management','Industrial Psychology','Organizational Development',
                                                    'Education','Training & Capacity Building','Adult Education','Instructional Design',

                                                    // --- Marketing / Sales ---
                                                    'Marketing','Digital Marketing','Communications','Public Relations',
                                                    'Sales','Retail Management','Customer Relationship Management',

                                                    // --- Sciences / General ---
                                                    'Statistics','Mathematics','Physics','Chemistry','Biology',
                                                    'Environmental Science','Biotechnology','Healthcare Administration',

                                                    // --- Law / Governance ---
                                                    'Law','Compliance','Risk Management','Political Science','International Relations',

                                                    // --- Other catch-all ---
                                                    'Arts & Humanities','Languages','Journalism','Hospitality Management','Tourism',
                                                    'Other'
                                                ];
                                                
                                                $selectedAreas = old('area_of_study', is_array($jobRequisition->area_of_study) 
                                                    ? $jobRequisition->area_of_study 
                                                    : json_decode($jobRequisition->area_of_study, true) ?? []);
                                            @endphp
                                            <option value="">Choose Area of Study</option>
                                            @foreach($areasOfStudy as $area)
                                                <option value="{{ $area }}" 
                                                        {{ collect($selectedAreas)->contains($area) ? 'selected' : '' }}>
                                                    {{ $area }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Hold Ctrl/Cmd to select multiple areas. You can also type to add new areas.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card-action">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Update Job Requisition
                                </button>
                                <a href="{{ route('job-requisitions.index') }}" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <a href="{{ route('job-requisitions.show', $jobRequisition->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TomSelect for Skills -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    new TomSelect('#area_of_study', {
        plugins: ['remove_button'],
        placeholder: "Select or type an area of study...",
        create: function(input) {
            return {
                value: input,
                text: `Add "${input}"`
            };
        },
        persist: false,
        duplicates: false,
        sortField: { field: "text", direction: "asc" },
        maxItems: 5 // optional
    });
</script>

<script>
    // Initialize TomSelect for skills
    new TomSelect('#required_skills', {
        plugins: ['remove_button'],
        create: function(input, callback) {
            fetch('{{ route('skills.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ name: input })
            }).then(response => response.json())
              .then(data => callback({ value: data.id, text: data.name }))
              .catch(() => callback());
        }
    });

    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'link', '|',
                'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|',
                'undo', 'redo'
            ]
        })
        .catch(console.error);

    // Validate shortlisting weights
    document.addEventListener('DOMContentLoaded', function() {
        const skillsWeight = document.querySelector('input[name="shortlisting_rules[skills]"]');
        const experienceWeight = document.querySelector('input[name="shortlisting_rules[experience]"]');
        const educationWeight = document.querySelector('input[name="shortlisting_rules[education]"]');
        
        if (skillsWeight && experienceWeight && educationWeight) {
            function validateWeights() {
                const total = parseInt(skillsWeight.value || 0) + 
                             parseInt(experienceWeight.value || 0) + 
                             parseInt(educationWeight.value || 0);
                
                const isValid = total === 100;
                const inputs = [skillsWeight, experienceWeight, educationWeight];
                
                inputs.forEach(input => {
                    if (isValid) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });
                
                // Show/hide validation message
                let validationMsg = document.getElementById('weights-validation');
                if (!validationMsg) {
                    validationMsg = document.createElement('div');
                    validationMsg.id = 'weights-validation';
                    validationMsg.className = 'invalid-feedback d-block';
                    educationWeight.parentNode.appendChild(validationMsg);
                }
                
                if (isValid) {
                    validationMsg.style.display = 'none';
                } else {
                    validationMsg.style.display = 'block';
                    validationMsg.textContent = `Total percentage is ${total}%. It should be exactly 100%.`;
                }
            }
            
            [skillsWeight, experienceWeight, educationWeight].forEach(input => {
                input.addEventListener('input', validateWeights);
            });
            
            validateWeights(); // Initial validation
        }
    });
</script>

<style>
.required-label {
    color: #e74c3c;
}

.selectgroup-pills .selectgroup-item .selectgroup-button {
    border-radius: 20px;
}

.form-group-default {
    position: relative;
    border: 1px solid #ebedf2;
    border-radius: 6px;
    background: #fff;
    padding: 10px 15px 5px;
}

.form-group-default label {
    font-size: 12px;
    color: #9b9b9b;
    font-weight: 400;
    margin-bottom: 5px;
}

.form-group-default input.form-control {
    border: none;
    padding: 0;
    font-size: 14px;
    background: transparent;
}

.form-group-default input.form-control:focus {
    box-shadow: none;
}

.card-action {
    padding: 20px 25px;
    border-top: 1px solid #ebedf2;
    background: #f8f9fa;
    border-radius: 0 0 6px 6px;
}

.alert-title {
    font-weight: 600;
    margin-bottom: 8px;
}

.page-header {
    margin-bottom: 30px;
}

.breadcrumbs {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
}

.breadcrumbs li {
    display: flex;
    align-items: center;
}

.breadcrumbs .separator {
    margin: 0 8px;
    color: #9b9b9b;
}

.breadcrumbs a {
    color: #177dff;
    text-decoration: none;
}

.breadcrumbs a:hover {
    color: #1269db;
}
</style>
@endsection