<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffAttendancesController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('staff_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attendances = StaffAttendance::with(['user', 'teacher.user', 'staff.user', 'branch', 'batch'])
            ->when(! $this->erpScope()['is_admin'], fn ($query) => $this->scopeBranchQuery($query))
            ->latest('attendance_date')
            ->latest('id')
            ->get();

        return view('admin.staffAttendances.index', compact('attendances'));
    }

    public function create()
    {
        abort_if(Gate::denies('staff_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.staffAttendances.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('staff_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'employee_type' => ['required', 'in:teacher,staff'],
            'teacher_id' => ['nullable', 'required_if:employee_type,teacher', 'exists:teachers,id'],
            'staff_id' => ['nullable', 'required_if:employee_type,staff', 'exists:staff,id'],
            'batch_id' => ['nullable', 'required_if:employee_type,teacher', 'exists:batches,id'],
            'branch_id' => ['nullable', 'required_if:employee_type,staff', 'exists:branches,id'],
            'attendance_date' => ['required', 'date'],
            'first_in_time' => ['nullable', 'date_format:H:i'],
            'last_out_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,half_day'],
            'remarks' => ['nullable', 'string'],
        ]);

        $scope = $this->erpScope();

        if ($data['employee_type'] === 'teacher') {
            $teacher = $this->scopeBranchQuery(Teacher::query())->findOrFail($data['teacher_id']);
            $batch = $this->scopeBatchQuery(Batch::query())->findOrFail($data['batch_id']);
            abort_if($batch->branch_id !== $teacher->branch_id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Teacher and batch must belong to the same branch.');
            abort_if(! $this->teacherBatchIds($teacher->id)->contains($batch->id), Response::HTTP_UNPROCESSABLE_ENTITY, 'This batch is not assigned to the selected teacher.');

            $payload = [
                'user_id' => $teacher->user_id,
                'teacher_id' => $teacher->id,
                'staff_id' => null,
                'branch_id' => $teacher->branch_id,
                'batch_id' => $batch->id,
            ];
            $where = ['teacher_id' => $teacher->id, 'attendance_date' => $data['attendance_date'], 'batch_id' => $batch->id];
        } else {
            $staff = $this->scopeBranchQuery(Staff::query())->findOrFail($data['staff_id']);
            abort_if(! $scope['is_admin'] && $staff->branch_id != $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Invalid staff member.');
            abort_if($staff->branch_id != $data['branch_id'], Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected branch does not match the staff member branch.');

            $payload = [
                'user_id' => $staff->user_id,
                'teacher_id' => null,
                'staff_id' => $staff->id,
                'branch_id' => $staff->branch_id,
                'batch_id' => null,
            ];
            $where = ['staff_id' => $staff->id, 'attendance_date' => $data['attendance_date'], 'batch_id' => null];
        }

        $workedMinutes = $this->workedMinutes($data['first_in_time'] ?? null, $data['last_out_time'] ?? null);

        StaffAttendance::updateOrCreate($where, $payload + [
            'first_in_time' => $data['first_in_time'] ?? null,
            'last_out_time' => $data['last_out_time'] ?? null,
            'worked_minutes' => $workedMinutes,
            'status' => $data['status'],
            'source' => 'manual',
            'remarks' => $data['remarks'] ?? null,
        ]);

        return redirect()->route('admin.staff-attendances.index')->with('message', 'Attendance saved successfully.');
    }

    private function formData(): array
    {
        return [
            'teachers' => $this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? 'Teacher #' . $teacher->id]),
            'staffMembers' => $this->scopeBranchQuery(Staff::with('user'))->get()->mapWithKeys(fn ($staff) => [$staff->id => $staff->user->name ?? 'Staff #' . $staff->id]),
            'batches' => $this->scopeBatchQuery(Batch::query())->pluck('name', 'id'),
            'branches' => $this->scopeBranchQuery(Branch::query(), 'id')->pluck('name', 'id'),
        ];
    }

    private function workedMinutes(?string $firstIn, ?string $lastOut): int
    {
        if (! $firstIn || ! $lastOut) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i', $firstIn);
        $end = Carbon::createFromFormat('H:i', $lastOut);

        return $end->greaterThan($start) ? $start->diffInMinutes($end) : 0;
    }
}
