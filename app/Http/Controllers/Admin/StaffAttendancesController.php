<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffAttendancesController extends Controller
{
    use AppliesErpScope;

    public function index(Request $request)
    {
        abort_if(Gate::denies('staff_attendance_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();

        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'in:present,absent,late,half_day,leave'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $attendances = StaffAttendance::with(['user', 'staff.user', 'branch'])
            ->whereNotNull('staff_id')
            ->when($scope['is_staff'] && $scope['staff_id'], fn ($query) => $query->where('staff_id', $scope['staff_id']))
            ->when(! $scope['is_admin'] && ! $scope['is_staff'], fn ($query) => $this->scopeBranchQuery($query))
            ->when($scope['is_admin'] && ! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('attendance_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('attendance_date', '<=', $filters['date_to']))
            ->latest('attendance_date')
            ->latest('id')
            ->get();

        return view('admin.staffAttendances.index', compact('attendances', 'filters') + [
            'branches' => Branch::query()
                ->when(! $scope['is_admin'], fn ($q) => $q->where('id', $scope['branch_id']))
                ->pluck('name', 'id')
                ->prepend('All Branches', ''),
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('staff_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.staffAttendances.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('staff_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'attendance_date' => ['required', 'date'],
            'first_in_time' => ['nullable', 'date_format:H:i'],
            'last_out_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,half_day'],
            'remarks' => ['nullable', 'string'],
        ]);

        $scope = $this->erpScope();

        $staff = $this->scopeBranchQuery(Staff::query())->findOrFail($data['staff_id']);
        abort_if(! $scope['is_admin'] && $staff->branch_id != $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Invalid staff member.');
        abort_if($staff->branch_id != $data['branch_id'], Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected branch does not match the staff member branch.');

        $payload = ['user_id' => $staff->user_id, 'teacher_id' => null, 'staff_id' => $staff->id, 'branch_id' => $staff->branch_id, 'batch_id' => null];
        $where = ['staff_id' => $staff->id, 'attendance_date' => $data['attendance_date'], 'batch_id' => null];

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
            'staffMembers' => $this->scopeBranchQuery(Staff::with('user'))->get()->mapWithKeys(fn ($staff) => [$staff->id => $staff->user->name ?? 'Staff #' . $staff->id]),
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
