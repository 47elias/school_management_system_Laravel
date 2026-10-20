<?php

use Illuminate\Support\Facades\Route;

// Auth & Guest Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\StudentForgotPasswordController;

// Admin & Portal Controllers
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\PortalController;

// Academic & Operational Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectAssignmentController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityAnalyticsController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| System & Maintenance Routes
|--------------------------------------------------------------------------
*/
Route::get('/fix-admin', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'receptionist']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'teacher']);

    $user = \App\Models\User::find(5); 
    if ($user) {
        $user->assignRole('admin');
        return 'Success! User ID 5 is now an Admin.';
    }
    
    return 'User ID 5 not found.';
});

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/
// Welcome & Authentication
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public Online Student Admission Application
Route::prefix('apply')->name('students.apply.')->group(function () {
    Route::get('/', [AdmissionController::class, 'index'])->name('index');
    Route::post('/', [AdmissionController::class, 'store'])->name('store');
    Route::post('/track', [AdmissionController::class, 'track'])->name('track');
    Route::get('/letter/{tracking_id}', [AdmissionController::class, 'downloadLetter'])->name('letter');
});

// Admin / Staff Password Recovery
Route::get('password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.request');
Route::post('password/update', [ForgotPasswordController::class, 'updatePassword'])->name('password.update.final');

// Student Password Recovery
Route::get('student/forgot-password', [StudentForgotPasswordController::class, 'showResetForm'])->name('student.password.request');
Route::post('student/reset-password', [StudentForgotPasswordController::class, 'updatePassword'])->name('student.password.update');

