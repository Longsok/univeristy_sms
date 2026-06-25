<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new StudentImport();

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
            '2025-ITE-001',
            'ITE',
            '1',
            '2005-03-15',
            'paid',
            '',
            '',
        ];

        $example2 = [
            'CHAN Dara',
            'ចាន់ ដារ៉ា',
            'dara.chan@student.edu.kh',
            'student1234',
            '2025-ITE-002',
            'ITE',
            '1',
            '2005-07-22',
            'full',
            '',
            '',
        ];

        $callback = function () use ($columns, $example, $example2) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fputcsv($file, $example2);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}