<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\PortalController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\SubjectAssignmentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\StudentForgotPasswordController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\ChatbotController;
use App\Models\Exam;
use App\Models\Term;


//Admission Routes
Route::get('/apply', [App\Http\Controllers\AdmissionController::class, 'index'])->name('students.apply');
Route::post('/apply', [App\Http\Controllers\AdmissionController::class, 'store'])->name('students.apply.store');
Route::post('/apply/track', [App\Http\Controllers\AdmissionController::class, 'track'])->name('students.apply.track');
Route::get('/admissions/letter/{tracking_id}', [App\Http\Controllers\AdmissionController::class, 'downloadLetter'])
     ->name('students.apply.letter');
// GET: Shows the welcome page
Route::get('/', function () {
    return view('welcome');
})->name('login');

// 1. Admin Password Reset Routes
Route::get('password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.request');
Route::post('password/update', [ForgotPasswordController::class, 'updatePassword'])->name('password.update.final');

// 2. Student Password Reset Routes
// Student Password Reset Routes
Route::get('student/forgot-password', [StudentForgotPasswordController::class, 'showResetForm'])->name('student.password.request');
Route::post('student/reset-password', [StudentForgotPasswordController::class, 'updatePassword'])->name('student.password.update');

// POST: Handles the sign-in submission
Route::post('/', [LoginController::class, 'login']);

// POST: Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/**
 * RECEPTIONIST PORTAL ROUTES
 */

Route::middleware(['auth', 'role:admin,receptionist'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {

        // Dashboard - Full Name: receptionist.dashboard
        Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->name('dashboard');

        // Student Management
        Route::prefix('students')->name('students.')->group(function () {
            // Full Name: receptionist.students.index
            Route::get('/', [ReceptionistController::class, 'indexStudents'])->name('index');
            // Full Name: receptionist.students.create
            Route::get('/create', [ReceptionistController::class, 'studentsCreate'])->name('create');
            Route::get('/{id}', [ReceptionistController::class, 'showStudent'])->name('show');
            Route::post('/store', [ReceptionistController::class, 'storeStudent'])->name('store');
            // AJAX Profile route for Receptionist
            Route::get('/{id}/profile-data', [StudentController::class, 'showProfile'])->name('profile.data');
        });

        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            // URL: /receptionist/payments | Name: receptionist.payments.index
            Route::get('/', [ReceptionistController::class, 'paymentsIndex'])->name('index');

            // URL: /receptionist/payments/create | Name: receptionist.payments.create
            Route::get('/create', [ReceptionistController::class, 'createPayment'])->name('create');

            // URL: /receptionist/payments/store | Name: receptionist.payments.store
            Route::post('/store', [ReceptionistController::class, 'storePayment'])->name('store');

            // URL: /receptionist/payments/receipt/{id} | Name: receptionist.payments.receipt
            // Also aliased as 'print' to match the Controller redirect if needed
            Route::get('/receipt/{id}', [ReceptionistController::class, 'printReceipt'])->name('receipt');
            Route::get('/print/{id}', [ReceptionistController::class, 'printReceipt'])->name('print');
        });

        // Profile - Full Name: receptionist.profile
        Route::get('/profile', [ReceptionistController::class, 'profile'])->name('profile');

        // Classes - Full Name: receptionist.classes.index
        Route::get('/classes', [ReceptionistController::class, 'classesIndex'])->name('classes.index');
        Route::post('/payments/store', [ReceptionistController::class, 'storePayment'])->name('payments.store');
        Route::get('/payments/{id}/print', [ReceptionistController::class, 'printReceipt'])->name('payments.print');
    });

/**
 * SHARED PROTECTED ROUTES
 * Accessible by Admin, Teacher, and now Receptionist (for specific views)
 */
Route::middleware(['auth', 'role:admin,teacher,receptionist'])->group(function () {
    // Shared Student View - Added receptionist so they don't get 403 when viewing the list
    Route::get('/students/manage', [StudentController::class, 'index'])->name('students.index');
});

Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    // Shared Exam Views
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    // ... rest of shared exam routes ...
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam_id}/marks/{grade}', [ExamController::class, 'createMarks'])->name('marks.create');
    Route::post('/marks/bulk-store', [ExamController::class, 'bulkStore'])->name('marks.bulk_store');
    Route::get('/exams/{exam_id}/report/{grade}', [ExamController::class, 'examReport'])->name('exams.report');
    Route::delete('/exams/{id}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::get('/exams/{exam_id}/{grade}/marks', [ExamController::class, 'createMarks'])->name('exams.create_marks');
    Route::get('/exams/{exam_id}/{grade}/report', [ExamController::class, 'examReport'])->name('exams.report');

    /**
     * CONTINUOUS ASSESSMENT (shared: admin can view teacher-recorded activities too)
     * Fully independent of the Exams routes above - no fixed schedule required.
     */
    Route::get('/activities', [ActivityController::class, 'adminIndex'])->name('activities.index');
});

