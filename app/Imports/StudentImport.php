<?php

namespace App\Imports;

use App\Models\{User, Student, Program, Section, Course, Enrollment};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentImport implements
    ToCollection,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError
{
    use SkipsErrors;

    public array $results = [
        'created'  => 0,
        'enrolled' => 0,
        'skipped'  => 0,
        'errors'   => [],
    ];

    /**
     * Expected columns:
     * name | name_kh | email | password | student_id | program_code
     * year_level | date_of_birth | course_code | section_name
     *
     * name_kh, date_of_birth, course_code, section_name are optional
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // +2 because row 1 is heading

            // Skip empty rows
            if (empty(trim($row['name'] ?? '')) && empty(trim($row['email'] ?? ''))) {
                continue;
            }

            try {
                // ── Validate required fields ──────────────────────────
                $missing = [];
                foreach (['name', 'email', 'student_id', 'program_code', 'year_level'] as $field) {
                    if (empty(trim($row[$field] ?? ''))) {
                        $missing[] = $field;
                    }
                }

                if (!empty($missing)) {
                    $this->results['errors'][] = "Row {$rowNum}: Missing required fields: " . implode(', ', $missing);
                    $this->results['skipped']++;
                    continue;
                }

                // ── Find program ──────────────────────────────────────
                $program = Program::where('code', trim($row['program_code']))->first();
                if (!$program) {
                    $this->results['errors'][] = "Row {$rowNum}: Program code '{$row['program_code']}' not found.";
                    $this->results['skipped']++;
                    continue;
                }

                // ── Create or find user ───────────────────────────────
                $email = trim(strtolower($row['email']));

                if (User::where('email', $email)->exists()) {
                    $this->results['errors'][] = "Row {$rowNum}: Email '{$email}' already exists — skipped.";
                    $this->results['skipped']++;
                    continue;
                }

                if (Student::where('student_id', trim($row['student_id']))->exists()) {
                    $this->results['errors'][] = "Row {$rowNum}: Student ID '{$row['student_id']}' already exists — skipped.";
                    $this->results['skipped']++;
                    continue;
                }

                // ── Create User ───────────────────────────────────────
                $password = !empty(trim($row['password'] ?? ''))
                    ? trim($row['password'])
                    : 'student1234'; // default password

                $user = User::create([
                    'name'      => trim($row['name']),
                    'name_kh'   => !empty(trim($row['name_kh'] ?? '')) ? trim($row['name_kh']) : null,
                    'email'     => $email,
                    'password'  => Hash::make($password),
                    'role'      => 'student',
                    'is_active' => true,
                ]);

                // ── Create Student profile ────────────────────────────
                $scholarshipType = trim(strtolower($row['scholarship_type'] ?? 'paid'));
                if (!in_array($scholarshipType, ['paid','partial','full'])) {
                    $scholarshipType = 'paid';
                }

                $student = Student::create([
                    'user_id'          => $user->id,
                    'program_id'       => $program->id,
                    'student_id'       => trim($row['student_id']),
                    'year_level'       => (int) $row['year_level'],
                    'date_of_birth'    => !empty(trim($row['date_of_birth'] ?? ''))
                                         ? date('Y-m-d', strtotime($row['date_of_birth']))
                                         : null,
                    'status'           => 'active',
                    'scholarship_type' => $scholarshipType,
                ]);

                $this->results['created']++;

                // ── Optional: Enroll into section ─────────────────────
                $courseCode   = trim($row['course_code'] ?? '');
                $sectionName  = trim($row['section_name'] ?? '');

                if (!empty($courseCode) && !empty($sectionName)) {
                    $section = Section::whereHas('course', fn($q) =>
                        $q->where('code', $courseCode)
                    )->where('name', $sectionName)->first();

                    if ($section) {
                        Enrollment::firstOrCreate(
                            ['student_id' => $student->id, 'section_id' => $section->id],
                            ['status' => 'enrolled']
                        );
                        $this->results['enrolled']++;
                    } else {
                        $this->results['errors'][] = "Row {$rowNum}: Student created but section '{$sectionName}' for course '{$courseCode}' not found — not enrolled.";
                    }
                }

            } catch (\Exception $e) {
                $this->results['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                $this->results['skipped']++;
            }
        }
    }

    public function batchSize(): int { return 50; }
    public function chunkSize(): int { return 50; }
}