<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeComponent;
use Illuminate\Http\Request;

class GradeComponentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id'     => 'required|exists:sections,id',
            'name'           => 'required|string|max:100',
            'weight_percent' => 'required|integer|min:1|max:100',
            'max_score'      => 'required|integer|min:1',
        ]);

        // Check total weight won't exceed 100
        $currentTotal = GradeComponent::where('section_id', $data['section_id'])
            ->sum('weight_percent');

        if ($currentTotal + $data['weight_percent'] > 100) {
            return back()->withErrors([
                'weight_percent' => "Total weight would be " . ($currentTotal + $data['weight_percent']) . "%. Cannot exceed 100%."
            ]);
        }

        GradeComponent::create($data);

        return back()->with('success', "Component '{$data['name']}' added ({$data['weight_percent']}%).");
    }

    public function destroy(GradeComponent $gradeComponent)
    {
        $gradeComponent->delete();
        return back()->with('success', 'Component deleted.');
    }
}