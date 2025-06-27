<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all(); // All roles
        $departments = Department::all();
    
        // For reviewer applications, eager load what you need (simplified example)
        $applications = JobApplication::with('jobRequisition')->get(); 
    
        return view('users.create', compact('roles', 'departments', 'applications'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'role' => 'required|string|exists:roles,name',
            'departments' => 'array',
            'departments.*' => 'exists:departments,id',
            'applications' => 'array',
            'applications.*' => 'exists:job_applications,id',
        ]);
    
        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make(Str::random(10)), // Temporary password
            ]);
    
            $role = Role::where('name', $validated['role'])->first();
            $user->role()->associate($role)->save();
    
            if (strcasecmp($role->name, 'Manager') === 0 && !empty($validated['departments'])) {
                $user->departments()->sync($validated['departments']);
            }
    
            if (strcasecmp($role->name, 'Reviewer') === 0 && !empty($validated['applications'])) {
                $user->reviewApplications()->sync($validated['applications']);
            }
    
            // Send password reset link if email is provided
            if ($user->email) {
                Password::sendResetLink(['email' => $user->email]);
            }
        });
    
        return redirect()->route('users.index')->with('success', 'User created successfully. A password reset link has been sent if an email was provided.');
    }

    public function resendPasswordReset(User $user)
{
    if (!$user->email) {
        return back()->with('error', 'User does not have an email address.');
    }

    Password::sendResetLink(['email' => $user->email]);

    return back()->with('success', 'Password reset link sent to ' . $user->email);
}
    
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