/*
|--------------------------------------------------------------------------
| Receptionist Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {

        Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [ReceptionistController::class, 'profile'])->name('profile');
        Route::get('/classes', [ReceptionistController::class, 'classesIndex'])->name('classes.index');

        // Pesepay Payment Gateway Endpoints for Receptionist
        Route::post('/fees/pesepay/initiate', [FeeController::class, 'payWithPesepay'])->middleware('permission:manage-fees')->name('fees.pesepay.initiate');
        Route::get('/fees/pesepay/return', [FeeController::class, 'pesepayReturn'])->name('fees.pesepay.return');
        Route::post('/fees/pesepay/result', [FeeController::class, 'pesepayResult'])->name('fees.pesepay.result')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        // Student Management
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [ReceptionistController::class, 'indexStudents'])->name('index');
            Route::get('/create', [ReceptionistController::class, 'studentsCreate'])->name('create');
            Route::get('/{id}', [ReceptionistController::class, 'showStudent'])->name('show');
            Route::post('/store', [ReceptionistController::class, 'storeStudent'])->name('store');
            Route::get('/{id}/profile-data', [StudentController::class, 'showProfile'])->name('profile.data');
            Route::get('/{id}/financials', [StudentController::class, 'financials'])->name('financials');
        });

        // Payment Processing
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [ReceptionistController::class, 'paymentsIndex'])->name('index');
            Route::get('/create', [ReceptionistController::class, 'createPayment'])->name('create');
            Route::post('/store', [ReceptionistController::class, 'storePayment'])->name('store');
            Route::get('/receipt/{id}', [ReceptionistController::class, 'printReceipt'])->name('receipt');
            Route::get('/print/{id}', [ReceptionistController::class, 'printReceipt'])->name('print');
        });
    });

/*
|--------------------------------------------------------------------------
| Admin & Operational Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // System Staff User Management
    Route::resource('users', UserController::class)->middleware('permission:manage-users');

    // System Roles & Permissions Security
    Route::prefix('administration')->name('roles.')->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->middleware('permission:manage-users')->name('index');
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])->middleware('permission:manage-users')->name('store_role');
        Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->middleware('permission:manage-users')->name('store_permission');
        Route::post('/roles/assign-permissions', [RolePermissionController::class, 'assignPermissions'])->middleware('permission:manage-users')->name('assign_permissions');
        Route::delete('/roles/{id}', [RolePermissionController::class, 'destroyRole'])->middleware('permission:manage-users')->name('destroy_role');
        Route::delete('/permissions/{id}', [RolePermissionController::class, 'destroyPermission'])->middleware('permission:manage-users')->name('destroy_permission');
    });
    // Account Profile & Security Settings
    Route::prefix('settings')->group(function () {
        Route::get('/change-password', [DashboardController::class, 'showChangePassword'])->name('admin.change_password');
        Route::post('/update-password', [DashboardController::class, 'updatePassword'])->name('admin.update_password');
        Route::get('/profile', [DashboardController::class, 'editProfile'])->name('admin.profile');
        Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('admin.update_profile');
    });

    // Student ERP Management
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/manage', [StudentController::class, 'index'])->middleware('permission:student_management')->name('index');
        Route::get('/create', [StudentController::class, 'create'])->middleware('permission:student_management')->name('create');
        Route::post('/store', [StudentController::class, 'store'])->middleware('permission:student_management')->name('store');
        Route::get('/stats', [StudentController::class, 'enrollmentStats'])->middleware('permission:student_management')->name('enrollment_stats');
        Route::get('/promote', [StudentController::class, 'showPromotionForm'])->middleware('permission:promote-students')->name('promote');
        Route::post('/promote', [StudentController::class, 'promote'])->middleware('permission:promote-students')->name('promote.store');
        Route::post('/promote/mass', [StudentController::class, 'processMassPromotion'])->middleware('permission:promote-students')->name('promote.mass');
        
        // Single Student Page & Modal Quick Data
        Route::get('/{student}', [StudentController::class, 'show'])->middleware('permission:student_management')->name('show');
        Route::get('/{id}/profile-data', [StudentController::class, 'showProfile'])->middleware('permission:student_management')->name('profile_data');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->middleware('permission:student_management')->name('edit');
        Route::put('/{id}', [StudentController::class, 'update'])->middleware('permission:student_management')->name('update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->middleware('permission:student_management')->name('destroy');
    });

    // Classes Management
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::post('/store', [ClassController::class, 'store'])->name('store');
        Route::get('/assign-subjects', [ClassController::class, 'assignSubjects'])->name('assign');
        Route::post('/assign-subjects', [ClassController::class, 'storeAssignments'])->name('assign.store');
        Route::get('/{class}/edit', [ClassController::class, 'edit'])->name('edit');
        Route::put('/{class}', [ClassController::class, 'update'])->name('update');
        Route::get('/{id}/students', [ClassController::class, 'showStudents'])->name('students');
    });

    // Grade Levels / Forms Management
    Route::post('/grade-levels', [ClassController::class, 'storeGradeLevel'])->name('grade-levels.store');
    Route::delete('/grade-levels/{id}', [ClassController::class, 'destroyGradeLevel'])->name('grade-levels.destroy');

    // Subjects & Allocations
    Route::get('/subjects', [SubjectAssignmentController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/manage', [SubjectAssignmentController::class, 'manage'])->name('subjects.manage');
    Route::get('/subjects/allocate-classes', [SubjectAssignmentController::class, 'allocateClasses'])->name('subjects.allocate_classes');
    Route::get('/subjects/allocate-teachers', [SubjectAssignmentController::class, 'allocateTeachers'])->name('subjects.allocate_teachers');

    Route::post('/subjects', [SubjectAssignmentController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [SubjectAssignmentController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectAssignmentController::class, 'destroy'])->name('subjects.destroy');

    Route::post('/subject-combinations', [SubjectAssignmentController::class, 'storeCombination'])->name('subject-combinations.store');
    Route::delete('/subject-combinations/{id}', [SubjectAssignmentController::class, 'destroyCombination'])->name('subject-combinations.destroy');

    Route::post('/class-subjects', [SubjectAssignmentController::class, 'storeClassAllocation'])->name('class-subjects.store');
    Route::delete('/class-subjects/{id}', [SubjectAssignmentController::class, 'destroyClassAllocation'])->name('class-subjects.destroy');

    Route::post('/subject-assignments', [SubjectAssignmentController::class, 'store'])->name('subject-assignments.store');
    Route::delete('/subject-assignments/{id}', [SubjectAssignmentController::class, 'destroy'])->name('subject-assignments.destroy');
    
    // Timetable System Routes
    Route::get('/api/classes/{classId}/subjects', [TimetableController::class, 'getSubjectsByClass']);
    Route::prefix('admin/timetable')->name('timetable.')->group(function () {
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::get('/create', [TimetableController::class, 'create'])->name('create');
        Route::post('/store', [TimetableController::class, 'store'])->name('store');
        Route::get('/class/{class_id}', [TimetableController::class, 'show'])->name('show');
        Route::post('/generate', [TimetableController::class, 'generate'])->name('generate');
        Route::get('/{id}/edit', [TimetableController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TimetableController::class, 'update'])->name('update');
        Route::delete('/bulk-delete-special', [TimetableController::class, 'bulkDeleteSpecial'])->name('bulk_delete_special');
        Route::delete('/{id}', [TimetableController::class, 'destroy'])->name('destroy');
    });

    // Academic Terms / Semesters
    Route::prefix('terms')->name('terms.')->group(function () {
        Route::get('/', [TermController::class, 'index'])->middleware('permission:term_management')->name('index');
        Route::post('/', [TermController::class, 'store'])->middleware('permission:term_management')->name('store');
        Route::post('/activate/{id}', [TermController::class, 'activate'])->middleware('permission:term_management')->name('activate');
    });

    // Announcements
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/store', [AnnouncementController::class, 'store'])->name('store');
        Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    // Newsletters
    Route::prefix('newsletters')->name('newsletters.')->group(function () {
        Route::get('/', [NewsletterController::class, 'index'])->name('index');
        Route::get('/create', [NewsletterController::class, 'create'])->name('create');
        Route::post('/store', [NewsletterController::class, 'store'])->name('store');
        Route::get('/{id}', [NewsletterController::class, 'show'])->name('show');
        Route::delete('/{id}', [NewsletterController::class, 'destroy'])->name('destroy');
    })->middleware('permission:newsletter_management');

    // Fees & Accounting Financials
    Route::prefix('fees')->name('fees.')->group(function () {
        Route::get('/payment', [FeeController::class, 'create'])->middleware('permission:manage-fees')->name('create');
        Route::post('/payment', [FeeController::class, 'store'])->middleware('permission:manage-fees')->name('store');
        Route::get('/history', [FeeController::class, 'index'])->middleware('permission:manage-fees')->name('index');
        Route::get('/structure', [FeeController::class, 'showStructure'])->middleware('permission:manage-fees')->name('structure');
        Route::post('/structure', [FeeController::class, 'storeStructure'])->middleware('permission:manage-fees')->name('structure.store');
        Route::post('/structure/bulk', [FeeController::class, 'bulkStoreStructure'])->middleware('permission:manage-fees')->name('structure.bulkStore');
        Route::post('/structure/process-invoices', [FeeController::class, 'processInvoices'])->middleware('permission:manage-fees')->name('structure.process_invoices');
        Route::delete('/structure/{id}', [FeeController::class, 'destroyStructure'])->middleware('permission:manage-fees')->name('structure.destroy');
        Route::get('/balance-report', [FeeController::class, 'balanceReport'])->middleware('permission:manage-fees')->name('report');
        Route::post('/deduct-credit/{id}', [FeeController::class, 'deductCredit'])->middleware('permission:manage-fees')->name('deduct_credit');
        Route::get('/{id}', [FeeController::class, 'show'])->middleware('permission:manage-fees')->name('show');
        Route::delete('/{id}', [FeeController::class, 'destroy'])->middleware('permission:manage-fees')->name('destroy');

        // Pesepay Payment Gateway Endpoints
        Route::post('/pesepay/initiate', [FeeController::class, 'payWithPesepay'])->middleware('permission:manage-fees')->name('pesepay.initiate');
        Route::get('/pesepay/return', [FeeController::class, 'pesepayReturn'])->name('pesepay.return');
        Route::post('/pesepay/result', [FeeController::class, 'pesepayResult'])->name('pesepay.result')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        // Online Payment Gateway Aliases
        Route::post('/pay-online', [FeeController::class, 'payOnline'])->middleware('permission:manage-fees')->name('payOnline');
    });

    // Payroll Management
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->middleware('permission:manage-payroll')->name('index');
        Route::post('/store', [PayrollController::class, 'store'])->middleware('permission:manage-payroll')->name('store');
        Route::delete('/{id}', [PayrollController::class, 'destroy'])->middleware('permission:manage-payroll')->name('destroy');
        Route::get('/print/{id}', [PayrollController::class, 'print'])->middleware('permission:manage-payroll')->name('print');
    });

    // Asset & Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->middleware('permission:manage-inventory')->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->middleware('permission:manage-inventory')->name('create');
        Route::post('/store', [InventoryController::class, 'store'])->middleware('permission:manage-inventory')->name('store');
        Route::post('/update-stock', [InventoryController::class, 'updateStock'])->middleware('permission:manage-inventory')->name('updateStock');
        Route::get('/logs', [InventoryController::class, 'logs'])->middleware('permission:manage-inventory')->name('logs');
        Route::get('/alerts', [InventoryController::class, 'lowStockAlerts'])->middleware('permission:manage-inventory')->name('alerts');
        Route::get('/{id}/edit', [InventoryController::class, 'edit'])->middleware('permission:manage-inventory')->name('edit');
        Route::put('/{id}', [InventoryController::class, 'update'])->middleware('permission:manage-inventory')->name('update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->middleware('permission:manage-inventory')->name('destroy');
    });

    // Expenses Tracking
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->middleware('permission:manage-expenses')->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->middleware('permission:manage-expenses')->name('create');
        Route::post('/store', [ExpenseController::class, 'store'])->middleware('permission:manage-expenses')->name('store');
        Route::get('/categories', [ExpenseController::class, 'categories'])->middleware('permission:manage-expenses')->name('categories');
        Route::get('/{id}/edit', [ExpenseController::class, 'edit'])->middleware('permission:manage-expenses')->name('edit');
        Route::put('/{id}', [ExpenseController::class, 'update'])->middleware('permission:manage-expenses')->name('update');
        Route::delete('/{id}', [ExpenseController::class, 'destroy'])->middleware('permission:manage-expenses')->name('destroy');
    });

    // Admissions Administration
    Route::prefix('admissions')->name('admissions.')->group(function () {
        Route::get('/', [AdmissionController::class, 'manage'])->middleware('permission:admissions')->name('manage');
        Route::put('/{id}', [AdmissionController::class, 'update'])->middleware('permission:admissions')->name('update');
    });

    // System Audit Trail
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit-logs')->name('audit.logs');
});

/*
|--------------------------------------------------------------------------
| Shared Academic & Examination Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Examinations & Marks Recording
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->middleware('permission:exams_management')->name('index');
        Route::get('/schedule/{type}/{id}', [ExamController::class, 'scheduleView'])->middleware('permission:exams_management')->name('schedule');
        Route::get('/subject-report/{subject_id}', [ExamController::class, 'subjectReport'])->middleware('permission:exams_management')->name('subject_report');
        Route::get('/{exam_id}/download-template/{target}', [ExamController::class, 'downloadTemplate'])->middleware('permission:exams_management')->name('download_template');
        Route::post('/import-marks', [ExamController::class, 'importMarks'])->middleware('permission:exams_management')->name('import_marks');
        Route::post('/', [ExamController::class, 'store'])->middleware('permission:exams_management')->name('store');
        Route::get('/{exam_id}/verify', [ExamController::class, 'verify'])->middleware('permission:exams_management')->name('verify');
        Route::post('/verify-face', [ExamController::class, 'verifyFace'])->middleware('permission:exams_management')->name('verify_face');
        Route::patch('/{id}/toggle-publish', [ExamController::class, 'togglePublish'])->middleware('permission:exams_management')->name('toggle_publish');
        Route::get('/{exam_id}/marks/{grade}', [ExamController::class, 'createMarks'])->middleware('permission:exams_management')->name('create_marks');
        Route::get('/{exam_id}/report/{grade}', [ExamController::class, 'examReport'])->middleware('permission:exams_management')->name('report');
        Route::delete('/{id}', [ExamController::class, 'destroy'])->middleware('permission:exams_management')->name('destroy');
    });

    Route::post('/marks/bulk-store', [ExamController::class, 'bulkStore'])->name('marks.bulk_store');

    // Continuous Assessment & Learning Analytics
    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [ActivityController::class, 'adminIndex'])->middleware('permission:activities_management')->name('index');
        Route::get('/analytics', [ActivityAnalyticsController::class, 'dashboard'])->middleware('permission:activities_management')->name('analytics');
        Route::post('/analytics/ai-insights', [ActivityAnalyticsController::class, 'aiInsights'])->middleware('permission:activities_management')->name('analytics.ai_insights');
        Route::get('/{id}', [ActivityController::class, 'adminShow'])->middleware('permission:activities_management')->name('show');
    });
});

/*
|--------------------------------------------------------------------------
| Teacher Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [TeacherController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [TeacherController::class, 'updateProfile'])->name('profile.update');
        Route::get('/my-class', [TeacherController::class, 'students'])->name('my_class');
        Route::get('/student/{id}', [TeacherController::class, 'showStudent'])->name('students.show');
        Route::get('/my-subjects', [TeacherController::class, 'assignedSubjects'])->name('subjects');
        Route::get('/subjects/{subject_id}/marks', [ExamController::class, 'teacherSubjectReport'])->name('subjects.marks');

        // Teacher Exam Management
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [TeacherController::class, 'examIndex'])->name('index');
            Route::get('/create', [TeacherController::class, 'examCreate'])->name('create');
            Route::post('/store', [TeacherController::class, 'examStore'])->name('store');
            Route::delete('/{id}', [ExamController::class, 'teacherDestroy'])->name('destroy');
        });

        // Teacher Marks Management
        Route::get('/marks/manage/{exam_id}', [ExamController::class, 'teacherManageMarks'])->name('marks.manage');
        Route::get('/marks/{exam_id}/template', [ExamController::class, 'downloadTeacherMarksTemplate'])->name('marks.template');
        Route::post('/marks/import', [ExamController::class, 'teacherImportMarks'])->name('marks.import');
        Route::post('/marks/store', [ExamController::class, 'teacherBulkStore'])->name('marks.store');
        Route::post('/marks/bulk-store', [ExamController::class, 'teacherBulkStore'])->name('marks.bulk_store');

        // Teacher Continuous Assessment
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::post('/', [ActivityController::class, 'store'])->name('store');
            Route::get('/{id}/record', [ActivityController::class, 'recordMarks'])->name('record');
            Route::post('/marks/store', [ActivityController::class, 'bulkStore'])->name('marks.store');
            Route::get('/{id}/template', [ActivityController::class, 'downloadTemplate'])->name('template');
            Route::post('/marks/import', [ActivityController::class, 'importMarks'])->name('import');
            Route::get('/assignment/{assignmentId}/history', [ActivityController::class, 'history'])->name('history');
            Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->group(function () {
    
    // Student Auth
    Route::get('/login', [StudentLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [StudentLoginController::class, 'logout'])->name('logout');

    // Protected Student Portal Workspace
    Route::middleware(['auth:student'])->group(function () {
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/updates', [PortalController::class, 'updates'])->name('updates');
        Route::get('/results', [PortalController::class, 'results'])->name('results');
        Route::get('/continuous-assessment', [PortalController::class, 'continuousAssessment'])->name('continuous_assessment');
        Route::get('/fees', [PortalController::class, 'fees'])->name('fees');
        Route::get('/change-password', [PortalController::class, 'changePassword'])->name('change_password');
        Route::post('/update-password', [PortalController::class, 'updatePassword'])->name('update_password');
        
        // AI Learning Assistant Chatbot
        Route::get('/ai-chat', [ChatbotController::class, 'index'])->name('ai_chat');
        Route::post('/ai-chat/message', [ChatbotController::class, 'handle'])->name('ai_chat.message');
    });
});
