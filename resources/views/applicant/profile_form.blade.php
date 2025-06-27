@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Complete Your Profile</h3>
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
                                       value="{{ old('first_name', $user->profile->first_name ?? '') }}">
                                @error('first_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required 
                                       value="{{ old('last_name', $user->profile->last_name ?? '') }}">
                                @error('last_name')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" name="phone" class="form-control" required 
                                       value="{{ old('phone', $user->profile->phone ?? '') }}">
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
                                    <option value="{{ $location }}" {{ old('location', $user->profile->district ?? '') == $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                </select>
                                @error('location')<small class="form-text text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Sections -->
            @foreach([
                'education' => ['title' => 'Education', 'required' => true, 'fields' => [
                    'degree' => ['type' => 'text', 'label' => 'Degree', 'required' => true, 'placeholder' => 'e.g., Bachelor of Science'],
                    'education_level' => ['type' => 'select', 'label' => 'Education Level', 'required' => true, 'options' => ['High School', 'Certificate', 'Diploma', 'Associate Degree', 'Bachelor\'s Degree', 'Postgraduate Diploma', 'Master\'s Degree', 'Doctorate (PhD)', 'Other']],
                    'field_of_study' => ['type' => 'text', 'label' => 'Field of Study', 'placeholder' => 'e.g., Computer Science'],
                    'institution' => ['type' => 'text', 'label' => 'Institution', 'required' => true, 'placeholder' => 'e.g., National University of Lesotho'],
                    'start_date' => ['type' => 'date', 'label' => 'Start Date'],
                    'end_date' => ['type' => 'date', 'label' => 'End Date', 'note' => 'Leave blank if currently studying']
                ]],
                'experiences' => ['title' => 'Work Experience', 'fields' => [
                    'job_title' => ['type' => 'text', 'label' => 'Job Title', 'required' => true, 'placeholder' => 'e.g., Software Developer'],
                    'company' => ['type' => 'text', 'label' => 'Company', 'required' => true, 'placeholder' => 'e.g., ABC Technologies'],
                    'description' => ['type' => 'textarea', 'label' => 'Description', 'placeholder' => 'Describe your role, responsibilities and achievements...', 'rows' => 3],
                    'start_date' => ['type' => 'date', 'label' => 'Start Date', 'required' => true],
                    'end_date' => ['type' => 'date', 'label' => 'End Date', 'note' => 'Leave blank if currently employed']
                ]],
                'qualifications' => ['title' => 'Professional Qualifications & Certifications', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title', 'required' => true, 'placeholder' => 'e.g., Microsoft Azure Certified'],
                    'type' => ['type' => 'select', 'label' => 'Type', 'required' => true, 'options' => ['Certification', 'License', 'Professional Qualification', 'Award', 'Other']],
                    'institution' => ['type' => 'text', 'label' => 'Institution/Organization', 'required' => true, 'placeholder' => 'e.g., Microsoft, Cisco, PMI'],
                    'issued_date' => ['type' => 'date', 'label' => 'Issued Date'],
                    'notes' => ['type' => 'textarea', 'label' => 'Notes', 'placeholder' => 'Additional details about this qualification...', 'rows' => 2]
                ]],
                'references' => ['title' => 'References', 'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Full Name', 'required' => true, 'placeholder' => 'e.g., John Doe'],
                    'relationship' => ['type' => 'text', 'label' => 'Relationship', 'required' => true, 'placeholder' => 'e.g., Former Manager, Colleague'],
                    'email' => ['type' => 'email', 'label' => 'Email', 'required' => true, 'placeholder' => 'reference@company.com'],
                    'phone' => ['type' => 'tel', 'label' => 'Phone', 'placeholder' => '+266 xxxx xxxx']
                ]]
            ] as $section => $config)
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <span>{{ $config['title'] }} {{ isset($config['required']) ? '*' : '' }}</span>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addEntry('{{ $section }}')">
                            <i class="fa fa-plus"></i> Add {{ rtrim($config['title'], 's') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="{{ $section }}-container">
                        @php
                            $items = old($section) ?? ($user->{$section} ?? []);
                            if (empty($items)) $items = [null];
                        @endphp
                        @foreach($items as $index => $item)
                        @php $item = (object) $item; @endphp
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
                                <div class="col-md-{{ in_array($fieldConfig['type'], ['textarea']) || $field == 'institution' ? '12' : '6' }}">
                                    <div class="form-group">
                                        <label>{{ $fieldConfig['label'] }} {{ isset($fieldConfig['required']) ? '*' : '' }}</label>
                                        @if($fieldConfig['type'] == 'select')
                                            <select name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-select" {{ isset($fieldConfig['required']) ? 'required' : '' }}>
                                                <option value="">-- Select {{ $fieldConfig['label'] }} --</option>
                                                @foreach($fieldConfig['options'] as $option)
                                                <option value="{{ $option }}" {{ ($item->{$field} ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fieldConfig['type'] == 'textarea')
                                            <textarea name="{{ $section }}[{{ $index }}][{{ $field }}]" class="form-control" 
                                                      rows="{{ $fieldConfig['rows'] ?? 3 }}" 
                                                      placeholder="{{ $fieldConfig['placeholder'] ?? '' }}">{{ $item->{$field} ?? '' }}</textarea>
                                        @else
                                            <input type="{{ $fieldConfig['type'] }}" name="{{ $section }}[{{ $index }}][{{ $field }}]" 
                                                   class="form-control" value="{{ $item->{$field} ?? '' }}" 
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
                </div>
                <div class="card-body">
                    @php
                        $skills = old('skills') ? (is_string(old('skills')) ? explode(',', old('skills')) : old('skills')) : 
                                 ($user->skills()->exists() ? $user->skills()->pluck('name')->toArray() : []);
                    @endphp
                    <div id="skills-container" class="mb-2">
                        @foreach($skills as $skill)
                        <span class="badge badge-primary me-1 mb-1">
                            {{ trim($skill) }} <span class="ms-1" style="cursor:pointer" onclick="removeSkill(this)">×</span>
                        </span>
                        @endforeach
                    </div>
                    <input type="text" id="skill-input" class="form-control" placeholder="Type skills separated by commas">
                    <input type="hidden" name="skills" id="skills-hidden" value="{{ implode(',', $skills) }}">
                    @error('skills')<small class="form-text text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <!-- Documents -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Upload Supporting Documents</div>
                </div>
                <div class="card-body">
                    @php $attachments = $user->attachments->groupBy('type'); @endphp
                    @foreach(['resume' => 'CV / Resume', 'cover_letter' => 'Cover Letter', 'transcripts' => 'Transcripts'] as $key => $label)
                    <div class="form-group">
                        <label>{{ $label }} (PDF, DOCX, max 5MB)</label>
                        <input type="file" name="{{ $key }}" class="form-control" accept=".pdf,.doc,.docx">
                        @if(isset($attachments[$key]) && count($attachments[$key]))
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
                        @if(isset($attachments['other']) && count($attachments['other']))
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

    window.removeSkill = function(element) {
        element.parentElement.remove();
        updateHiddenInput();
    }

    function updateHiddenInput() {
        const skills = Array.from(skillsContainer.querySelectorAll('.badge')).map(b => b.textContent.replace('×', '').trim());
        hiddenInput.value = skills.join(',');
    }
});

// Dynamic entry management
function addEntry(section) {
    const container = document.getElementById(section + '-container');
    const entries = container.querySelectorAll('.entry-block');
    const newIndex = entries.length;
    const template = entries[0].cloneNode(true);
    
    template.dataset.index = newIndex;
    template.querySelector('h6').textContent = template.querySelector('h6').textContent.replace(/#\d+/, '#' + (newIndex + 1));
    
    // Update form field names and clear values
    template.querySelectorAll('input, select, textarea').forEach(field => {
        const name = field.getAttribute('name');
        if (name) {
            field.setAttribute('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
            field.value = '';
        }
    });
    
    // Add remove button if not present
    if (!template.querySelector('.btn-danger')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm';
        removeBtn.innerHTML = '<i class="fa fa-trash"></i>';
        removeBtn.onclick = function() { removeEntry(this); };
        template.querySelector('.d-flex').appendChild(removeBtn);
    }
    
    container.appendChild(template);
}

function removeEntry(button) {
    button.closest('.entry-block').remove();
}
</script>
@endsection