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

