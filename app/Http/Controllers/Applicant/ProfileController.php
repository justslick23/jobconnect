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

        foreach($jobRequisitions as $jobRequisition) {
            $jobRequisitionSkills = [];
           if ($jobRequisition->skills && $jobRequisition->skills->isNotEmpty()) {
        $jobRequisitionSkills = $jobRequisition->skills->pluck('name')->toArray();
    } 
}

        $profile = $user->profile ?? new ApplicantProfile();
        $skills = $user->skills()->get();
        $education = $user->education()->orderBy('start_date', 'desc')->get();
        $experience = $user->experiences()->orderBy('start_date', 'desc')->get();
        $references = $user->references()->get();
        $qualifications = $user->qualifications()->orderBy('issued_date', 'desc')->get();
        $attachments = $user->attachments()->get()->groupBy('type');
        session(['return_to_application' => url()->current()]);

        return view('applicant.update', compact(
            'profile', 'skills', 'education', 'experience', 'references', 'qualifications', 'attachments', 'user', 'jobRequisitionSkills'
        ));
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('info', 'Use the update form to submit your profile.');
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function edit(string $id)
    {
        abort(404);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location' => 'required|string|in:Maseru,Berea,Butha-Buthe,Leribe,Mafeteng,Mohale\'s Hoek,Mokhotlong,Qacha\'s Nek,Quthing,Thaba-Tseka',
    
            // Education (at least one required) - CHANGED FROM 'educations' to 'education'
            'education' => 'required|array|min:1',
            'education.*.degree' => 'required|string|max:255',
            'education.*.education_level' => 'required|string|in:High School,Certificate,Diploma,Associate Degree,Bachelor\'s Degree,Postgraduate Diploma,Master\'s Degree,Doctorate (PhD),Other',
            'education.*.field_of_study' => 'nullable|string|max:255',
            'education.*.institution' => 'required|string|max:255',
            'education.*.start_date' => 'nullable|date',
            'education.*.end_date' => 'nullable|date|after_or_equal:education.*.start_date',
    
            // Experience (optional) - CHANGED FROM 'experiences' to 'experiences'
            'experiences' => 'nullable|array',
            'experiences.*.job_title' => 'required_with:experiences|string|max:255',
            'experiences.*.company' => 'required_with:experiences|string|max:255',
            'experiences.*.description' => 'nullable|string',
            'experiences.*.start_date' => 'required_with:experiences|date',
            'experiences.*.end_date' => 'nullable|date|after_or_equal:experiences.*.start_date',
    
            // Skills
            'skills' => 'required|string|min:1',
    
            // References (optional)
            'references' => 'nullable|array',
            'references.*.name' => 'required_with:references|string|max:255',
            'references.*.email' => 'required_with:references|email|max:255',
            'references.*.relationship' => 'nullable|string|max:255',
            'references.*.phone' => 'nullable|string|max:20',
    
            // Qualifications (optional)
           // Qualifications (all optional)
            'qualifications' => 'nullable|array',
            'qualifications.*.title' => 'nullable|string|max:255',
            'qualifications.*.type' => 'nullable|string|max:255',
            'qualifications.*.institution' => 'nullable|string|max:255',
            'qualifications.*.issued_date' => 'nullable|date',
            'qualifications.*.notes' => 'nullable|string',

    
            // Documents
            'resume'          => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter'    => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'transcripts'     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ], [
            // Custom error messages
            'education.required' => 'At least one education entry is required.',
            'education.min' => 'At least one education entry is required.',
            'education.*.degree.required' => 'Degree is required for all education entries.',
            'education.*.education_level.required' => 'Education level is required for all education entries.',
            'education.*.institution.required' => 'Institution is required for all education entries.',
        ]);
    
        $user = Auth::user();
    
        try {
            DB::beginTransaction();
    
            // Update or create profile
            $profileData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'district' => $validated['location'],
            ];
    
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
    
            // Handle Education - CHANGED FROM 'educations' to 'education'
            $this->updateEducation($user, $validated['education']);
    
            // Handle Experience
            if (isset($validated['experiences']) && !empty($validated['experiences'])) {
                $this->updateExperience($user, $validated['experiences']);
            } else {
                $user->experiences()->delete();
            }
    
            // Handle Skills
            $skillsArray = $this->parseSkillsFromCsv($validated['skills']);
            $this->updateSkills($user, $skillsArray);
    
            // Handle References
            if (isset($validated['references']) && !empty($validated['references'])) {
                $this->syncReferences($user, $validated['references']);
            } else {
                $user->references()->delete();
            }
    
            // Handle Qualifications
            if (isset($validated['qualifications']) && !empty($validated['qualifications'])) {
                $this->syncQualifications($user, $validated['qualifications']);
            } else {
                $user->qualifications()->delete();
            }
    
            // Handle File Uploads
            $this->handleFileUploads($user, $request);
    
            DB::commit();
    
            return redirect()
                ->back()
                ->with('success', 'Profile updated successfully!');
    
        } catch (\Exception $e) {
            DB::rollBack();
        
            \Log::error('Profile update failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
        
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Sorry, something went wrong while updating your profile. Please try again or contact support.');
        }
    }

    /**
     * Parse skills from CSV string to array
     */
    private function parseSkillsFromCsv(string $skillsCsv): array
    {
        if (empty($skillsCsv)) {
            return [];
        }

        // Split by comma and clean up each skill
        $skills = array_map('trim', explode(',', $skillsCsv));
        
        // Remove empty skills and return unique values
        return array_unique(array_filter($skills, function($skill) {
            return !empty($skill);
        }));
    }

