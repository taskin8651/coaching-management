<?php

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});
 
Auth::routes();

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
Route::get('/', 'HomeController@index')->name('home');
    Route::get('my-portal', 'MyPortalController@index')->name('my-portal.index');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

     // Branches
    Route::delete('branches/destroy', 'BranchesController@massDestroy')->name('branches.massDestroy');
    Route::resource('branches', 'BranchesController');

    // Courses
Route::delete('courses/destroy', 'CoursesController@massDestroy')->name('courses.massDestroy');
Route::resource('courses', 'CoursesController');

// Subjects
Route::delete('subjects/destroy', 'SubjectsController@massDestroy')->name('subjects.massDestroy');
Route::resource('subjects', 'SubjectsController');

// Batches
Route::delete('batches/destroy', 'BatchesController@massDestroy')->name('batches.massDestroy');
Route::resource('batches', 'BatchesController');

// Teachers
Route::delete('teachers/destroy', 'TeachersController@massDestroy')->name('teachers.massDestroy');
Route::resource('teachers', 'TeachersController');

// Staff
Route::delete('staff/destroy', 'StaffController@massDestroy')->name('staff.massDestroy');
Route::resource('staff', 'StaffController');

// Students
Route::delete('students/destroy', 'StudentsController@massDestroy')->name('students.massDestroy');
Route::resource('students', 'StudentsController');

// Enquiries
Route::delete('enquiries/destroy', 'EnquiriesController@massDestroy')->name('enquiries.massDestroy');
Route::post('enquiries/{enquiry}/follow-ups', 'EnquiriesController@storeFollowUp')->name('enquiries.followUps.store');
Route::resource('enquiries', 'EnquiriesController');

// Fee Payments
Route::delete('fee-payments/destroy', 'FeePaymentsController@massDestroy')->name('fee-payments.massDestroy');
Route::resource('fee-payments', 'FeePaymentsController');
// Fee Payment Invoice
Route::get('fee-payments/{fee_payment}/invoice', 'FeePaymentsController@invoice')->name('fee-payments.invoice');

// Expenses
Route::delete('expenses/destroy', 'ExpensesController@massDestroy')->name('expenses.massDestroy');
Route::resource('expenses', 'ExpensesController');

// Salary Payments
Route::delete('salary-payments/destroy', 'SalaryPaymentsController@massDestroy')->name('salary-payments.massDestroy');
Route::get('salary-payments/{salary_payment}/slip', 'SalaryPaymentsController@slip')->name('salary-payments.slip');
Route::resource('salary-payments', 'SalaryPaymentsController');

// Student Batch Assignments
Route::resource('student-batches', 'StudentBatchesController')->except(['show']);

// Student Attendance
Route::resource('student-attendances', 'StudentAttendancesController')->only(['index', 'create', 'store']);
Route::resource('staff-attendances', 'StaffAttendancesController')->only(['index', 'create', 'store']);

// Faculty Log Book
Route::post('faculty-log-books/{faculty_log_book}/approve', 'FacultyLogBooksController@approve')->name('faculty-log-books.approve');
Route::resource('faculty-log-books', 'FacultyLogBooksController')->except(['show', 'destroy']);

// Extra Classes
Route::post('extra-classes/{extra_class}/approve', 'ExtraClassesController@approve')->name('extra-classes.approve');
Route::post('extra-classes/{extra_class}/reject', 'ExtraClassesController@reject')->name('extra-classes.reject');
Route::resource('extra-classes', 'ExtraClassesController')->except(['show', 'destroy']);

// Salary Reports / Calculation
Route::get('salary-reports', 'SalaryReportsController@index')->name('salary-reports.index');
Route::post('salary-reports/calculate', 'SalaryReportsController@calculate')->name('salary-reports.calculate');

// WhatsApp
Route::resource('whatsapp-settings', 'WhatsappSettingsController')->only(['index', 'create', 'store', 'edit', 'update']);
Route::get('whatsapp-logs', 'WhatsappLogsController@index')->name('whatsapp-logs.index');

// Biometric Logs
Route::get('biometric-logs', 'BiometricLogsController@index')->name('biometric-logs.index');

// Exams
Route::delete('exams/destroy', 'ExamsController@massDestroy')->name('exams.massDestroy');
Route::post('exams/{exam}/results', 'ExamsController@storeResults')->name('exams.results.store');
Route::resource('exams', 'ExamsController');

// Study Materials
Route::delete('study-materials/destroy', 'StudyMaterialsController@massDestroy')->name('study-materials.massDestroy');
Route::delete('study-materials/media/{media}', 'StudyMaterialsController@deleteMedia')->name('study-materials.media.destroy');
Route::resource('study-materials', 'StudyMaterialsController');

// Timetable & Substitution
Route::post('timetables/{timetable}/substitute', 'TimetablesController@substitute')->name('timetables.substitute');
Route::resource('timetables', 'TimetablesController')->except(['show', 'destroy']);

// Homework
Route::resource('homeworks', 'HomeworksController')->only(['index', 'create', 'store', 'show']);
Route::delete('homeworks/destroy', 'HomeworksController@massDestroy')->name('homeworks.massDestroy');
// Student Remarks
Route::resource('student-remarks', 'StudentRemarksController')->only(['index', 'create', 'store']);

// Notices
Route::delete('notices/destroy', 'NoticesController@massDestroy')->name('notices.massDestroy');
Route::delete('notices/media/{media}', 'NoticesController@deleteMedia')->name('notices.media.destroy');
Route::resource('notices', 'NoticesController');

// Admissions
Route::delete('admissions/destroy', 'AdmissionsController@massDestroy')->name('admissions.massDestroy');
Route::delete('admissions/media/{media}', 'AdmissionsController@deleteMedia')->name('admissions.deleteMedia');
Route::resource('admissions', 'AdmissionsController');

// Fee Structures
Route::delete('fee-structures/destroy', 'FeeStructuresController@massDestroy')->name('fee-structures.massDestroy');
Route::resource('fee-structures', 'FeeStructuresController');

// Fee Installments
Route::post('fee-installments/{fee_installment}/remind', 'FeeInstallmentsController@remind')->name('fee-installments.remind');
Route::resource('fee-installments', 'FeeInstallmentsController')->only(['index', 'create', 'store']);

// Report Cards
Route::post('report-cards/generate', 'ReportCardsController@generate')->name('report-cards.generate');
Route::post('report-cards/{report_card}/publish', 'ReportCardsController@publish')->name('report-cards.publish');
Route::resource('report-cards', 'ReportCardsController')->only(['index']);

// Maintenance
Route::resource('maintenance-requests', 'MaintenanceRequestsController')->except(['show', 'destroy']);

// Inventory
Route::post('inventory-items/{inventory_item}/transaction', 'InventoryItemsController@transaction')->name('inventory-items.transaction');
Route::resource('inventory-items', 'InventoryItemsController')->except(['show', 'destroy']);

    
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
