<?php
namespace App\Imports;

use App\Models\{Grade, Enrollment, GradeComponent, Section};
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class GradesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsErrors;

    private \Illuminate\Support\Collection $components;
    public int $importedCount = 0;

    public function __construct(private Section $section)
    {
        // Pre-load components keyed by name for fast lookup
        $this->components = $section->gradeComponents->keyBy('name');
    }

    /**
     * Expected columns: student_id | component_name | score | reexam_score (optional)
     */
    public function model(array $row): ?Grade
    {
        $enrollment = Enrollment::whereHas('student', fn($q) =>
            $q->where('student_id', trim($row['student_id']))
        )->where('section_id', $this->section->id)->first();

        $component = $this->components->get(trim($row['component_name']));

        if (!$enrollment || !$component) {
            return null;
        }

        $this->importedCount++;

        // Use updateOrCreate via Eloquent — return null and handle manually
        Grade::updateOrCreate(
            [
                'enrollment_id'      => $enrollment->id,
                'grade_component_id' => $component->id,
            ],
            [
                'score'        => (float) $row['score'],
                'reexam_score' => isset($row['reexam_score']) && is_numeric($row['reexam_score'])
                                    ? (float) $row['reexam_score']
                                    : null,
                'remarks'      => $row['remarks'] ?? null,
            ]
        );

        return null; // returning null because we handle upsert above
    }

    public function rules(): array
    {
        return [
            'student_id'      => 'required',
            'component_name'  => 'required',
            'score'           => 'required|numeric|min:0',
            'reexam_score'    => 'nullable|numeric|min:0',
        ];
    }

    public function batchSize(): int  { return 100; }
    public function chunkSize(): int  { return 100; }
}