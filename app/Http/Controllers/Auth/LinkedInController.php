<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class LinkedInController extends Controller
{
    /**
     * Redirect to LinkedIn OAuth page
     */
    public function redirectToLinkedIn()
    {
        return Socialite::driver('linkedin')
            ->scopes(['r_liteprofile', 'r_emailaddress'])
            ->redirect();
    }

    /**
     * Handle LinkedIn OAuth callback
     */
    public function handleLinkedInCallback()
    {
        try {
            $linkedinUser = Socialite::driver('linkedin')->user();
            
            // Check if user already exists with this LinkedIn ID
            $user = User::where('linkedin_id', $linkedinUser->id)->first();
            
            if ($user) {
                // Update user info
                $user->update([
                    'name' => $linkedinUser->name,
                    'email' => $linkedinUser->email,
                    'avatar' => $linkedinUser->avatar,
                ]);
            } else {
                // Check if user exists with same email
                $existingUser = User::where('email', $linkedinUser->email)->first();
                
                if ($existingUser) {
                    // Link LinkedIn to existing account
                    $existingUser->update([
                        'linkedin_id' => $linkedinUser->id,
                        'avatar' => $linkedinUser->avatar ?? $existingUser->avatar,
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $linkedinUser->name,
                        'email' => $linkedinUser->email,
                        'linkedin_id' => $linkedinUser->id,
                        'avatar' => $linkedinUser->avatar,
                        'email_verified_at' => now(),
                        'account_type' => 'jobseeker', // Default to jobseeker
                    ]);
                }
            }
            
            Auth::login($user, true);
            
            return redirect()->intended('/dashboard')->with('success', 'Successfully logged in with LinkedIn!');
            
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'LinkedIn login failed. Please try again.');
        }
    }
}