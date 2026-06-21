<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () { return redirect()->route('login'); });

// ── AUTH ──────────────────────────────────────────────────────────────────────
Route::get('/login',         [AuthController::class, 'showStudentLogin'])->name('login');
Route::get('/admin/login',   [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::get('/teacher/login', [AuthController::class, 'showTeacherLogin'])->name('teacher.login');
Route::get('/student/login', [AuthController::class, 'showStudentLogin'])->name('student.login');
Route::post('/login',         [AuthController::class, 'login']);
Route::post('/admin/login',   [AuthController::class, 'login']);
Route::post('/teacher/login', [AuthController::class, 'login']);
Route::post('/student/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── PROFILE ───────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});

// ── ADMIN ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // University structure
    Route::resource('faculties',   \App\Http\Controllers\Admin\FacultyController::class);
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
    Route::resource('programs',    \App\Http\Controllers\Admin\ProgramController::class);

    // Teacher grouped views — BEFORE resource
    Route::get('teachers', [\App\Http\Controllers\Admin\UserController::class, 'teachers'])
         ->name('teachers.index');
    Route::get('teachers/department/{department}', [\App\Http\Controllers\Admin\UserController::class, 'teachersByDepartment'])
         ->name('teachers.department');

    // Users — specific routes BEFORE resource to avoid conflicts
    Route::post('users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])
         ->name('users.toggle-active');
    Route::post('users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])
         ->name('users.reset-password');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    // Resource handles DELETE users/{user} → destroy() automatically, no need to duplicate

    // Students grouped — import routes FIRST (more specific), then grouped view
    Route::get('students/import/template', [\App\Http\Controllers\Admin\StudentImportController::class, 'template'])
         ->name('students.import.template');
    Route::get('students/import', [\App\Http\Controllers\Admin\StudentImportController::class, 'index'])
         ->name('students.import');
    Route::post('students/import', [\App\Http\Controllers\Admin\StudentImportController::class, 'store'])
         ->name('students.import.store');
    Route::get('students/program/{program}', [\App\Http\Controllers\Admin\UserController::class, 'studentsByProgram'])
         ->name('students.program');
     Route::put('students/{student}/scholarship', [\App\Http\Controllers\Admin\UserController::class, 'updateScholarship'])
          ->name('students.scholarship');
    Route::get('students', [\App\Http\Controllers\Admin\UserController::class, 'students'])
         ->name('students.index');

    // Courses — specific before resource
    Route::get('courses/programs-by-department', [\App\Http\Controllers\Admin\CourseController::class, 'getProgramsByDepartment'])
         ->name('courses.programs-by-department');
    Route::get('courses/program/{program}', [\App\Http\Controllers\Admin\CourseController::class, 'program'])
         ->name('courses.program');
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);

    // Sections — specific before resource
    Route::get('sections/{section}/print-students', [\App\Http\Controllers\Admin\SectionController::class, 'printStudents'])
         ->name('sections.print-students');
    Route::resource('sections', \App\Http\Controllers\Admin\SectionController::class);

    // Grade Components
    Route::post('grade-components', [\App\Http\Controllers\Admin\GradeComponentController::class, 'store'])
         ->name('grade-components.store');
    Route::delete('grade-components/{gradeComponent}', [\App\Http\Controllers\Admin\GradeComponentController::class, 'destroy'])
         ->name('grade-components.destroy');

    // Class Groups
    Route::get('class-groups', [\App\Http\Controllers\Admin\ClassGroupController::class, 'index'])->name('class-groups.index');
    Route::post('class-groups', [\App\Http\Controllers\Admin\ClassGroupController::class, 'store'])->name('class-groups.store');
    Route::get('class-groups/{classGroup}', [\App\Http\Controllers\Admin\ClassGroupController::class, 'show'])->name('class-groups.show');
    Route::put('class-groups/{classGroup}', [\App\Http\Controllers\Admin\ClassGroupController::class, 'update'])->name('class-groups.update');
    Route::delete('class-groups/{classGroup}', [\App\Http\Controllers\Admin\ClassGroupController::class, 'destroy'])->name('class-groups.destroy');
    Route::post('class-groups/{classGroup}/add-students', [\App\Http\Controllers\Admin\ClassGroupController::class, 'addStudents'])->name('class-groups.add-students');
    Route::delete('class-groups/{classGroup}/students/{student}', [\App\Http\Controllers\Admin\ClassGroupController::class, 'removeStudent'])->name('class-groups.remove-student');
    Route::post('class-groups/{classGroup}/enroll', [\App\Http\Controllers\Admin\ClassGroupController::class, 'enrollToSection'])->name('class-groups.enroll');

    // Semesters — set-active-year MUST be before resource
    Route::post('semesters/set-active-year', [\App\Http\Controllers\Admin\SemesterController::class, 'setActiveYear'])
         ->name('semesters.set-active-year');
    Route::resource('semesters', \App\Http\Controllers\Admin\SemesterController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('semesters/{semester}/set-active', [\App\Http\Controllers\Admin\SemesterController::class, 'setActive'])
         ->name('semesters.set-active');

    // Enrollments — specific before store/index
    Route::post('enrollments/bulk', [\App\Http\Controllers\Admin\EnrollmentController::class, 'bulkStore'])->name('enrollments.bulk');
    Route::post('enrollments/from-class-group', [\App\Http\Controllers\Admin\EnrollmentController::class, 'enrollClassGroup'])->name('enrollments.from-class-group');
    Route::post('enrollments/retake', [\App\Http\Controllers\Admin\EnrollmentController::class, 'retake'])->name('enrollments.retake');
    Route::get('enrollments', [\App\Http\Controllers\Admin\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('enrollments', [\App\Http\Controllers\Admin\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('enrollments/{enrollment}', [\App\Http\Controllers\Admin\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    // Announcements
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);
    Route::post('announcements/{announcement}/publish', [\App\Http\Controllers\Admin\AnnouncementController::class, 'publish'])
         ->name('announcements.publish');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('grades/{section}/excel',     [\App\Http\Controllers\Admin\ReportController::class, 'gradesExcel'])->name('grades-excel');
        Route::get('attendance/{section}/excel', [\App\Http\Controllers\Admin\ReportController::class, 'attendanceExcel'])->name('attendance-excel');
        Route::get('transcript/{student}/pdf',   [\App\Http\Controllers\Admin\ReportController::class, 'transcriptPdf'])->name('transcript-pdf');
    });

    // Timetable — specific before wildcard {section}
    Route::get('timetable/program/{program}', [\App\Http\Controllers\Admin\TimetableController::class, 'program'])->name('timetable.program');
    Route::get('timetable', [\App\Http\Controllers\Admin\TimetableController::class, 'overview'])->name('timetable.overview');
    Route::get('timetable/{section}', [\App\Http\Controllers\Admin\TimetableController::class, 'index'])->name('timetable.index');
    Route::post('timetable/{section}', [\App\Http\Controllers\Admin\TimetableController::class, 'store'])->name('timetable.store');
    Route::put('timetable/{timetable}', [\App\Http\Controllers\Admin\TimetableController::class, 'update'])->name('timetable.update');
    Route::delete('timetable/{timetable}', [\App\Http\Controllers\Admin\TimetableController::class, 'destroy'])->name('timetable.destroy');

    // Year Promotion — history BEFORE index to avoid wildcard conflict
    Route::get('promotion/history', [\App\Http\Controllers\Admin\PromotionController::class, 'history'])->name('promotion.history');
    Route::get('promotion', [\App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotion.index');
    Route::post('promotion/promote', [\App\Http\Controllers\Admin\PromotionController::class, 'promote'])->name('promotion.promote');
    Route::post('promotion/graduate', [\App\Http\Controllers\Admin\PromotionController::class, 'graduate'])->name('promotion.graduate');

    // System Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

    // Academic Standing — specific before wildcard
    Route::get('standing/program/{program}', [\App\Http\Controllers\Admin\AcademicStandingController::class, 'program'])->name('standing.program');
    Route::get('standing', [\App\Http\Controllers\Admin\AcademicStandingController::class, 'index'])->name('standing.index');
});

// ── TEACHER ───────────────────────────────────────────────────────────────────
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher'])->group(function () {

    Route::get('/', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

    Route::get('courses', [\App\Http\Controllers\Teacher\CourseController::class, 'index'])->name('courses.index');
    Route::get('announcements', [\App\Http\Controllers\Teacher\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('courses/{section}/students', [\App\Http\Controllers\Teacher\CourseController::class, 'students'])->name('courses.students');

    Route::get('attendance/{section}', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/{section}/save', [\App\Http\Controllers\Teacher\AttendanceController::class, 'save'])->name('attendance.save');
    Route::post('attendance/{section}/save-week', [\App\Http\Controllers\Teacher\AttendanceController::class, 'saveWeek'])->name('attendance.save-week');

    Route::get('grades/{section}', [\App\Http\Controllers\Teacher\GradeController::class, 'index'])->name('grades.index');
    Route::post('grades/{section}/upsert', [\App\Http\Controllers\Teacher\GradeController::class, 'upsert'])->name('grades.upsert');
    Route::post('grades/{section}/reexam', [\App\Http\Controllers\Teacher\GradeController::class, 'enterReexamScore'])->name('grades.reexam');
    Route::post('grades/{section}/finalise', [\App\Http\Controllers\Teacher\GradeController::class, 'finalise'])->name('grades.finalise');
    Route::post('grades/{section}/import', [\App\Http\Controllers\Teacher\GradeController::class, 'import'])->name('grades.import');
    Route::post('grades/{section}/sync-attendance', [\App\Http\Controllers\Teacher\GradeController::class, 'syncAttendance'])->name('grades.sync-attendance');

    Route::get('reexam/{section}', [\App\Http\Controllers\Teacher\ReexamController::class, 'index'])->name('reexam.index');
    Route::post('reexam/{section}/save', [\App\Http\Controllers\Teacher\ReexamController::class, 'save'])->name('reexam.save');

    Route::get('reports/{section}', [\App\Http\Controllers\Teacher\GradeReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{section}/pdf', [\App\Http\Controllers\Teacher\GradeReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/{section}/excel', [\App\Http\Controllers\Teacher\GradeReportController::class, 'excel'])->name('reports.excel');

    Route::get('groups/{section}', [\App\Http\Controllers\Teacher\GroupController::class, 'index'])->name('groups.index');
    Route::post('groups/{section}', [\App\Http\Controllers\Teacher\GroupController::class, 'store'])->name('groups.store');
    Route::post('groups/{group}/assign', [\App\Http\Controllers\Teacher\GroupController::class, 'assign'])->name('groups.assign');
    Route::post('groups/{group}/leader', [\App\Http\Controllers\Teacher\GroupController::class, 'setLeader'])->name('groups.leader');
    Route::delete('groups/{group}/members/{student}', [\App\Http\Controllers\Teacher\GroupController::class, 'removeMember'])->name('groups.remove-member');
    Route::delete('groups/{group}', [\App\Http\Controllers\Teacher\GroupController::class, 'destroy'])->name('groups.destroy');
});

// ── STUDENT ───────────────────────────────────────────────────────────────────
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {

    Route::get('/', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('courses', [\App\Http\Controllers\Student\CourseController::class, 'index'])->name('courses.index');
    Route::get('timetable', [\App\Http\Controllers\Student\TimetableController::class, 'index'])->name('timetable');

    // Specific routes before wildcards
    Route::get('attendance/records', [\App\Http\Controllers\Student\AttendanceController::class, 'records'])->name('attendance.records');
    Route::get('announcements', [\App\Http\Controllers\Student\AnnouncementController::class, 'index'])->name('announcements.index');

    Route::get('transcript/download', [\App\Http\Controllers\Student\TranscriptController::class, 'download'])->name('transcript.download');
    Route::get('transcript/print', [\App\Http\Controllers\Student\TranscriptController::class, 'print'])->name('transcript.print');
    Route::get('transcript', [\App\Http\Controllers\Student\TranscriptController::class, 'index'])->name('transcript.index');
});