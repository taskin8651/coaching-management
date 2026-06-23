<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        $admin_permissions = Permission::all();
        Role::findOrFail(1)->permissions()->sync($admin_permissions->pluck('id'));

        $user_permissions = $admin_permissions->filter(function ($permission) {
            return substr($permission->title, 0, 5) != 'user_' && substr($permission->title, 0, 5) != 'role_' && substr($permission->title, 0, 11) != 'permission_';
        });
        Role::findOrFail(2)->permissions()->sync($user_permissions->pluck('id'));

        $this->syncRole('Branch Manager', [
            'branch_access', 'course_access', 'subject_access', 'batch_access',
            'teacher_access', 'staff_access', 'student_access',
            'enquiry_access', 'enquiry_create', 'enquiry_edit', 'enquiry_show', 'enquiry_follow_up_create',
            'admission_access', 'admission_create', 'admission_edit', 'admission_show',
            'fee_structure_access', 'fee_payment_access', 'fee_payment_create', 'fee_payment_show',
            'fee_installment_access', 'fee_installment_create', 'fee_installment_remind',
            'expense_access', 'expense_create', 'expense_show',
            'salary_payment_access', 'salary_payment_show', 'salary_report_access', 'salary_calculate',
            'exam_access', 'exam_create', 'exam_edit', 'exam_show', 'exam_result_create',
            'report_card_access', 'report_card_create', 'report_card_publish',
            'study_material_access', 'study_material_create', 'study_material_edit', 'study_material_show',
            'notice_access', 'notice_create', 'notice_edit', 'notice_show',
            'student_batch_access', 'student_batch_create', 'student_batch_edit', 'student_batch_delete',
            'student_attendance_access', 'student_attendance_create',
            'teacher_attendance_access', 'teacher_attendance_create', 'staff_attendance_access', 'staff_attendance_create',
            'faculty_log_access', 'faculty_log_create', 'faculty_log_edit', 'faculty_log_approve',
            'extra_class_access', 'extra_class_create', 'extra_class_edit', 'extra_class_approve',
            'timetable_access', 'timetable_create', 'timetable_edit', 'timetable_substitute',
            'homework_access', 'homework_create', 'homework_show',
            'student_remark_access', 'student_remark_create',
            'maintenance_access', 'maintenance_create', 'maintenance_edit',
            'inventory_access', 'inventory_create', 'inventory_edit', 'inventory_transaction_create',
            'whatsapp_logs_access', 'biometric_logs_access',
        ]);

        $this->syncRole('Teacher', [
            'student_access', 'student_show',
            'exam_access', 'exam_show', 'exam_result_create',
            'report_card_access',
            'study_material_access', 'study_material_create', 'study_material_edit', 'study_material_show',
            'notice_access', 'notice_show',
            'student_attendance_access',
            'teacher_attendance_access',
            'faculty_log_access', 'faculty_log_create', 'faculty_log_edit',
            'extra_class_access',
            'timetable_access',
            'homework_access', 'homework_create', 'homework_show',
            'student_remark_access', 'student_remark_create',
            'salary_report_access', 'salary_payment_access', 'salary_payment_show',
        ]);

        $this->syncRole('Staff', [
            'student_access', 'student_show',
            'enquiry_access', 'enquiry_create', 'enquiry_edit', 'enquiry_show', 'enquiry_follow_up_create',
            'admission_access', 'admission_show',
            'fee_payment_access', 'fee_payment_create', 'fee_payment_show',
            'fee_installment_access', 'fee_installment_remind',
            'expense_access', 'expense_create', 'expense_show',
            'notice_access', 'notice_show',
            'maintenance_access', 'maintenance_create', 'maintenance_edit',
            'inventory_access', 'inventory_transaction_create',
            'staff_attendance_access',
        ]);

        $this->syncRole('Student', [
            'student_access', 'student_show',
            'admission_access', 'admission_show',
            'fee_payment_access', 'fee_payment_show',
            'fee_installment_access',
            'exam_access', 'exam_show',
            'report_card_access',
            'study_material_access', 'study_material_show',
            'notice_access', 'notice_show',
            'student_attendance_access',
            'homework_access', 'homework_show',
            'student_remark_access',
        ]);

        $this->syncRole('Parent', [
            'student_access', 'student_show',
            'admission_access', 'admission_show',
            'fee_payment_access', 'fee_payment_show',
            'fee_installment_access',
            'exam_access', 'exam_show',
            'report_card_access',
            'study_material_access', 'study_material_show',
            'notice_access', 'notice_show',
            'student_attendance_access',
            'homework_access', 'homework_show',
            'student_remark_access',
        ]);
    }

    private function syncRole(string $roleTitle, array $permissionTitles): void
    {
        $role = Role::where('title', $roleTitle)->first();

        if (! $role) {
            return;
        }

        $permissionIds = Permission::whereIn('title', $permissionTitles)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }
}
