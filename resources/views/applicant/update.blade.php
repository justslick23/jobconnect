@extends('layouts.app')
@section('title', 'Applicant Profile')

@section('content')
<div class="container">
    <div class="page-inner">
      {{-- Page Header Section --}}
{{-- Page Header Section --}}
<div class="page-header">
    <div class="page-header-content">
        <div class="header-title-section mb-4">
            <h3 class="fw-bold mb-3">Complete Your Profile</h3>
            
            <p class="text-muted">
                Please provide information that is relevant to the position you're applying for.  
                Focus on experiences, skills, and qualifications that demonstrate your suitability for the role.
            </p>
        </div>

        {{-- Draft Status Alert --}}
        @if(optional($user->profile)->is_draft)
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Draft in Progress:</strong> 
                Your profile is currently saved as a draft. Complete all required sections and click "Submit Profile" to finalize it.
            </div>
        @endif

    {{-- Navigation Section --}}
<div class="header-navigation">
    <div class="d-flex gap-2 flex-wrap mb-3">
       
        <a href="{{ route('job-requisitions.index') }}" class="btn btn-primary">
            <i class="fas fa-briefcase me-1"></i> 
            Back to Jobs
        </a>
    </div>
</div>
    </div>
</div>
        
        @include('partials.alerts')

        <form action="{{ isset($user->profile) && $user->profile->exists ? route('applicant.profile.update') : route('applicant.profile.store') }}" 
            method="POST" enctype="multipart/form-data" id="profile-form">
          @csrf
          @if(isset($user->profile) && $user->profile->exists)
              @method('PUT')
          @endif
            
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Personal Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control required-for-final" 
                                       value="{{ old('first_name', optional($user->profile)->first_name) }}">
                                @error('first_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control required-for-final" 
                                       value="{{ old('last_name', optional($user->profile)->last_name) }}">
                                @error('last_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control required-for-final" 
                                       value="{{ old('phone', optional($user->profile)->phone) }}">
                                @error('phone')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" readonly value="{{ auth()->user()->email }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>District <span class="text-danger">*</span></label>
                                <select name="location" class="form-select required-for-final">
                                    <option value="">Select Location</option>
                                    @foreach(['Maseru', 'Berea', 'Butha-Buthe', 'Leribe', 'Mafeteng', "Mohale's Hoek", 'Mokhotlong', "Qacha's Nek", 'Quthing', 'Thaba-Tseka'] as $location)
                                    <option value="{{ $location }}" {{ old('location', optional($user->profile)->district) == $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                </select>
                                @error('location')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Sections -->
            @php
            $sections = [
                'education' => [
                    'title' => 'Education', 
                    'required' => true,
                    'description' => 'Include educational qualifications that are relevant to the position you\'re applying for. Focus on degrees, courses, or certifications that demonstrate the knowledge and skills required for the role.',
                    'fields' => [
                        'degree' => ['type' => 'text', 'label' => 'Degree', 'required' => true, 'placeholder' => 'e.g., Bachelor of Science'],
                        'education_level' => [
                            'type' => 'select_with_custom', 
                            'label' => 'Education Level', 
                            'required' => true, 
                            'options' => [
                                'High School', 'Certificate', 'Diploma', 'Associate Degree', 
                                'Bachelor\'s Degree', 'Postgraduate Diploma', 'Master\'s Degree', 
                                'Doctorate (PhD)', 'Chartered Accountant', 'Certified Public Accountant', 
                                'Project Management Professional', 'IT Certification', 
                                'HR Professional Certification', 'Legal Qualification', 
                                'Financial Analyst Certification', 'Professional Qualification'
                            ]
                        ],
                        'field_of_study' => [
                            'type' => 'select_with_custom', 
                            'label' => 'Field of Study', 
                            'required' => true, 
                            'options' => [
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
                                'Arts & Humanities','Languages','Journalism','Hospitality Management','Tourism'
                            ]
                        ],
                        'institution' => ['type' => 'text', 'label' => 'Institution', 'required' => true, 'placeholder' => 'e.g., National University of Lesotho'],
                        'status' => ['type' => 'select', 'label' => 'Status', 'required' => true, 'options' => ['Completed', 'In Progress', 'Paused/Deferred']],
                        'start_date' => ['type' => 'date', 'label' => 'Start Date'],
                        'end_date' => ['type' => 'date', 'label' => 'End Date', 'note' => 'Leave blank if currently studying or not yet completed'],
                        'expected_graduation' => ['type' => 'date', 'label' => 'Expected Graduation Date', 'note' => 'For ongoing studies']
                    ]
                ],
                'experiences' => [
                    'title' => 'Work Experience', 
                    'description' => 'List work experiences that are most relevant to the position. Highlight responsibilities, achievements, and skills that align with the job requirements. You can include internships, part-time work, and volunteer positions if they\'re relevant.',
                    'fields' => [
                        'job_title' => ['type' => 'text', 'label' => 'Job Title', 'required' => true, 'placeholder' => 'e.g., Software Developer'],
                        'company' => ['type' => 'text', 'label' => 'Company/Organization', 'required' => true, 'placeholder' => 'e.g., ABC Technologies'],
                        'description' => ['type' => 'textarea', 'label' => 'Key Responsibilities & Achievements', 'required' => true, 'placeholder' => 'Focus on accomplishments and responsibilities that demonstrate your suitability for the role you\'re applying for...', 'rows' => 4],
                        'start_date' => ['type' => 'date', 'label' => 'Start Date', 'required' => true],
                        'end_date' => ['type' => 'date', 'label' => 'End Date', 'note' => 'Leave blank if currently employed']
                    ]
                ],
                'qualifications' => [
                    'title' => 'Professional Qualifications & Certifications',
                    'required' => false, // Make the entire section optional
                    'description' => 'Include certifications, licenses, and professional qualifications that are relevant to the position. These could be technical certifications, professional memberships, or industry-specific qualifications. All fields in this section are optional.',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Title (Optional)', 'required' => false, 'placeholder' => 'e.g., Microsoft Azure Certified'],
                        'type' => ['type' => 'select', 'label' => 'Type (Optional)', 'required' => false, 'options' => ['', 'Certification', 'License', 'Professional Qualification', 'Award', 'Other']],
                        'institution' => ['type' => 'text', 'label' => 'Issuing Organization (Optional)', 'required' => false, 'placeholder' => 'e.g., Microsoft, Cisco, PMI'],
                        'issued_date' => ['type' => 'date', 'label' => 'Issued Date (Optional)', 'required' => false],
                        'notes' => ['type' => 'textarea', 'label' => 'Relevance to Position (Optional)', 'required' => false, 'placeholder' => 'Explain how this qualification is relevant to the role you\'re applying for...', 'rows' => 2]
                    ]
                ],
                'references' => [
                    'title' => 'References',
                    'description' => 'Provide references who can speak to your professional abilities and character, particularly those who have supervised your work in roles similar to the position you\'re applying for.',
                    'fields' => [
                        'name' => ['type' => 'text', 'label' => 'Full Name', 'required' => true, 'placeholder' => 'e.g., John Doe'],
                        'relationship' => ['type' => 'text', 'label' => 'Professional Relationship', 'required' => true, 'placeholder' => 'e.g., Former Supervisor, Team Lead, Client'],
                        'email' => ['type' => 'email', 'label' => 'Email', 'required' => true, 'placeholder' => 'reference@company.com'],
                        'phone' => ['type' => 'tel', 'label' => 'Phone', 'placeholder' => '+266 xxxx xxxx'],
                        'context' => ['type' => 'text', 'label' => 'Context of Relationship', 'placeholder' => 'e.g., Supervised my work on software development projects (2020-2022)']
                    ]
                ]
            ];
            @endphp
            
            @foreach($sections as $section => $config)
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <span>
                            {{ $config['title'] }}
                            @if(isset($config['required']) && $config['required'])
                                <span class="text-danger">*</span>
                            @endif
                        </span>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addEntry('{{ $section }}')">
                            <i class="fa fa-plus"></i> Add {{ rtrim($config['title'], 's') }}
                        </button>
                    </div>
                    @if(isset($config['description']))
                    <div class="card-subtitle mt-2">
                        <small class="text-muted">{{ $config['description'] }}</small>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div id="{{ $section }}-container">
                        @php
                            $items = old($section) ?? (isset($user->{$section}) && $user->{$section}->isNotEmpty() ? $user->{$section}->toArray() : []);
                            if (empty($items)) $items = [[]];
                        @endphp
                        @foreach($items as $index => $item)
                        @php $item = is_array($item) ? (object) $item : $item; @endphp
                        <div class="entry-block p-3 mb-3 border rounded" data-index="{{ $index }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">{{ rtrim($config['title'], 's') }} #{{ $index + 1 }}</h6>
                                @if($index > 0)
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row">
                                @foreach($config['fields'] as $field => $fieldConfig)
                                <div class="col-md-{{ in_array($fieldConfig['type'], ['textarea']) || $field == 'institution' || $field == 'context' ? '12' : '6' }}">
                                    <div class="form-group">
                                        <label>
                                            {{ $fieldConfig['label'] }}
                                            @if(isset($fieldConfig['required']) && $fieldConfig['required'] && isset($config['required']) && $config['required'])
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        @if($fieldConfig['type'] == 'select_with_custom')
                                            @php
                                                $currentValue = isset($item->{$field}) ? $item->{$field} : '';
                                                $isCustomValue = !empty($currentValue) && !in_array($currentValue, $fieldConfig['options']);
                                                $uniqueFieldId = $section . '-' . $index . '-' . $field;
                                            @endphp
                                            <select name="{{ $section }}[{{ $index }}][{{ $field }}]" 
                                                    class="form-select custom-select-field {{ isset($fieldConfig['required']) && $fieldConfig['required'] && isset($config['required']) && $config['required'] ? 'required-for-final' : '' }}" 
                                                    onchange="toggleCustomField(this, '{{ $uniqueFieldId }}')">
                                                <option value="">-- Select {{ str_replace(' (Optional)', '', $fieldConfig['label']) }} --</option>
                                                @foreach($fieldConfig['options'] as $option)
                                                    <option value="{{ $option }}" {{ $currentValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                                <option value="custom" {{ $isCustomValue ? 'selected' : '' }}>Other (Please specify)</option>
                                            </select>
                                            
                                            <!-- Custom input field (hidden by default) -->
                                            <input type="text" 
                                                   name="{{ $section }}[{{ $index }}][custom_{{ $field }}]" 
                                                   id="custom-field-{{ $uniqueFieldId }}" 
                                                   class="form-control mt-2 custom-field-input" 
                                                   placeholder="Please specify..." 
                                                   value="{{ $isCustomValue ? $currentValue : '' }}"
                                                   style="display: {{ $isCustomValue ? 'block' : 'none' }};">
                                                   
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Don't see your {{ strtolower($fieldConfig['label']) }}? Select "Other (Please specify)" to add a custom one.
                                            </small>
                                        @elseif($fieldConfig['type'] == 'select')
                                            <!-- Regular select field (no custom option) -->
                                            <select name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-select {{ isset($fieldConfig['required']) && $fieldConfig['required'] && isset($config['required']) && $config['required'] ? 'required-for-final' : '' }}">
                                                @if(isset($fieldConfig['options']) && count($fieldConfig['options']) > 0 && $fieldConfig['options'][0] !== '')
                                                    <option value="">-- Select {{ str_replace(' (Optional)', '', $fieldConfig['label']) }} --</option>
                                                @endif
                                                @foreach($fieldConfig['options'] as $option)
                                                    @if($option !== '')
                                                    <option value="{{ $option }}" {{ (isset($item->{$field}) ? $item->{$field} : '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @elseif($fieldConfig['type'] == 'textarea')
                                            <textarea name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-control {{ isset($fieldConfig['required']) && $fieldConfig['required'] && isset($config['required']) && $config['required'] ? 'required-for-final' : '' }}" 
                                                      rows="{{ $fieldConfig['rows'] ?? 3 }}" 
                                                      placeholder="{{ $fieldConfig['placeholder'] ?? '' }}">{{ isset($item->{$field}) ? $item->{$field} : '' }}</textarea>
                                       @else
                                            <input type="{{ $fieldConfig['type'] }}" name="{{ $section }}[{{ $index }}][{{ $field }}]" 
                                                class="form-control {{ isset($fieldConfig['required']) && $fieldConfig['required'] && isset($config['required']) && $config['required'] ? 'required-for-final' : '' }}" value="{{ isset($item->{$field}) ? $item->{$field} : '' }}" 
                                                placeholder="{{ $fieldConfig['placeholder'] ?? '' }}">
                                        @endif
                                        @if(isset($fieldConfig['note']))
                                            <small class="form-text text-muted">{{ $fieldConfig['note'] }}</small>
                                        @endif
                                        @error("$section.$index.$field")<small class="form-text text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
            
            {{-- <script>
            function toggleCustomFieldOfStudy(selectElement, uniqueId) {
                const customInput = document.getElementById('custom-field-' + uniqueId);
                const isCustomSelected = selectElement.value === 'custom';
                
                if (isCustomSelected) {
                    customInput.style.display = 'block';
                    customInput.required = true;
                    customInput.focus();
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.value = '';
                }
            }
            
            // Handle form submission to use custom value if "Other" is selected
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const customSelects = document.querySelectorAll('.field-of-study-select');
                        customSelects.forEach(function(select) {
                            if (select.value === 'custom') {
                                const uniqueId = select.getAttribute('onchange').match(/'([^']+)'/)[1];
                                const customInput = document.getElementById('custom-field-' + uniqueId);
                                if (customInput && customInput.value.trim()) {
                                    // Create a hidden input with the custom value
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = select.name;
                                    hiddenInput.value = customInput.value.trim();
                                    form.appendChild(hiddenInput);
                                    
                                    // Remove the original select to avoid conflicts
                                    select.disabled = true;
                                }
                            }
                        });
                    });
                }
            });
            </script> --}}

            <!-- Skills -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Skills <span class="text-danger">*</span></div>
                    <div class="card-subtitle mt-2">
                        <small class="text-muted">Add skills that are relevant to the position you're applying for. Click on suggested skills below or type your own. Include both technical skills and soft skills that match the job requirements.</small>
                    </div>
                </div>
                <div class="card-body">
                    @php
                         $skills = [];
                if (old('skills')) {
                    $skills = is_string(old('skills')) ? explode(',', old('skills')) : old('skills');
                } elseif (isset($user) && $user->skills()->exists()) {
                    $skills = $user->skills()->pluck('name')->toArray();
                }

                        // Initialize jobRequisitionSkills if not set
                        $jobRequisitionSkills = isset($jobRequisitionSkills) ? $jobRequisitionSkills : [];
                        
                        // Define skill categories
                        $skillCategories = [
                            'Job-Specific Skills' => $jobRequisitionSkills,
                            'Technical Skills' => [
                                'Microsoft Office', 'Excel', 'PowerPoint', 'Word', 'Outlook',
                                'Google Workspace', 'Data Analysis', 'SQL', 'Python', 'JavaScript',
                                'HTML', 'CSS', 'Project Management', 'Agile/Scrum', 'Database Management',
                                'Web Development', 'Mobile Development', 'Cloud Computing', 'Cybersecurity',
                                'Network Administration', 'Systems Administration', 'IT Support',
                                'Quality Assurance', 'Software Testing', 'Version Control (Git)',
                                'Adobe Creative Suite', 'Graphic Design', 'UI/UX Design',
                                'Digital Marketing', 'SEO/SEM', 'Social Media Management',
                                'Content Management Systems', 'E-commerce', 'CRM Software',
                                'ERP Systems', 'Accounting Software', 'Financial Analysis',
                                'Budgeting', 'Procurement', 'Supply Chain Management'
                            ],
                            'Soft Skills' => [
                                'Communication', 'Leadership', 'Teamwork', 'Problem Solving',
                                'Critical Thinking', 'Time Management', 'Organization',
                                'Adaptability', 'Creativity', 'Initiative', 'Attention to Detail',
                                'Customer Service', 'Negotiation', 'Public Speaking',
                                'Conflict Resolution', 'Decision Making', 'Strategic Planning',
                                'Mentoring', 'Training & Development', 'Cross-functional Collaboration',
                                'Stress Management', 'Multitasking', 'Cultural Awareness',
                                'Emotional Intelligence', 'Active Listening', 'Presentation Skills',
                                'Research Skills', 'Analytical Thinking', 'Innovation',
                                'Relationship Building', 'Client Management', 'Vendor Management'
                            ],
                            'Industry-Specific' => [
                                'Healthcare Administration', 'Patient Care', 'Medical Terminology',
                                'HIPAA Compliance', 'Regulatory Compliance', 'Risk Management',
                                'Audit', 'Tax Preparation', 'Financial Reporting', 'Investment Analysis',
                                'Legal Research', 'Contract Management', 'Policy Development',
                                'Grant Writing', 'Fundraising', 'Event Planning', 'Marketing Strategy',
                                'Brand Management', 'Market Research', 'Sales Strategy',
                                'Business Development', 'Operations Management', 'Process Improvement',
                                'Quality Control', 'Inventory Management', 'Logistics',
                                'Human Resources', 'Recruitment', 'Employee Relations',
                                'Performance Management', 'Compensation & Benefits', 'Training Coordination'
                            ]
                        ];
                    @endphp

             
                    
                    <!-- Selected Skills Display -->
                    <div id="skills-container" class="mb-3">
                        <div class="fw-bold mb-2">Selected Skills:</div>
                        <div id="selected-skills-display" class="mb-2 p-2 border rounded" style="min-height: 50px; background-color: #f8f9fa;">
                            @foreach($skills as $skill)
                            <span class="badge badge-primary me-1 mb-1 skill-badge" data-skill="{{ trim($skill) }}">
                                {{ trim($skill) }} <span class="ms-1" style="cursor:pointer" onclick="removeSkill(this)">×</span>
                            </span>
                            @endforeach
                            <span id="no-skills-text" class="text-muted fst-italic" style="{{ count($skills) > 0 ? 'display: none;' : '' }}">No skills selected yet. Click on suggestions below or type your own.</span>
                        </div>
                    </div>
                    
                    <!-- Manual Skill Input -->
                    <div class="form-group">
                        <label class="fw-bold">Add Skills:</label>
                        <input type="text" id="skill-input" class="form-control" 
                               placeholder="Start typing to see skill suggestions or add your own...">
                        <small class="text-muted">Press Enter or comma to add skills. Suggestions will appear as you type.</small>
                    </div>

                    <!-- Live Skill Suggestions (shown as user types) -->
                    <div id="skill-suggestions-container" class="mt-3" style="display: none;">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted fw-bold mb-2 d-block">
                                <i class="fas fa-lightbulb"></i> Suggested skills:
                            </small>
                            <div id="live-suggestions"></div>
                        </div>
                    </div>

                    <!-- Hidden input for form submission -->
                    <input type="hidden" name="skills" id="skills-hidden" class="required-for-final" value="{{ implode(',', $skills) }}">
                    @error('skills')<small class="form-text text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <!-- Documents -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Upload Supporting Documents</div>
                    <div class="card-subtitle mt-2">
                        <small class="text-muted">Upload documents that support your application. Ensure your CV/Resume highlights experiences and achievements relevant to the position.</small>
                    </div>
                </div>
                <div class="card-body">
                    @php 
                        $attachments = isset($user) && $user->attachments ? $user->attachments->groupBy('type') : collect();
                    @endphp
                    
                    <!-- Resume/CV - Required for final submission -->
                    <div class="form-group">
                        <label>CV / Resume <span class="text-danger">*</span> (PDF, DOCX, max 5MB)</label>
                        <input type="file" 
                               name="resume" 
                               class="form-control document-required-for-final" 
                               accept=".pdf,.doc,.docx"
                               data-max-size="5242880">
                        <small class="form-text text-muted">Ensure your CV highlights relevant experience and achievements for the position you're applying for.</small>
                        @if($attachments->has('resume') && $attachments['resume']->count() > 0)
                            <small class="form-text text-success">
                                Current file: <a href="{{ asset('storage/' . $attachments['resume'][0]->file_path) }}" target="_blank">{{ $attachments['resume'][0]->original_name }}</a>
                                <br><em>Upload a new file to replace the current one</em>
                            </small>
                        @endif
                        @error('resume')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>
                    
                    <!-- Cover Letter -->
                    <div class="form-group">
                        <label>Cover Letter (PDF, DOCX, max 5MB)</label>
                        <input type="file" 
                               name="cover_letter" 
                               class="form-control" 
                               accept=".pdf,.doc,.docx"
                               data-max-size="5242880">
                        <small class="form-text text-muted">A tailored cover letter explaining your interest and suitability for the specific position.</small>
                        @if($attachments->has('cover_letter') && $attachments['cover_letter']->count() > 0)
                            <small class="form-text text-success">
                                Current file: <a href="{{ asset('storage/' . $attachments['cover_letter'][0]->file_path) }}" target="_blank">{{ $attachments['cover_letter'][0]->original_name }}</a>
                            </small>
                        @endif
                        @error('cover_letter')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>

                    <!-- Academic Transcripts -->
                    <div class="form-group">
                        <label>Academic Transcripts (PDF, DOCX, max 5MB)</label>
                        <input type="file" 
                               name="transcripts" 
                               class="form-control" 
                               accept=".pdf,.doc,.docx"
                               data-max-size="5242880">
                        @if($attachments->has('transcripts') && $attachments['transcripts']->count() > 0)
                            <small class="form-text text-success">
                                Current file: <a href="{{ asset('storage/' . $attachments['transcripts'][0]->file_path) }}" target="_blank">{{ $attachments['transcripts'][0]->original_name }}</a>
                            </small>
                        @endif
                        @error('transcripts')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>
                    
                    <!-- Other Supporting Documents -->
                    <div class="form-group">
                        <label>Other Supporting Documents (Multiple files allowed)</label>
                        <input type="file" 
                               name="other_documents[]" 
                               class="form-control" 
                               multiple 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               data-max-size="5242880">
                        <small class="form-text text-muted">Additional documents relevant to your application (certifications, portfolios, etc.). Max 5MB per file.</small>
                        @if($attachments->has('other') && $attachments['other']->count() > 0)
                            <small class="form-text text-success">
                                Current files: 
                                @foreach($attachments['other'] as $doc)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">{{ $doc->original_name }}</a>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </small>
                        @endif
                        @error('other_documents.*')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>

                    <!-- File validation feedback -->
                    <div id="file-validation-feedback" class="mt-2"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Submission Options</h5>
                                <p class="mb-2"><strong>Save Draft:</strong> Save your progress without validation. You can continue editing later.</p>
                                <p class="mb-0"><strong>Submit Profile:</strong> Complete your profile with all required fields filled. This will finalize your profile.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="draft-info">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                @if(optional($user->profile)->is_draft)
                                    Last saved as draft: {{ optional($user->profile)->updated_at?->format('M j, Y g:i A') ?? 'Never' }}
                                @else
                                    Auto-save as draft while editing
                                @endif
                            </small>
                        </div>
                        
                        <div class="action-buttons d-flex gap-2">
                            <button type="submit" name="save_draft" value="1" class="btn btn-outline-primary">
                                <i class="fas fa-save me-1"></i> Save Draft
                            </button>
                            <button type="submit" class="btn btn-success" id="submit-final">
                                <i class="fas fa-check me-1"></i> Submit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.skill-badge {
    font-size: 0.875rem;
    cursor: default;
}

.live-suggestion {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    transition: all 0.2s;
    border: 1px solid #007bff;
}

.live-suggestion:hover {
    background-color: #007bff;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

#skill-suggestions-container {
    position: relative;
    z-index: 1000;
}

.live-suggestion strong {
    color: #0056b3;
}

.live-suggestion:hover strong {
    color: white;
}

#selected-skills-display {
    max-height: 150px;
    overflow-y: auto;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.validation-error {
    border-color: #dc3545 !important;
}

.validation-summary {
    max-height: 200px;
    overflow-y: auto;
}
</style>
<!-- COMPLETE UPDATED SCRIPT SECTION -->

<script>
    // ============================================
    // CUSTOM FIELD HANDLING (Education Level & Field of Study)
    // ============================================
    
    // Generic function to toggle custom fields
    function toggleCustomField(selectElement, uniqueId) {
        const customInput = document.getElementById('custom-field-' + uniqueId);
        if (!customInput) return;
        
        const isCustomSelected = selectElement.value === 'custom';
        
        if (isCustomSelected) {
            customInput.style.display = 'block';
            customInput.required = true;
            customInput.focus();
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }
    
    // Handle form submission to use custom values
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#profile-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Handle ALL custom select fields (education_level, field_of_study, etc.)
                const customSelects = document.querySelectorAll('.custom-select-field');
                customSelects.forEach(function(select) {
                    if (select.value === 'custom') {
                        const onchangeAttr = select.getAttribute('onchange');
                        if (onchangeAttr) {
                            const match = onchangeAttr.match(/'([^']+)'/);
                            if (match && match[1]) {
                                const uniqueId = match[1];
                                const customInput = document.getElementById('custom-field-' + uniqueId);
                                if (customInput && customInput.value.trim()) {
                                    // Create a hidden input with the custom value
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = select.name;
                                    hiddenInput.value = customInput.value.trim();
                                    form.appendChild(hiddenInput);
                                    
                                    // Disable the original select to avoid conflicts
                                    select.disabled = true;
                                }
                            }
                        }
                    }
                });
            });
        }
    
        // ============================================
        // SKILLS FUNCTIONALITY
        // ============================================
        
        const skillInput = document.getElementById('skill-input');
        const skillsContainer = document.getElementById('selected-skills-display');
        const hiddenInput = document.getElementById('skills-hidden');
        const noSkillsText = document.getElementById('no-skills-text');
        const suggestionsContainer = document.getElementById('skill-suggestions-container');
        const liveSuggestions = document.getElementById('live-suggestions');
    
        // Get all available skills from PHP data
        const allSkills = [
            @if(!empty($jobRequisitionSkills))
                ...@json($jobRequisitionSkills),
            @endif
            ...@json($skillCategories['Technical Skills'] ?? []),
            ...@json($skillCategories['Soft Skills'] ?? []),
            ...@json($skillCategories['Industry-Specific'] ?? [])
        ].filter((skill, index, arr) => {
            return skill && skill.trim() && arr.indexOf(skill) === index;
        });
        
        let suggestionTimeout;
    
        // Initialize
        updateSkillStates();
    
        // Live suggestions as user types
        if (skillInput) {
            skillInput.addEventListener('input', function() {
                clearTimeout(suggestionTimeout);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    suggestionTimeout = setTimeout(() => showLiveSuggestions(query), 150);
                } else {
                    hideSuggestions();
                }
            });
    
            skillInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addSkillsFromInput();
                }
                if (e.key === 'Escape') {
                    hideSuggestions();
                }
            });
    
            skillInput.addEventListener('blur', function() {
                setTimeout(() => {
                    const input = this.value.trim();
                    if (input) addSkillsFromInput();
                    hideSuggestions();
                }, 150);
            });
    
            skillInput.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query.length >= 2) {
                    showLiveSuggestions(query);
                }
            });
        }
    
        function showLiveSuggestions(query) {
            const matchedSkills = allSkills.filter(skill => 
                skill.toLowerCase().includes(query.toLowerCase()) && 
                !isSkillSelected(skill)
            ).slice(0, 8);
    
            if (matchedSkills.length > 0) {
                liveSuggestions.innerHTML = matchedSkills.map(skill => {
                    const escapedSkill = skill.replace(/'/g, "\\'");
                    return `<button type="button" 
                             class="btn btn-outline-primary btn-sm me-1 mb-1 live-suggestion" 
                             data-skill="${skill}"
                             onmousedown="event.preventDefault(); addSkillFromSuggestion('${escapedSkill}')">
                        ${highlightMatch(skill, query)}
                     </button>`;
                }).join('');
                suggestionsContainer.style.display = 'block';
            } else {
                hideSuggestions();
            }
        }
    
        function highlightMatch(skill, query) {
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return skill.replace(regex, '<strong>$1</strong>');
        }
    
        function hideSuggestions() {
            suggestionsContainer.style.display = 'none';
            liveSuggestions.innerHTML = '';
        }
    
        function addSkillsFromInput() {
            const input = skillInput.value.trim();
            if (!input) return;
    
            const skills = input.split(',').map(s => s.trim()).filter(s => s);
            skills.forEach(skill => addSkill(skill));
            skillInput.value = '';
        }
    
        function addSkill(skillName) {
            skillName = skillName.trim();
            if (!skillName || isSkillSelected(skillName)) return;
    
            const badge = document.createElement('span');
            badge.className = 'badge badge-primary me-1 mb-1 skill-badge';
            badge.dataset.skill = skillName;
            badge.innerHTML = `${skillName} <span class="ms-1" style="cursor:pointer" onclick="removeSkill(this)">×</span>`;
            
            skillsContainer.appendChild(badge);
            updateHiddenInput();
            updateSkillStates();
        }
    
        function isSkillSelected(skillName) {
            return Array.from(skillsContainer.querySelectorAll('.skill-badge'))
                       .some(badge => badge.dataset.skill.toLowerCase() === skillName.toLowerCase());
        }
    
        function updateHiddenInput() {
            const skills = Array.from(skillsContainer.querySelectorAll('.skill-badge'))
                              .map(badge => badge.dataset.skill);
            hiddenInput.value = skills.join(',');
        }
    
        function updateSkillStates() {
            toggleNoSkillsText();
        }
    
        function toggleNoSkillsText() {
            const hasSkills = skillsContainer.querySelectorAll('.skill-badge').length > 0;
            noSkillsText.style.display = hasSkills ? 'none' : 'block';
        }
    
        // Global functions for onclick handlers
        window.addSkillFromSuggestion = function(skillName) {
            addSkill(skillName);
            skillInput.value = '';
            hideSuggestions();
            skillInput.focus();
        };
    
        window.removeSkill = function(element) {
            element.parentElement.remove();
            updateHiddenInput();
            updateSkillStates();
        };
    
        // ============================================
        // FORM VALIDATION FOR FINAL SUBMISSION
        // ============================================
        
        const submitFinalBtn = document.getElementById('submit-final');
    
        if (submitFinalBtn) {
            submitFinalBtn.addEventListener('click', function(e) {
                clearValidationStates();
                
                const requiredFields = form.querySelectorAll('.required-for-final');
                const missingFields = [];
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('validation-error');
                        const label = field.closest('.form-group')?.querySelector('label')?.textContent?.trim();
                        if (label) {
                            missingFields.push(label.replace('*', '').trim());
                        }
                    }
                });
    
                // Check if at least one education entry exists
                const educationEntries = form.querySelectorAll('#education-container .entry-block');
                const hasValidEducation = Array.from(educationEntries).some(entry => {
                    const degree = entry.querySelector('input[name*="[degree]"]')?.value?.trim();
                    const institution = entry.querySelector('input[name*="[institution]"]')?.value?.trim();
                    return degree && institution;
                });
    
                if (!hasValidEducation) {
                    missingFields.push('At least one education entry');
                }
    
                // Check resume upload requirement
                const resumeInput = form.querySelector('input[name="resume"]');
                const hasExistingResume = form.querySelector('.form-text.text-success') && 
                                         form.querySelector('.form-text.text-success').textContent.includes('Current file:');
                
                if (!hasExistingResume && (!resumeInput.files || resumeInput.files.length === 0)) {
                    missingFields.push('CV/Resume upload');
                    resumeInput.classList.add('validation-error');
                }
    
                if (missingFields.length > 0) {
                    e.preventDefault();
                    showValidationSummary(missingFields);
                    return false;
                }
            });
        }
    
        function clearValidationStates() {
            form.querySelectorAll('.validation-error').forEach(el => {
                el.classList.remove('validation-error');
            });
            
            const existingSummary = document.getElementById('validation-summary');
            if (existingSummary) {
                existingSummary.remove();
            }
        }
    
        function showValidationSummary(missingFields) {
            const summaryHtml = `
                <div id="validation-summary" class="alert alert-danger mt-3">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Required Fields Missing</h5>
                    <p class="mb-2">Please complete the following required fields before submitting:</p>
                    <div class="validation-summary">
                        <ul class="mb-0">
                            ${missingFields.map(field => `<li>${field}</li>`).join('')}
                        </ul>
                    </div>
                    <hr>
                    <p class="mb-0">
                        <small><strong>Tip:</strong> You can save your progress as a draft and complete these fields later.</small>
                    </p>
                </div>
            `;
            
            form.insertAdjacentHTML('beforebegin', summaryHtml);
            
            document.getElementById('validation-summary').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    
        // ============================================
        // FILE VALIDATION
        // ============================================
        
        const fileInputs = document.querySelectorAll('input[type="file"]');
        const feedbackDiv = document.getElementById('file-validation-feedback');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                validateFiles(this);
            });
        });
        
        function validateFiles(input) {
            const files = input.files;
            const maxSize = parseInt(input.dataset.maxSize) || 5242880;
            const feedback = [];
            
            if (!files || files.length === 0) return;
            
            Array.from(files).forEach((file) => {
                const errors = [];
                
                if (file.size > maxSize) {
                    errors.push(`File "${file.name}" is too large (${formatFileSize(file.size)}). Maximum allowed: ${formatFileSize(maxSize)}`);
                }
                
                if (!file.name || file.name.trim() === '') {
                    errors.push('Invalid file: File name is empty');
                }
                
                const allowedExtensions = input.accept.split(',').map(ext => ext.trim().toLowerCase());
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                
                if (allowedExtensions.length > 0 && !allowedExtensions.includes(fileExtension)) {
                    errors.push(`File "${file.name}" has unsupported format. Allowed: ${allowedExtensions.join(', ')}`);
                }
                
                if (errors.length > 0) {
                    feedback.push(...errors);
                    input.value = '';
                }
            });
            
            if (feedback.length > 0) {
                feedbackDiv.innerHTML = '<div class="alert alert-danger"><ul class="mb-0">' + 
                    feedback.map(msg => `<li>${msg}</li>`).join('') + '</ul></div>';
            } else {
                feedbackDiv.innerHTML = '';
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    
        // ============================================
        // EDUCATION FIELD VISIBILITY TOGGLING
        // ============================================
        
        function toggleEducationFields(container) {
            const statusSelect = container.querySelector('select[name*="[status]"]');
            if (!statusSelect) return;
    
            const endDateField = container.querySelector('input[name*="[end_date]"]')?.closest('.form-group');
            const expectedGradField = container.querySelector('input[name*="[expected_graduation]"]')?.closest('.form-group');
    
            function updateVisibility() {
                if (!endDateField || !expectedGradField) return;
    
                const status = statusSelect.value;
    
                if (status === "Completed") {
                    endDateField.style.display = "block";
                    expectedGradField.style.display = "none";
                } else if (status === "In Progress") {
                    endDateField.style.display = "none";
                    expectedGradField.style.display = "block";
                } else {
                    endDateField.style.display = "none";
                    expectedGradField.style.display = "none";
                }
            }
    
            statusSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        }
    
        // Apply to all existing education entries
        document.querySelectorAll('#education-container .entry-block').forEach(toggleEducationFields);
    
        // Hook into addEntry function
        const originalAddEntry = window.addEntry;
        if (originalAddEntry) {
            window.addEntry = function(section) {
                originalAddEntry(section);
    
                if (section === 'education') {
                    const container = document.querySelector('#education-container .entry-block:last-child');
                    if (container) {
                        toggleEducationFields(container);
                    }
                }
            };
        }
    });
    
    // ============================================
    // DYNAMIC ENTRY MANAGEMENT
    // ============================================
    
    function addEntry(section) {
        const container = document.getElementById(section + '-container');
        if (!container) return;
        
        const entries = container.querySelectorAll('.entry-block');
        if (entries.length === 0) return;
        
        const newIndex = entries.length;
        const template = entries[0].cloneNode(true);
        
        template.dataset.index = newIndex;
        const titleElement = template.querySelector('h6');
        if (titleElement) {
            titleElement.textContent = titleElement.textContent.replace(/#\d+/, '#' + (newIndex + 1));
        }
        
        // Update form field names and clear values
        template.querySelectorAll('input, select, textarea').forEach(field => {
            const name = field.getAttribute('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, '[' + newIndex + ']');
                field.setAttribute('name', newName);
                
                // Update onchange attribute for custom select fields
                if (field.classList.contains('custom-select-field')) {
                    const onchangeAttr = field.getAttribute('onchange');
                    if (onchangeAttr) {
                        const newOnchange = onchangeAttr.replace(/'-\d+-/, "'-" + newIndex + "-");
                        field.setAttribute('onchange', newOnchange);
                    }
                }
                
                // Update custom input IDs
                if (field.classList.contains('custom-field-input')) {
                    const oldId = field.getAttribute('id');
                    if (oldId) {
                        const newId = oldId.replace(/-\d+-/, '-' + newIndex + '-');
                        field.setAttribute('id', newId);
                    }
                }
                
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else {
                    field.value = '';
                }
                
                // Hide custom input fields by default
                if (field.classList.contains('custom-field-input')) {
                    field.style.display = 'none';
                }
            }
        });
        
        // Add remove button if not present
        const headerDiv = template.querySelector('.d-flex');
        if (headerDiv && !template.querySelector('.btn-danger')) {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm';
            removeBtn.innerHTML = '<i class="fa fa-trash"></i>';
            removeBtn.onclick = function() { removeEntry(this); };
            headerDiv.appendChild(removeBtn);
        }
        
        container.appendChild(template);
    }
    
    function removeEntry(button) {
        const entryBlock = button.closest('.entry-block');
        if (entryBlock) {
            entryBlock.remove();
            
            const container = entryBlock.closest('[id$="-container"]');
            if (container) {
                const entries = container.querySelectorAll('.entry-block');
                entries.forEach((entry, index) => {
                    entry.dataset.index = index;
                    const titleElement = entry.querySelector('h6');
                    if (titleElement) {
                        titleElement.textContent = titleElement.textContent.replace(/#\d+/, '#' + (index + 1));
                    }
                    
                    entry.querySelectorAll('input, select, textarea').forEach(field => {
                        const name = field.getAttribute('name');
                        if (name) {
                            field.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                        }
                        
                        // Update onchange for custom fields
                        if (field.classList.contains('custom-select-field')) {
                            const onchangeAttr = field.getAttribute('onchange');
                            if (onchangeAttr) {
                                const sectionName = onchangeAttr.match(/'([^-]+)-/)[1];
                                const fieldName = onchangeAttr.match(/-([^']+)'/)[1];
                                field.setAttribute('onchange', `toggleCustomField(this, '${sectionName}-${index}-${fieldName}')`);
                            }
                        }
                        
                        // Update IDs for custom input fields
                        if (field.classList.contains('custom-field-input')) {
                            const oldId = field.getAttribute('id');
                            if (oldId) {
                                const parts = oldId.split('-');
                                if (parts.length >= 4) {
                                    parts[2] = index;
                                    field.setAttribute('id', parts.join('-'));
                                }
                            }
                        }
                    });
                });
            }
        }
    }
    </script>
@endsection