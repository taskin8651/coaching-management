<?php

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (auth()->check() && auth()->user()->studentProfile()->exists()) {
        if (session('status')) {
            return redirect()->route('admin.my-portal.index')->with('status', session('status'));
        }

        return redirect()->route('admin.my-portal.index');
    }

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
Route::post('enquiries/{enquiry}/convert', 'EnquiriesController@convert')->name('enquiries.convert');
Route::resource('enquiries', 'EnquiriesController');

// Fee Payments
Route::delete('fee-payments/destroy', 'FeePaymentsController@massDestroy')->name('fee-payments.massDestroy');
Route::resource('fee-payments', 'FeePaymentsController');
// Fee Payment Invoice
Route::get('fee-payments/{fee_payment}/invoice', 'FeePaymentsController@invoice')->name('fee-payments.invoice');
// Fee Payment Cancel
Route::post('fee-payments/{fee_payment}/cancel', 'FeePaymentsController@cancel')->name('fee-payments.cancel');

// Expenses
Route::delete('expenses/destroy', 'ExpensesController@massDestroy')->name('expenses.massDestroy');
Route::resource('expenses', 'ExpensesController');

// Salary Payments
Route::delete('salary-payments/destroy', 'SalaryPaymentsController@massDestroy')->name('salary-payments.massDestroy');
Route::get('salary-payments/{salary_payment}/slip', 'SalaryPaymentsController@slip')->name('salary-payments.slip');
Route::resource('salary-payments', 'SalaryPaymentsController');

// Student Batch Assignments
Route::get('student-batches/matrix', 'StudentBatchesController@matrix')->name('student-batches.matrix');
Route::resource('student-batches', 'StudentBatchesController')->except(['show']);

// Student Attendance
Route::resource('student-attendances', 'StudentAttendancesController')->only(['index', 'create', 'store']);
Route::resource('teacher-attendances', 'TeacherAttendancesController')->only(['index', 'create', 'store']);
Route::resource('staff-attendances', 'StaffAttendancesController')->only(['index', 'create', 'store']);

// Faculty Log Book
Route::post('faculty-log-books/{faculty_log_book}/approve', 'FacultyLogBooksController@approve')->name('faculty-log-books.approve');
Route::get('faculty-log-books/timetable', 'FacultyLogBooksController@timetable')->name('faculty-log-books.timetable');
Route::delete('faculty-log-books/media/{media}', 'FacultyLogBooksController@deleteMedia')->name('faculty-log-books.media.destroy');
Route::resource('faculty-log-books', 'FacultyLogBooksController')->except([ 'destroy']);

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

// Exam Types
Route::delete('exam-types/destroy', 'ExamTypesController@massDestroy')->name('exam-types.massDestroy');
Route::resource('exam-types', 'ExamTypesController');

// Exams
Route::delete('exams/destroy', 'ExamsController@massDestroy')->name('exams.massDestroy');
Route::post('exams/{exam}/results', 'ExamsController@storeResults')->name('exams.results.store');
Route::post('exams/{exam}/self-assessment', 'ExamsController@storeSelfAssessment')->name('exams.selfAssessment.store');
Route::resource('exams', 'ExamsController');

// Study Materials
Route::delete('study-materials/destroy', 'StudyMaterialsController@massDestroy')->name('study-materials.massDestroy');
Route::delete('study-materials/media/{media}', 'StudyMaterialsController@deleteMedia')->name('study-materials.media.destroy');
Route::resource('study-materials', 'StudyMaterialsController');

// Timetable & Substitution
Route::get('timetable-substitutions/free-teachers', 'TimetableSubstitutionsController@freeTeachers')->name('timetable-substitutions.free-teachers');
Route::resource('timetable-substitutions', 'TimetableSubstitutionsController')
    ->parameters(['timetable-substitutions' => 'timetableSubstitution']);
Route::post('timetables/{timetable}/substitute', 'TimetablesController@substitute')->name('timetables.substitute');
Route::resource('timetables', 'TimetablesController')->except(['show']);

// Staff Duty Timetable
Route::delete('staff-timetables/destroy', 'StaffTimetablesController@massDestroy')->name('staff-timetables.massDestroy');
Route::resource('staff-timetables', 'StaffTimetablesController')->except(['show']);

// Homework
Route::delete('homeworks/destroy', 'HomeworksController@massDestroy')->name('homeworks.massDestroy');
Route::delete('homeworks/media/{media}', 'HomeworksController@deleteMedia')->name('homeworks.media.destroy');
Route::post('homeworks/{homework}/approve', 'HomeworksController@approve')->name('homeworks.approve');
Route::resource('homeworks', 'HomeworksController')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
// Student Remarks
Route::post('student-remarks/{student_remark}/approve', 'StudentRemarksController@approve')->name('student-remarks.approve');
Route::resource('student-remarks', 'StudentRemarksController')->only(['index', 'create', 'store']);

