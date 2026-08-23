<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'dashboard_access',
            'my_portal_access',

            'user_management_access',

            'permission_create',
            'permission_edit',
            'permission_show',
            'permission_delete',
            'permission_access',

            'role_create',
            'role_edit',
            'role_show',
            'role_delete',
            'role_access',

            'user_create',
            'user_edit',
            'user_show',
            'user_delete',
            'user_access',

            'audit_log_show',
            'audit_log_access',

            'epaper_create',
            'epaper_edit',
            'epaper_show',
            'epaper_delete',
            'epaper_access',

            'profile_password_edit',
            'profile_password_update',
            'profile_password_destroy',
            'profile_password_updateProfile',

            'branch_create',
            'branch_edit',
            'branch_show',
            'branch_delete',
            'branch_access',

            'course_create',
            'course_edit',
            'course_show',
            'course_delete',
            'course_access',

            'subject_create',
            'subject_edit',
            'subject_show',
            'subject_delete',
            'subject_access',

            'batch_create',
            'batch_edit',
            'batch_show',
            'batch_delete',
            'batch_access',

            'teacher_create',
            'teacher_edit',
            'teacher_show',
            'teacher_delete',
            'teacher_access',

            'staff_create',
            'staff_edit',
            'staff_show',
            'staff_delete',
            'staff_access',

            'student_create',
            'student_edit',
            'student_show',
            'student_delete',
            'student_access',

            'enquiry_create',
            'enquiry_edit',
            'enquiry_show',
            'enquiry_delete',
            'enquiry_access',
            'enquiry_follow_up_create',

            'admission_create',
            'admission_edit',
            'admission_show',
            'admission_delete',
            'admission_access',

            'fee_structure_create',
            'fee_structure_edit',
            'fee_structure_show',
            'fee_structure_delete',
            'fee_structure_access',

            'fee_payment_create',
            'fee_payment_edit',
            'fee_payment_show',
            'fee_payment_delete',
            'fee_payment_access',

            'expense_create',
            'expense_edit',
            'expense_show',
            'expense_delete',
            'expense_access',

            'salary_payment_create',
            'salary_payment_edit',
            'salary_payment_show',
            'salary_payment_delete',
            'salary_payment_access',

            'exam_create',
            'exam_edit',
            'exam_show',
            'exam_delete',
            'exam_access',
            'exam_result_create',

            'exam_type_create',
            'exam_type_edit',
            'exam_type_show',
            'exam_type_delete',
            'exam_type_access',

            'study_material_create',
            'study_material_edit',
            'study_material_show',
            'study_material_delete',
            'study_material_access',

            'notice_create',
            'notice_edit',
            'notice_show',
            'notice_delete',
            'notice_access',

            'whatsapp_settings_access',
            'whatsapp_settings_create',
            'whatsapp_settings_edit',
            'whatsapp_logs_access',

            'biometric_logs_access',

            'student_batch_access',
            'student_batch_create',
            'student_batch_edit',
            'student_batch_delete',

            'student_attendance_access',
            'student_attendance_create',

            'teacher_attendance_access',
            'teacher_attendance_create',

            'staff_attendance_access',
            'staff_attendance_create',

            'faculty_log_access',
            'faculty_log_create',
            'faculty_log_edit',
            'faculty_log_approve',

            'extra_class_access',
            'extra_class_create',
            'extra_class_edit',
            'extra_class_show',
            'extra_class_delete',
            'extra_class_approve',

            'salary_calculate',
            'salary_report_access',

            'timetable_access',
            'timetable_create',
            'timetable_edit',
            'timetable_delete',
            'timetable_substitute',

            'homework_access',
            'homework_create',
            'homework_show',
            'homework_edit',
            'homework_delete',
            'homework_approve',

            'student_remark_access',
            'student_remark_create',
            'student_remark_approve',

            'maintenance_access',
            'maintenance_create',
            'maintenance_edit',

            'inventory_access',
            'inventory_create',
            'inventory_edit',
            'inventory_transaction_create',

            'fee_installment_access',
            'fee_installment_create',
            'fee_installment_remind',

            'report_card_access',
            'report_card_create',
            'report_card_publish',
            'faculty_log_show',
        ];

        foreach ($permissions as $title) {
            Permission::firstOrCreate([
                'title' => $title,
            ]);
        }
    }
}