/**
 * ADMIN ONLY ROUTES
 */
Route::middleware(['auth', 'role:admin'])->group(function () {
    // The main admin dashboard
    Route::get('/api/classes/{classId}/subjects', [TimetableController::class, 'getSubjectsByClass']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('teachers', TeacherController::class);
    Route::get('/students/{id}/enroll-face', [App\Http\Controllers\StudentController::class, 'enrollFaceView'])->name('students.enroll_face');
    Route::post('/students/{id}/enroll-face', [App\Http\Controllers\StudentController::class, 'storeFace'])->name('students.store_face');
    Route::get('/students/{id}/view-face', [App\Http\Controllers\StudentController::class, 'getFace'])->name('students.get_face');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::get('/students/stats', [StudentController::class, 'enrollmentStats'])->name('students.enrollment_stats');
    Route::get('/students/promote', [StudentController::class, 'showPromotionForm'])->name('students.promote');
    Route::post('/students/promote', [StudentController::class, 'promote'])->name('students.promote.store');
    Route::post('/students/promote/mass', [StudentController::class, 'processMassPromotion'])->name('students.promote.mass');
    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{id}/update', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/timetable/{id}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
    // Add this inside your timetable route group
    Route::delete('/timetable/bulk-delete-special', [App\Http\Controllers\Admin\TimetableController::class, 'bulkDeleteSpecial'])
        ->name('timetable.bulk_delete_special');

    // ADDED PROFILE DATA ROUTE FOR ADMIN (Matches the AJAX URL /students/{id}/profile-data)
    Route::get('/students/{id}/profile-data', [StudentController::class, 'showProfile'])->name('students.profile.data');

    Route::get('/receptionist/students/{id}/financials', [StudentController::class, 'financials'])->name('students.financials');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes/store', [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/assign-subjects', [ClassController::class, 'assignSubjects'])->name('classes.assign');
    Route::post('/classes/assign-subjects', [ClassController::class, 'storeAssignments'])->name('classes.assign.store');
    Route::get('/classes/{class}/edit', [ClassController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
    Route::get('/classes/{id}/students', [ClassController::class, 'showStudents'])->name('classes.students');

    Route::get('/subjects', [SubjectAssignmentController::class, 'index'])->name('subjects.index');
    Route::post('/subjects/store', [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Timetable Routes
    Route::group(['as' => 'timetable.', 'prefix' => 'admin/timetable'], function () {
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::get('/create', [TimetableController::class, 'create'])->name('create');
        Route::post('/store', [TimetableController::class, 'store'])->name('store');
        Route::get('/class/{class_id}', [TimetableController::class, 'show'])->name('show');
    });

    // Assignment Actions
    Route::post('/subject-assignments', [SubjectAssignmentController::class, 'store'])->name('subject-assignments.store');
    Route::delete('/subject-assignments/{id}', [SubjectAssignmentController::class, 'destroy'])->name('subject-assignments.destroy');

    Route::get('/terms', [TermController::class, 'index'])->name('terms.index');
    Route::post('/terms', [TermController::class, 'store'])->name('terms.store');
    Route::get('/terms/activate/{id}', [TermController::class, 'activate'])->name('terms.activate');

    //Fees and Financials Routes
    Route::get('/fees/payment', [FeeController::class, 'create'])->name('fees.create');
    Route::post('/fees/payment', [FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/history', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/structure', [FeeController::class, 'showStructure'])->name('fees.structure');
    Route::post('/fees/structure', [FeeController::class, 'storeStructure'])->name('fees.structure.store');
    Route::get('/fees/balance-report', [FeeController::class, 'balanceReport'])->name('fees.report');
    Route::get('/fees/{id}', [App\Http\Controllers\FeeController::class, 'show'])->name('fees.show');
    Route::delete('/fees/{id}', [FeeController::class, 'destroy'])->name('fees.destroy');
    Route::delete('/fees/structure/{id}', [App\Http\Controllers\FeeController::class, 'destroyStructure'])->name('fees.structure.destroy');
    Route::post('/fees/deduct-credit/{id}', [App\Http\Controllers\FeeController::class, 'deductCredit'])->name('fees.deduct_credit');
    Route::post('/fees/pay-online', [FeeController::class, 'payOnline'])->name('fees.payOnline');
    // Paynow calls this server-to-server to confirm payment status — must be public, no auth/CSRF
    Route::post('/fees/pay-online/result', [FeeController::class, 'payOnlineResult'])
        ->name('fees.payOnline.result')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    // Paynow redirects the payer's browser back here after they pay
    Route::get('/fees/pay-online/return/{feeTransaction}', [FeeController::class, 'payOnlineReturn'])
    ->name('fees.payOnline.return');

    Route::get('/settings/change-password', [DashboardController::class, 'showChangePassword'])->name('admin.change_password');
    Route::post('/settings/update-password', [DashboardController::class, 'updatePassword'])->name('admin.update_password');
    Route::get('/settings/profile', [DashboardController::class, 'editProfile'])->name('admin.profile');
    Route::post('/settings/profile', [DashboardController::class, 'updateProfile'])->name('admin.update_profile');

    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/store', [PayrollController::class, 'store'])->name('payroll.store');
    Route::delete('/payroll/{id}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
    Route::get('/payroll/print/{id}', [PayrollController::class, 'print'])->name('payroll.print');

    // Main Inventory Dashboard
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/update-stock', [InventoryController::class, 'updateStock'])->name('inventory.updateStock');
    Route::get('/inventory/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/alerts', [InventoryController::class, 'lowStockAlerts'])->name('inventory.alerts');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Expense Management Routes
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/store', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::get('/categories', [ExpenseController::class, 'categories'])->name('expenses.categories');
    });

    //Admission Routes for Admin
    Route::prefix('admissions')->group(function () {
        Route::get('/', [AdmissionController::class, 'manage'])->name('admissions.manage');
        Route::put('/{id}', [AdmissionController::class, 'update'])->name('admissions.update');
    });

});
// Remove the Closure route and use this instead:
Route::get('/exams/{id}/verify', [App\Http\Controllers\TeacherController::class, 'examVerifyView'])->name('exams.verify');
Route::post('/exams/verify-face', [App\Http\Controllers\TeacherController::class, 'processFaceVerification'])->name('teacher.exams.verify_face');
/**
 * TEACHER PORTAL ROUTES
 */
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [TeacherController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [TeacherController::class, 'updateProfile'])->name('profile.update');
    Route::get('/my-class', [TeacherController::class, 'students'])->name('my_class');
    Route::get('/student/{id}', [TeacherController::class, 'showStudent'])->name('students.show');
    Route::get('/my-subjects', [TeacherController::class, 'assignedSubjects'])->name('subjects');
    Route::get('/teacher/marks/{id}/manage', [TeacherController::class, 'manageMarks'])->name('teacher.marks.manage');
    Route::post('/teacher/marks/store', [TeacherController::class, 'storeMarks'])->name('teacher.marks.store');
    Route::get('/exams', [TeacherController::class, 'examIndex'])->name('exams.index');
    Route::get('/exams/create', [TeacherController::class, 'examCreate'])->name('exams.create');
    Route::post('/exams/store', [TeacherController::class, 'examStore'])->name('exams.store');
    Route::delete('/exams/{id}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::get('/marks/manage/{exam_id}', [ExamController::class, 'teacherManageMarks'])->name('marks.manage');
    Route::post('/marks/store', [ExamController::class, 'teacherBulkStore'])->name('marks.store');
    Route::post('/marks/bulk-store', [ExamController::class, 'teacherBulkStore'])->name('marks.bulk_store');

    /**
     * CONTINUOUS ASSESSMENT (CA) — independent of Exams above.
     * Daily classwork/homework/quiz/participation/practical/project marks,
     * no fixed schedule, recordable any day.
     */
    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [ActivityController::class, 'teacherIndex'])->name('index');
        Route::post('/', [ActivityController::class, 'store'])->name('store');
        Route::get('/{id}/record', [ActivityController::class, 'recordMarks'])->name('record');
        Route::post('/marks/store', [ActivityController::class, 'bulkStore'])->name('marks.store');
        Route::get('/assignment/{assignmentId}/history', [ActivityController::class, 'history'])->name('history');
        Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('destroy');
    });
});

/**
 * STUDENT PORTAL ROUTES
 */
Route::prefix('student')->group(function () {
    Route::get('/login', [StudentLoginController::class, 'showLoginForm'])->name('student.login');
    Route::post('/login', [StudentLoginController::class, 'login'])->name('student.login.submit');
    Route::post('/logout', [StudentLoginController::class, 'logout'])->name('student.logout');

    Route::middleware(['auth:student'])->group(function () {
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('student.dashboard');
        Route::get('/results', [PortalController::class, 'results'])->name('student.results');
        Route::get('/fees', [PortalController::class, 'fees'])->name('student.fees');
        Route::get('/change-password', [PortalController::class, 'changePassword'])->name('student.change_password');
        Route::post('/update-password', [PortalController::class, 'updatePassword'])->name('student.update_password');
    });
    // Inside your Route::group or middleware for students:
    Route::get('/student/ai-chat', [ChatbotController::class, 'index'])->name('student.ai_chat');
    // Also add the POST route for the actual messaging logic:
    Route::post('/student/ai-chat/message', [ChatbotController::class, 'handle'])->name('student.ai_chat.message');
});