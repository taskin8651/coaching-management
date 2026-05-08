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

// Exams
Route::delete('exams/destroy', 'ExamsController@massDestroy')->name('exams.massDestroy');
Route::post('exams/{exam}/results', 'ExamsController@storeResults')->name('exams.results.store');
Route::resource('exams', 'ExamsController');

// Study Materials
Route::delete('study-materials/destroy', 'StudyMaterialsController@massDestroy')->name('study-materials.massDestroy');
Route::delete('study-materials/media/{media}', 'StudyMaterialsController@deleteMedia')->name('study-materials.media.destroy');
Route::resource('study-materials', 'StudyMaterialsController');

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

