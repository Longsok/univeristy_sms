<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\{Program, Section, Course};
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StudentImportController extends Controller
{
    /**
     * Show the import page
     */
    public function index()
    {
        $programs = Program::with('department.faculty')
                        ->where('is_active', true)
                        ->get();

        $sections = Section::with('course.program', 'teacher.user')
                        ->get();

        return view('admin.students.import', compact('programs', 'sections'));
    }

    /**
     * Handle the uploaded Excel file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $import = new StudentsImport();

        Excel::import($import, $request->file('file'));

        $results = $import->results;

        return back()->with([
            'import_results' => $results,
            'success' => "Import complete: {$results['created']} students created, {$results['enrolled']} enrolled, {$results['skipped']} skipped.",
        ]);
    }

    /**
     * Download the Excel template
     */
    public function template()
    {
        // Generate template using simple CSV
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $columns = [
            'name',
            'name_kh',
            'email',
            'password',
            'student_id',
            'program_code',
            'year_level',
            'date_of_birth',
            'scholarship_type',
            'course_code',
            'section_name',
        ];

        $example = [
            'YOUNG Soklong',
            'យ៉ុង សុខឡុង',
            'young.soklong@student.edu.kh',
            'student1234',
            '2025-CS-001',
            'BCS',
            '3',
            '2002-03-15',
            'CS301',
            '3CS-A',
        ];

        $example2 = [
            'Sophea Meas',
            'សុភា មាស',
            'sophea.meas@student.edu.kh',
            'student1234',
            '2025-CS-002',
            'BCS',
            '3',
            '2001-07-22',
            'CS301',
            '3CS-A',
        ];

        $callback = function () use ($columns, $example, $example2) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 (helps Excel show Khmer correctly)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fputcsv($file, $example2);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}