// Notices
Route::delete('notices/destroy', 'NoticesController@massDestroy')->name('notices.massDestroy');
Route::delete('notices/media/{media}', 'NoticesController@deleteMedia')->name('notices.media.destroy');
Route::resource('notices', 'NoticesController');

// Fee Heads (Fee Master)
Route::delete('fee-heads/destroy', 'FeeHeadsController@massDestroy')->name('fee-heads.massDestroy');
Route::resource('fee-heads', 'FeeHeadsController');

// Fee Accounts
Route::delete('fee-accounts/destroy', 'FeeAccountsController@massDestroy')->name('fee-accounts.massDestroy');
Route::resource('fee-accounts', 'FeeAccountsController');

// Fee Structures
Route::delete('fee-structures/destroy', 'FeeStructuresController@massDestroy')->name('fee-structures.massDestroy');
Route::resource('fee-structures', 'FeeStructuresController');
Route::post('fee-structures/{fee_structure}/assign', 'FeeStructuresController@assignToStudents')->name('fee-structures.assign');

// Fee Installments
Route::post('fee-installments/{fee_installment}/remind', 'FeeInstallmentsController@remind')->name('fee-installments.remind');
Route::post('fee-installments/{fee_installment}/apply-late-fee', 'FeeInstallmentsController@applyLateFee')->name('fee-installments.applyLateFee');
Route::resource('fee-installments', 'FeeInstallmentsController')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

// Concessions
Route::post('concessions/{concession}/approve', 'ConcessionsController@approve')->name('concessions.approve');
Route::post('concessions/{concession}/reject', 'ConcessionsController@reject')->name('concessions.reject');
Route::resource('concessions', 'ConcessionsController')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

// Refunds
Route::post('refunds/{refund}/approve', 'RefundsController@approve')->name('refunds.approve');
Route::post('refunds/{refund}/reject', 'RefundsController@reject')->name('refunds.reject');
Route::post('refunds/{refund}/complete', 'RefundsController@complete')->name('refunds.complete');
Route::resource('refunds', 'RefundsController')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

// Student Fee Ledgers
Route::post('student-fee-ledgers/{student_fee_ledger}/apply-credit', 'StudentCreditTransactionsController@apply')->name('student-fee-ledgers.applyCredit');
Route::resource('student-fee-ledgers', 'StudentFeeLedgersController')->only(['index', 'show']);

// Student Credits
Route::get('student-credits', 'StudentCreditTransactionsController@index')->name('student-credits.index');

// Events
Route::delete('events/destroy', 'EventsController@massDestroy')->name('events.massDestroy');
Route::resource('events', 'EventsController');
Route::post('events/{event}/publish', 'EventsController@publish')->name('events.publish');
Route::post('events/{event}/close', 'EventsController@close')->name('events.close');
Route::post('events/{event}/reopen', 'EventsController@reopen')->name('events.reopen');
Route::post('events/{event}/cancel', 'EventsController@cancel')->name('events.cancel');
Route::post('events/{event}/bulk-enroll', 'EventsController@bulkEnroll')->name('events.bulkEnroll');
Route::post('events/{event}/fee-rules', 'EventsController@storeFeeRule')->name('events.feeRules.store');
Route::put('events/{event}/fee-rules/{fee_rule}', 'EventsController@updateFeeRule')->name('events.feeRules.update');
Route::delete('events/{event}/fee-rules/{fee_rule}', 'EventsController@destroyFeeRule')->name('events.feeRules.destroy');

// Event Enrollments
Route::post('events/{event}/enrollments', 'EventEnrollmentsController@store')->name('event-enrollments.store');
Route::post('event-enrollments/{event_enrollment}/cancel', 'EventEnrollmentsController@cancel')->name('event-enrollments.cancel');
Route::post('event-enrollments/{event_enrollment}/attendance', 'EventEnrollmentsController@markAttendance')->name('event-enrollments.attendance');
Route::post('event-enrollments/{event_enrollment}/certificate', 'EventEnrollmentsController@markCertificate')->name('event-enrollments.certificate');
Route::post('event-enrollments/{event_enrollment}/complimentary', 'EventEnrollmentsController@markComplimentary')->name('event-enrollments.complimentary');
Route::post('event-enrollments/{event_enrollment}/collect-payment', 'EventEnrollmentsController@collectPayment')->name('event-enrollments.collectPayment');

// External Contacts
Route::get('external-contacts/search', 'ExternalContactsController@search')->name('external-contacts.search');
Route::delete('external-contacts/destroy', 'ExternalContactsController@massDestroy')->name('external-contacts.massDestroy');
Route::resource('external-contacts', 'ExternalContactsController');

// Report Cards
Route::get('report-cards/export', 'ReportCardsController@export')->name('report-cards.export');
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
