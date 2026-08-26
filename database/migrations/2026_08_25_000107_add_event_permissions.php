<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $all = [
            'event_access', 'event_create', 'event_edit', 'event_show', 'event_delete',
            'event_enroll', 'event_attendance_mark', 'event_certificate_mark', 'event_fee_rule_manage', 'event_payment_collect',
            'external_contact_access', 'external_contact_create', 'external_contact_edit', 'external_contact_show', 'external_contact_delete',
        ];

        $ids = collect($all)->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($ids->values());

        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'event_access', 'event_create', 'event_edit', 'event_show',
            'event_enroll', 'event_attendance_mark', 'event_certificate_mark', 'event_fee_rule_manage', 'event_payment_collect',
            'external_contact_access', 'external_contact_create', 'external_contact_edit', 'external_contact_show',
        ])->values());

        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'event_access', 'event_show', 'event_enroll', 'event_payment_collect',
            'external_contact_access', 'external_contact_create', 'external_contact_edit', 'external_contact_show',
        ])->values());

        foreach (['Teacher', 'Student', 'Parent'] as $title) {
            Role::where('title', $title)->first()?->permissions()->syncWithoutDetaching($ids->only([
                'event_access', 'event_show',
            ])->values());
        }
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'event_access', 'event_create', 'event_edit', 'event_show', 'event_delete',
            'event_enroll', 'event_attendance_mark', 'event_certificate_mark', 'event_fee_rule_manage', 'event_payment_collect',
            'external_contact_access', 'external_contact_create', 'external_contact_edit', 'external_contact_show', 'external_contact_delete',
        ])->delete();
    }
};
