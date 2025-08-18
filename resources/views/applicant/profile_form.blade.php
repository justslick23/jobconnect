@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Complete Your Profile</h3>
            <p class="text-muted">Please provide information that is relevant to the position you're applying for. Focus on experiences, skills, and qualifications that demonstrate your suitability for the role.</p>
        </div>

        @include('partials.alerts')

        <form action="{{ route('applicant.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Personal Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" class="form-control" required 
                                       value="{{ old('first_name', optional($user->profile)->first_name) }}">
                                @error('first_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required 
                                       value="{{ old('last_name', optional($user->profile)->last_name) }}">
                                @error('last_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" name="phone" class="form-control" required 
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
                                <label>District *</label>
                                <select name="location" class="form-select" required>
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
                                'education_level' => ['type' => 'select', 'label' => 'Education Level', 'required' => true, 'options' => ['High School', 'Certificate', 'Diploma', 'Associate Degree', 'Bachelor\'s Degree', 'Postgraduate Diploma', 'Master\'s Degree', 'Doctorate (PhD)', 'Other']],
                                'field_of_study' => ['type' => 'text', 'label' => 'Field of Study', 'required' => true, 'placeholder' => 'e.g., Computer Science'],
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
                        'description' => 'Include certifications, licenses, and professional qualifications that are relevant to the position. These could be technical certifications, professional memberships, or industry-specific qualifications.',
                        'fields' => [
                            'title' => ['type' => 'text', 'label' => 'Title', 'required' => true, 'placeholder' => 'e.g., Microsoft Azure Certified'],
                            'type' => ['type' => 'select', 'label' => 'Type', 'required' => true, 'options' => ['Certification', 'License', 'Professional Qualification', 'Award', 'Other']],
                            'institution' => ['type' => 'text', 'label' => 'Issuing Organization', 'required' => true, 'placeholder' => 'e.g., Microsoft, Cisco, PMI'],
                            'issued_date' => ['type' => 'date', 'label' => 'Issued Date'],
                            'notes' => ['type' => 'textarea', 'label' => 'Relevance to Position', 'placeholder' => 'Explain how this qualification is relevant to the role you\'re applying for...', 'rows' => 2]
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
                        <span>{{ $config['title'] }} {{ isset($config['required']) ? '*' : '' }}</span>
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
                                        <label>{{ $fieldConfig['label'] }} {{ isset($fieldConfig['required']) ? '*' : '' }}</label>
                                        @if($fieldConfig['type'] == 'select')
                                            <select name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-select" {{ isset($fieldConfig['required']) ? 'required' : '' }}>
                                                <option value="">-- Select {{ $fieldConfig['label'] }} --</option>
                                                @foreach($fieldConfig['options'] as $option)
                                                <option value="{{ $option }}" {{ (isset($item->{$field}) ? $item->{$field} : '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldConfig['type'] == 'textarea')
                                            <textarea name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-control" 
                                                      rows="{{ $fieldConfig['rows'] ?? 3 }}" 
                                                      placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                                                      {{ isset($fieldConfig['required']) ? 'required' : '' }}>{{ isset($item->{$field}) ? $item->{$field} : '' }}</textarea>
                                        @else
                                            <input type="{{ $fieldConfig['type'] }}" name="{{ $section }}[{{ $index }}][{{ $field }}]" 
                                                   class="form-control" value="{{ isset($item->{$field}) ? $item->{$field} : '' }}" 
                                                   placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                                                   {{ isset($fieldConfig['required']) ? 'required' : '' }}>
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

            <!-- Skills -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Skills</div>
                    <div class="card-subtitle mt-2">
                        <small class="text-muted">Add skills that are relevant to the position you're applying for. Include both technical skills and soft skills that match the job requirements.</small>
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
                        $skills = array_filter($skills); // Remove empty values
                    @endphp
                    <div id="skills-container" class="mb-2">
                        @foreach($skills as $skill)
                        <span class="badge badge-primary me-1 mb-1">
                            {{ trim($skill) }} <span class="ms-1" style="cursor:pointer" onclick="removeSkill(this)">×</span>
                        </span>
                        @endforeach
                    </div>
                    <input type="text" id="skill-input" class="form-control" placeholder="Type relevant skills separated by commas (e.g., Project Management, Python, Leadership)">
                    <input type="hidden" name="skills" id="skills-hidden" value="{{ implode(',', $skills) }}">
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
                    
                    <!-- Resume/CV - Required -->
                    <div class="form-group">
                        <label>CV / Resume * (PDF, DOCX, max 5MB)</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" 
                               {{ !($attachments->has('resume') && $attachments['resume']->count() > 0) ? 'required' : '' }}>
                        <small class="form-text text-muted">Ensure your CV highlights relevant experience and achievements for the position you're applying for.</small>
                        @if($attachments->has('resume') && $attachments['resume']->count() > 0)
                            <small class="form-text text-muted">
                                Existing: <a href="{{ asset('storage/' . $attachments['resume'][0]->file_path) }}" target="_blank">{{ $attachments['resume'][0]->original_name }}</a>
                            </small>
                        @endif
                        @error('resume')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>
                    
                    <!-- Optional documents -->
                    @foreach(['cover_letter' => 'Cover Letter', 'transcripts' => 'Academic Transcripts'] as $key => $label)
                    <div class="form-group">
                        <label>{{ $label }} (PDF, DOCX, max 5MB)</label>
                        <input type="file" name="{{ $key }}" class="form-control" accept=".pdf,.doc,.docx">
                        @if($key == 'cover_letter')
                            <small class="form-text text-muted">A tailored cover letter explaining your interest and suitability for the specific position.</small>
                        @endif
                        @if($attachments->has($key) && $attachments[$key]->count() > 0)
                            <small class="form-text text-muted">
                                Existing: <a href="{{ asset('storage/' . $attachments[$key][0]->file_path) }}" target="_blank">{{ $attachments[$key][0]->original_name }}</a>
                            </small>
                        @endif
                        @error($key)<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>
                    @endforeach
                    
                    <div class="form-group">
                        <label>Other Supporting Documents (Multiple files allowed)</label>
                        <input type="file" name="other_documents[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.zip,.rar">
                        <small class="form-text text-muted">Additional documents relevant to your application (certifications, portfolios, etc.)</small>
                        @if($attachments->has('other') && $attachments['other']->count() > 0)
                            <small class="form-text text-muted">
                                Existing: 
                                @foreach($attachments['other'] as $doc)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">{{ $doc->original_name }}</a>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </small>
                        @endif
                        @error('other_documents.*')<small class="form-text text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Skills functionality
    const skillInput = document.getElementById('skill-input');
    const skillsContainer = document.getElementById('skills-container');
    const hiddenInput = document.getElementById('skills-hidden');

    if (skillInput && skillsContainer && hiddenInput) {
        skillInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addSkills(this.value);
                this.value = '';
            }
        });

        skillInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                addSkills(this.value);
                this.value = '';
            }
        });

        function addSkills(input) {
            const skills = input.split(',').map(s => s.trim()).filter(s => s);
            skills.forEach(skill => {
                if (!isSkillExists(skill)) {
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-primary me-1 mb-1';
                    badge.innerHTML = `${skill} <span class="ms-1" style="cursor:pointer" onclick="removeSkill(this)">×</span>`;
                    skillsContainer.appendChild(badge);
                }
            });
            updateHiddenInput();
        }

        function isSkillExists(skill) {
            const existing = Array.from(skillsContainer.querySelectorAll('.badge')).map(b => b.textContent.replace('×', '').trim());
            return existing.includes(skill);
        }

        function updateHiddenInput() {
            const skills = Array.from(skillsContainer.querySelectorAll('.badge')).map(b => b.textContent.replace('×', '').trim());
            hiddenInput.value = skills.join(',');
        }
    }

    // Make removeSkill globally available
    window.removeSkill = function(element) {
        element.parentElement.remove();
        updateHiddenInput();
    }

    // Make updateHiddenInput globally available for removeSkill
    window.updateHiddenInput = function() {
        const skills = Array.from(skillsContainer.querySelectorAll('.badge')).map(b => b.textContent.replace('×', '').trim());
        hiddenInput.value = skills.join(',');
    }
});

// Dynamic entry management
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
            field.setAttribute('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else {
                field.value = '';
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
        
        // Update entry numbers after removal
        const container = entryBlock.closest('[id$="-container"]');
        if (container) {
            const entries = container.querySelectorAll('.entry-block');
            entries.forEach((entry, index) => {
                entry.dataset.index = index;
                const titleElement = entry.querySelector('h6');
                if (titleElement) {
                    titleElement.textContent = titleElement.textContent.replace(/#\d+/, '#' + (index + 1));
                }
                
                // Update field names
                entry.querySelectorAll('input, select, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
            });
        }
    }
}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to toggle visibility of education fields based on status
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
            updateVisibility(); // run on load
        }
    
        // Apply to all existing education entries
        document.querySelectorAll('#education-container .entry-block').forEach(toggleEducationFields);
    
        // Hook into your existing addEntry function
        const originalAddEntry = window.addEntry;
        window.addEntry = function(section) {
            originalAddEntry(section);
    
            if (section === 'education') {
                const container = document.querySelector('#education-container .entry-block:last-child');
                toggleEducationFields(container);
            }
        }
    });
    </script>
    
@endsection