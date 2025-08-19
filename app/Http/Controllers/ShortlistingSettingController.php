<?php
// app/Http/Controllers/ShortlistingSettingController.php
namespace App\Http\Controllers;

use App\Models\ShortlistingSetting;
use Illuminate\Http\Request;

class ShortlistingSettingController extends Controller
{
    public function index()
    {
        $setting = ShortlistingSetting::first();
        return view('shortlisting-settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'skills_weight' => 'required|numeric|min:0|max:100',
            'experience_weight' => 'required|numeric|min:0|max:100',
            'education_weight' => 'required|numeric|min:0|max:100',
            'qualification_bonus' => 'required|numeric|min:0|max:100',
            'threshold' => 'required|numeric|min:0|max:100', // added threshold
        ]);

        $total = $request->skills_weight + $request->experience_weight + $request->education_weight;
        if ($total !== 100) {
            return back()->withErrors(['total' => 'Skills, Experience and Education must sum up to 100%.'])->withInput();
        }

        $setting = ShortlistingSetting::first();
        $setting->update($request->only([
            'skills_weight', 
            'experience_weight', 
            'education_weight', 
            'qualification_bonus',
            'threshold' // update threshold
        ]));

        return redirect()->route('shortlisting-settings.index')->with('success', 'Shortlisting settings updated.');
    }
}
