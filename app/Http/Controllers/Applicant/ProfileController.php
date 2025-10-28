<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\ApplicantSkill;
use App\Models\ApplicantEducation;
use App\Models\User;
use App\Models\ApplicantExperience;
use App\Models\JobRequisition;
use App\Models\ApplicantReferences;
use App\Models\ApplicantQualifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ApplicationAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const ALLOWED_FILE_TYPES = [
        'resume' => ['pdf', 'doc', 'docx'],
        'cover_letter' => ['pdf', 'doc', 'docx'],
        'portfolio' => ['pdf', 'zip', 'rar', 'tar'],
        'transcripts' => ['pdf']
    ];

    private const MAX_FILE_SIZE = 10240; // 10MB in KB

    public function index()
    {
        return redirect()->route('profile.create');
    }

    public function create()
    {
        $user = Auth::user();
        $jobRequisitions = JobRequisition::all();

        // Collect all unique skills from all job requisitions
        $jobRequisitionSkills = [];
        foreach($jobRequisitions as $jobRequisition) {
            if ($jobRequisition->skills && $jobRequisition->skills->isNotEmpty()) {
                $skills = $jobRequisition->skills->pluck('name')->toArray();
                $jobRequisitionSkills = array_merge($jobRequisitionSkills, $skills);
            } 
        }
        // Remove duplicates
        $jobRequisitionSkills = array_unique($jobRequisitionSkills);

        $profile = $user->profile ?? new ApplicantProfile();
        $skills = $user->skills()->get();
        $education = $user->education()->orderBy('start_date', 'desc')->get();
        $experience = $user->experiences()->orderBy('start_date', 'desc')->get();
        $references = $user->references()->get();
        $qualifications = $user->qualifications()->orderBy('issued_date', 'desc')->get();
        $attachments = $user->attachments()->get()->groupBy('type');
        session(['return_to_application' => url()->current()]);

        return view('applicant.update', compact(
            'profile', 'skills', 'education', 'experience', 'references', 
            'qualifications', 'attachments', 'user', 'jobRequisitionSkills'
        ));
    }

    /**
     * Store a new profile (handles both draft and final submission)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isDraft = $request->has('save_draft') && $request->input('save_draft') == '1';
        
        // Check if profile already exists
        if ($user->profile) {
            // Profile exists, redirect to update
            return $this->update($request);
        }

        // Validate based on whether it's a draft or final submission
        $validated = $this->validateProfileData($request, $isDraft, false);

        try {
            DB::beginTransaction();

            // Create new profile
            $profileData = [
                'user_id' => $user->id,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'district' => $validated['location'] ?? null,
                'is_draft' => $isDraft,
                'completed_at' => $isDraft ? null : now(),
            ];

            $profile = ApplicantProfile::create($profileData);

            // Save related data
            $this->saveProfileRelatedData($user, $validated, $request);

            DB::commit();

            $message = $isDraft ? 'Profile saved as draft successfully!' : 'Profile submitted successfully!';
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Profile creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['resume', 'cover_letter', 'transcripts', 'other_documents'])
            ]);
            
            // In development, show the actual error
            if (config('app.debug')) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
            }
        
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Sorry, something went wrong while creating your profile. Please try again or contact support.');
        }
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update existing profile (handles both draft and final submission)
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $isDraft = $request->has('save_draft') && $request->input('save_draft') == '1';
        
        // Validate based on whether it's a draft or final submission
        $validated = $this->validateProfileData($request, $isDraft, true);

        try {
            DB::beginTransaction();

            // Check if profile exists before updating
            if (!$user->profile) {
                return $this->store($request);
            }

            // Update profile
            $profileData = [
                'first_name' => $validated['first_name'] ?? $user->profile->first_name,
                'last_name' => $validated['last_name'] ?? $user->profile->last_name,
                'phone' => $validated['phone'] ?? $user->profile->phone,
                'district' => $validated['location'] ?? $user->profile->district,
                'is_draft' => $isDraft,
                'completed_at' => $isDraft ? $user->profile->completed_at : now(),
            ];

            $user->profile()->update($profileData);

            // Update related data
            $this->saveProfileRelatedData($user, $validated, $request);

            DB::commit();

            $message = $isDraft ? 'Profile draft updated successfully!' : 'Profile submitted successfully!';
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['resume', 'cover_letter', 'transcripts', 'other_documents'])
            ]);
            
            // In development, show the actual error
            if (config('app.debug')) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
            }
        
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Sorry, something went wrong while updating your profile. Please try again or contact support.');
        }
    }

    /**
     * Validate profile data based on draft vs final submission
     */
    private function validateProfileData(Request $request, bool $isDraft, bool $isUpdate)
    {
        // Base validation rules
        $rules = [
            // Documents
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'transcripts' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    
        $messages = [];
    
        if ($isDraft) {
            // Draft validation - more lenient
            $rules = array_merge($rules, [
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'location' => 'nullable|string|in:Maseru,Berea,Butha-Buthe,Leribe,Mafeteng,Mohale\'s Hoek,Mokhotlong,Qacha\'s Nek,Quthing,Thaba-Tseka',
                'education' => 'nullable|array',
                'education.*.degree' => 'nullable|string|max:255',
                // FIXED: Added 'custom' as a valid option
                'education.*.education_level' => 'nullable|string|max:255',
                'education.*.custom_education_level' => 'nullable|string|max:255',
                'education.*.field_of_study' => 'nullable|string|max:255',
                'education.*.custom_field_of_study' => 'nullable|string|max:255',
                'education.*.institution' => 'nullable|string|max:255',
                'education.*.status' => 'nullable|string|in:Completed,In Progress,Paused/Deferred',
                'education.*.start_date' => 'nullable|date',
                'education.*.end_date' => 'nullable|date|after_or_equal:education.*.start_date',
                'education.*.expected_graduation' => 'nullable|date',
                // ... rest of validation rules
            ]);
        } else {
            // Final submission validation - strict
            $rules = array_merge($rules, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'location' => 'required|string|in:Maseru,Berea,Butha-Buthe,Leribe,Mafeteng,Mohale\'s Hoek,Mokhotlong,Qacha\'s Nek,Quthing,Thaba-Tseka',
                'education' => 'required|array|min:1',
                'education.*.degree' => 'required|string|max:255',
                // FIXED: Added 'custom' as a valid option
                'education.*.education_level' => 'nullable|string|max:255',
                'education.*.custom_education_level' => 'nullable|string|max:255',
                'education.*.field_of_study' => 'nullable|string|max:255',
                'education.*.custom_field_of_study' => 'nullable|string|max:255',
                'education.*.institution' => 'required|string|max:255',
                'education.*.status' => 'nullable|string|in:Completed,In Progress,Paused/Deferred',
                'education.*.start_date' => 'nullable|date',
                'education.*.end_date' => 'nullable|date|after_or_equal:education.*.start_date',
                'education.*.expected_graduation' => 'nullable|date',
                // ... rest of validation rules
            ]);
        }
    
        return $request->validate($rules, $messages);
    }
    /**
     * Save all profile related data
     */
    private function saveProfileRelatedData(User $user, array $validated, Request $request)
    {
        // Handle Education
        if (isset($validated['education']) && !empty($validated['education'])) {
            $this->updateEducation($user, $validated['education']);
        }

        // Handle Experience
        if (isset($validated['experiences']) && !empty($validated['experiences'])) {
            $this->updateExperience($user, $validated['experiences']);
        }

        // Handle Skills
        if (isset($validated['skills']) && !empty($validated['skills'])) {
            $skillsArray = $this->parseSkillsFromCsv($validated['skills']);
            $this->updateSkills($user, $skillsArray);
        }

        // Handle References
        if (isset($validated['references']) && !empty($validated['references'])) {
            $this->syncReferences($user, $validated['references']);
        }

        // Handle Qualifications
        if (isset($validated['qualifications']) && !empty($validated['qualifications'])) {
            $this->syncQualifications($user, $validated['qualifications']);
        }

        // Handle File Uploads
        $this->handleFileUploads($user, $request);
    }

    /**
     * Parse skills from CSV string to array
     */
    private function parseSkillsFromCsv(string $skillsCsv): array
    {
        if (empty($skillsCsv)) {
            return [];
        }

        $skills = array_map('trim', explode(',', $skillsCsv));
        return array_unique(array_filter($skills, function($skill) {
            return !empty($skill);
        }));
    }

    private function handleFileUploads(User $user, Request $request)
    {
        $singleTypes = ['resume', 'cover_letter', 'transcripts'];
        $multiType = 'other';
        
        foreach ($singleTypes as $type) {
            if ($request->hasFile($type) && $request->file($type)->isValid()) {
                $file = $request->file($type);
                
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                if (empty($originalName) || empty($extension)) {
                    continue;
                }
                
                $existing = $user->attachments()->where('type', $type)->first();
                if ($existing) {
                    Storage::disk('public')->delete($existing->file_path);
                    $existing->delete();
                }
                
                $filename = $user->id . '_' . $type . '_' . time() . '.' . $extension;
                
                try {
                    $filePath = $file->storeAs('applicant-documents', $filename, 'public');
                    
                    if ($filePath) {
                        $user->attachments()->create([
                            'type' => $type,
                            'original_name' => $originalName,
                            'file_path' => $filePath,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('File upload failed: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'type' => $type,
                        'original_name' => $originalName
                    ]);
                }
            }
        }
        
        if ($request->hasFile('other_documents')) {
            foreach ($request->file('other_documents') as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    
                    if (empty($originalName) || empty($extension)) {
                        continue;
                    }
                    
                    $filename = $user->id . '_other_' . uniqid() . '.' . $extension;
                    
                    try {
                        $filePath = $file->storeAs('applicant-documents', $filename, 'public');
                        
                        if ($filePath) {
                            $user->attachments()->create([
                                'type' => $multiType,
                                'original_name' => $originalName,
                                'file_path' => $filePath,
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('File upload failed: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'type' => $multiType,
                            'original_name' => $originalName
                        ]);
                    }
                }
            }
        }
    }

    protected function updateSkills(User $user, array $skills)
    {
        $user->skills()->delete();

        foreach ($skills as $skillName) {
            ApplicantSkill::firstOrCreate([
                'user_id' => $user->id,
                'name'    => trim($skillName),
            ]);
        }
    }

    private function updateEducation(User $user, array $educations)
    {
        $user->education()->delete();

        foreach ($educations as $educationData) {
            if (empty($educationData['degree']) || empty($educationData['institution'])) {
                continue;
            }

            // Handle custom field of study
            $fieldOfStudy = $educationData['field_of_study'] ?? null;
            if ($fieldOfStudy === 'custom' && !empty($educationData['custom_field_of_study'])) {
                $fieldOfStudy = $educationData['custom_field_of_study'];
            }

            // Handle custom education level
            $educationLevel = $educationData['education_level'] ?? null;
            if ($educationLevel === 'custom' && !empty($educationData['custom_education_level'])) {
                $educationLevel = $educationData['custom_education_level'];
            }

            $user->education()->create([
                'degree' => $educationData['degree'],
                'education_level' => $educationLevel,
                'field_of_study' => $fieldOfStudy,
                'status' => $educationData['status'] ?? 'Completed',
                'institution' => $educationData['institution'],
                'start_date' => $educationData['start_date'] ?? null,
                'end_date' => $educationData['end_date'] ?? null,
                'expected_graduation' => $educationData['expected_graduation'] ?? null,
            ]);
        }
    }

    private function updateExperience(User $user, array $experiences)
    {
        $user->experiences()->delete();

        foreach ($experiences as $experienceData) {
            if (empty($experienceData['job_title']) || empty($experienceData['company'])) {
                continue;
            }

            $user->experiences()->create([
                'job_title' => $experienceData['job_title'],
                'company' => $experienceData['company'],
                'description' => $experienceData['description'] ?? null,
                'start_date' => $experienceData['start_date'],
                'end_date' => $experienceData['end_date'] ?? null,
            ]);
        }
    }

    protected function syncReferences($user, $references)
    {
        $user->references()->delete();

        $referencesToCreate = [];
        foreach ($references as $ref) {
            if (!empty($ref['name']) && !empty($ref['email'])) {
                $referencesToCreate[] = [
                    'user_id' => $user->id,
                    'name' => trim($ref['name']),
                    'relationship' => trim($ref['relationship'] ?? ''),
                    'email' => trim($ref['email']),
                    'phone' => trim($ref['phone'] ?? ''),
                    'context' => trim($ref['context'] ?? ''),
                    'notes' => $ref['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($referencesToCreate)) {
            ApplicantReferences::insert($referencesToCreate);
        }
    }

    protected function syncQualifications($user, $qualifications)
    {
        $user->qualifications()->delete();

        $qualificationsToCreate = [];
        foreach ($qualifications as $qual) {
            // Make qualifications completely optional
            if (!empty($qual['title']) || !empty($qual['institution'])) {
                $qualificationsToCreate[] = [
                    'user_id' => $user->id,
                    'title' => trim($qual['title'] ?? ''),
                    'type' => trim($qual['type'] ?? 'Certification'),
                    'institution' => trim($qual['institution'] ?? ''),
                    'issued_date' => $qual['issued_date'] ?? null,
                    'notes' => $qual['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($qualificationsToCreate)) {
            ApplicantQualifications::insert($qualificationsToCreate);
        }
    }
}