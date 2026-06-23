<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'audit_log_show',
            ],
            [
                'id'    => 18,
                'title' => 'audit_log_access',
            ],
            [
                'id'    => 19,
                'title' => 'epaper_create',
            ],
            [
                'id'    => 20,
                'title' => 'epaper_edit',
            ],
            [
                'id'    => 21,
                'title' => 'epaper_show',
            ],
            [
                'id'    => 22,
                'title' => 'epaper_delete',
            ],
            [
                'id'    => 23,
                'title' => 'epaper_access',
            ],
            [
                'id'    => 24,
                'title' => 'profile_password_edit',
            ],
                [
                    'id'    => 25,
                    'title' => 'profile_password_update',
                ],
                [
                    'id'    => 26,
                    'title' => 'profile_password_destroy',
                ],
                [
                    'id'    => 27,
                    'title' => 'profile_password_updateProfile',
                ],
                [
                    'id'    => 28,
                    'title' => 'branch_create',
                ],
                [
                    'id'    => 29,
                    'title' => 'branch_edit',
                ],
                [
                    'id'    => 30,
                    'title' => 'branch_show',
                ],
                [
                    'id'    => 31,
                    'title' => 'branch_delete',
                ],
                [
                    'id'    => 32,
                    'title' => 'branch_access',
                ],
                [
                    'id'    => 33,
                    'title' => 'course_create',
                ],
                [
                    'id'    => 34,
                    'title' => 'course_edit',
                ],
                [
                    'id'    => 35,
                    'title' => 'course_show',
                ],
                [
                    'id'    => 36,
                    'title' => 'course_delete',
                ],
                [
                    'id'    => 37,
                    'title' => 'course_access',
                ],
        ];

        $nextPermissions = [
            'subject_create', 'subject_edit', 'subject_show', 'subject_delete', 'subject_access',
            'batch_create', 'batch_edit', 'batch_show', 'batch_delete', 'batch_access',
            'teacher_create', 'teacher_edit', 'teacher_show', 'teacher_delete', 'teacher_access',
            'staff_create', 'staff_edit', 'staff_show', 'staff_delete', 'staff_access',
            'student_create', 'student_edit', 'student_show', 'student_delete', 'student_access',
            'enquiry_create', 'enquiry_edit', 'enquiry_show', 'enquiry_delete', 'enquiry_access', 'enquiry_follow_up_create',
            'admission_create', 'admission_edit', 'admission_show', 'admission_delete', 'admission_access',
            'fee_structure_create', 'fee_structure_edit', 'fee_structure_show', 'fee_structure_delete', 'fee_structure_access',
            'fee_payment_create', 'fee_payment_edit', 'fee_payment_show', 'fee_payment_delete', 'fee_payment_access',
            'expense_create', 'expense_edit', 'expense_show', 'expense_delete', 'expense_access',
            'salary_payment_create', 'salary_payment_edit', 'salary_payment_show', 'salary_payment_delete', 'salary_payment_access',
            'exam_create', 'exam_edit', 'exam_show', 'exam_delete', 'exam_access', 'exam_result_create',
            'study_material_create', 'study_material_edit', 'study_material_show', 'study_material_delete', 'study_material_access',
            'notice_create', 'notice_edit', 'notice_show', 'notice_delete', 'notice_access',
            'whatsapp_settings_access', 'whatsapp_settings_create', 'whatsapp_settings_edit', 'whatsapp_logs_access',
            'biometric_logs_access',
            'student_batch_access', 'student_batch_create', 'student_batch_edit', 'student_batch_delete',
            'student_attendance_access', 'student_attendance_create',
            'teacher_attendance_access', 'teacher_attendance_create',
            'staff_attendance_access', 'staff_attendance_create',
            'faculty_log_access', 'faculty_log_create', 'faculty_log_edit', 'faculty_log_approve',
            'extra_class_access', 'extra_class_create', 'extra_class_edit', 'extra_class_show', 'extra_class_delete', 'extra_class_approve',
            'salary_calculate', 'salary_report_access',
            'timetable_access', 'timetable_create', 'timetable_edit', 'timetable_substitute',
            'homework_access', 'homework_create', 'homework_show',
            'student_remark_access', 'student_remark_create',
            'maintenance_access', 'maintenance_create', 'maintenance_edit',
            'inventory_access', 'inventory_create', 'inventory_edit', 'inventory_transaction_create',
            'fee_installment_access', 'fee_installment_create', 'fee_installment_remind',
            'report_card_access', 'report_card_create', 'report_card_publish',
        ];

        foreach ($nextPermissions as $title) {
            $permissions[] = ['title' => $title];
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['title' => $permission['title']], $permission);
        }
    }
}