private function handleFileUploads(User $user, Request $request)
{
    // Document types that should only keep the latest upload
    $singleTypes = ['resume', 'cover_letter', 'transcripts'];
    // Type for multiple supporting documents
    $multiType = 'other';
    
    // === Handle single uploads (replace existing) ===
    foreach ($singleTypes as $type) {
        if ($request->hasFile($type) && $request->file($type)->isValid()) {
            $file = $request->file($type);
            
            // More robust validation
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            if (empty($originalName) || empty($extension)) {
                continue; // Skip this file if name or extension is empty
            }
            
            // Delete existing file if present
            $existing = $user->attachments()->where('type', $type)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }
            
            // Generate filename with fallback
            $filename = $user->id . '_' . $type . '_' . time() . '.' . $extension;
            
            try {
                $filePath = $file->storeAs(
                    'applicant-documents',
                    $filename,
                    'public'
                );
                
                if ($filePath) {
                    // Save DB record
                    $user->attachments()->create([
                        'type' => $type,
                        'original_name' => $originalName,
                        'file_path' => $filePath,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error or handle as needed
                \Log::error('File upload failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'type' => $type,
                    'original_name' => $originalName
                ]);
            }
        }
    }
    
    // === Handle multiple supporting documents ===
    if ($request->hasFile('other_documents')) {
        foreach ($request->file('other_documents') as $file) {
            if ($file && $file->isValid()) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                if (empty($originalName) || empty($extension)) {
                    continue; // Skip this file
                }
                
                $filename = $user->id . '_other_' . uniqid() . '.' . $extension;
                
                try {
                    $filePath = $file->storeAs(
                        'applicant-documents',
                        $filename,
                        'public'
                    );
                    
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

    // Sync Methods
    protected function updateSkills(User $user, array $skills)
{
    // Optionally delete or leave existing — depends on your logic
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
        // Delete existing education records
        $user->education()->delete();

        // Create new education records
        foreach ($educations as $educationData) {
            // Skip empty entries
            if (empty($educationData['degree']) || empty($educationData['institution'])) {
                continue;
            }

            $user->education()->create([
                'degree' => $educationData['degree'],
                'education_level' => $educationData['education_level'],
                'field_of_study' => $educationData['field_of_study'] ?? null,
                'status' => $educationData['status'] ?? 'Completed', // Default to Completed

                'institution' => $educationData['institution'],
                'start_date' => $educationData['start_date'] ?? null,
                'end_date' => $educationData['end_date'] ?? null,
                'expected_graduation' => $educationData['expected_graduation'] ?? null,

            ]);
        }
    }

    private function updateExperience(User $user, array $experiences)
    {
        // Delete existing experience records
        $user->experiences()->delete();

        // Create new experience records
        foreach ($experiences as $experienceData) {
            // Skip empty entries
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
            if (!empty($qual['title']) && !empty($qual['institution'])) {
                $qualificationsToCreate[] = [
                    'user_id' => $user->id,
                    'title' => trim($qual['title']),
                    'type' => trim($qual['type'] ?? 'Certification'),
                    'institution' => trim($qual['institution']),
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