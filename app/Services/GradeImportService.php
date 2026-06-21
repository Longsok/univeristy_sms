<?php
namespace App\Services;

use App\Models\{Section, Grade, Enrollment, GradeComponent};
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class GradeImportService
{
    /**
     * Expected Excel columns: student_id | component_name | score
     */
    public function handle(UploadedFile $file, Section $section): array
    {
        $rows       = Excel::toArray([], $file)[0];
        $headers    = array_shift($rows);
        $components = $section->gradeComponents->keyBy('name');
        $imported   = 0;
        $errors     = [];

        foreach ($rows as $i => $row) {
            $data      = array_combine($headers, $row);
            $studentId = $data['student_id']     ?? null;
            $compName  = $data['component_name'] ?? null;
            $score     = $data['score']          ?? null;

            $enrollment = Enrollment::whereHas('student', fn($q) => $q->where('student_id', $studentId))
                              ->where('section_id', $section->id)
                              ->first();

            $component = $components->get($compName);

            if (!$enrollment || !$component || !is_numeric($score)) {
                $errors[] = "Row " . ($i + 2) . " skipped (invalid data).";
                continue;
            }

            Grade::updateOrCreate(
                ['enrollment_id' => $enrollment->id, 'grade_component_id' => $component->id],
                ['score' => (float) $score]
            );

            $imported++;
        }

        return compact('imported', 'errors');
    }
}