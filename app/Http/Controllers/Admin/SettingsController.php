<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
 
class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }
 
    public function update(Request $request)
    {
        $data = $request->validate([
            'university_name'   => 'required|string|max:255',
            'university_name_kh'=> 'nullable|string|max:255',
            'university_short'  => 'required|string|max:20',
            'academic_year'     => 'required|string|max:20',
            'pass_threshold'    => 'required|numeric|min:0|max:100',
            'reexam_threshold'  => 'required|numeric|min:0|max:100',
            'attendance_weeks'  => 'required|integer|min:1|max:52',
            'min_gpa_promotion' => 'required|numeric|min:0|max:4',
            'contact_email'     => 'nullable|email|max:255',
            'contact_phone'     => 'nullable|string|max:30',
            'address'           => 'nullable|string|max:500',
        ]);
 
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
 
        return back()->with('success', 'Settings saved successfully.');
    }
}