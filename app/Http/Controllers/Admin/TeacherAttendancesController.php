<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherAttendancesController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('teacher_attendance_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $scope = $this->erpScope();
        $attendances = StaffAttendance::with(['teacher.user', 'branch', 'batch'])
            ->whereNotNull('teacher_id')
            ->when($scope['is_teacher'] && $scope['teacher_id'], fn ($query) => $query->where('teacher_id', $scope['teacher_id']))
            ->when(! $scope['is_admin'] && ! $scope['is_teacher'], fn ($query) => $this->scopeBranchQuery($query))
            ->latest('attendance_date')->latest('id')->get();

        return view('admin.teacherAttendances.index', compact('attendances'));
    }

    public function create()
    {
        abort_if(Gate::denies('teacher_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.teacherAttendances.create', [
            'teachers' => $this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? 'Teacher #' . $teacher->id]),
            'batches' => $this->scopeBatchQuery(Batch::query())->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('teacher_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'], 'batch_id' => ['required', 'exists:batches,id'],
            'attendance_date' => ['required', 'date'], 'first_in_time' => ['nullable', 'date_format:H:i'],
            'last_out_time' => ['nullable', 'date_format:H:i'], 'status' => ['required', 'in:present,absent,late,half_day'], 'remarks' => ['nullable', 'string'],
        ]);
        $teacher = $this->scopeBranchQuery(Teacher::query())->findOrFail($data['teacher_id']);
        $batch = $this->scopeBatchQuery(Batch::query())->findOrFail($data['batch_id']);
        abort_if($batch->branch_id !== $teacher->branch_id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Teacher and batch must belong to the same branch.');
        abort_if(! $this->teacherBatchIds($teacher->id)->contains($batch->id), Response::HTTP_UNPROCESSABLE_ENTITY, 'This batch is not assigned to the selected teacher.');
        $minutes = ($data['first_in_time'] ?? null) && ($data['last_out_time'] ?? null) && Carbon::createFromFormat('H:i', $data['last_out_time'])->greaterThan(Carbon::createFromFormat('H:i', $data['first_in_time'])) ? Carbon::createFromFormat('H:i', $data['first_in_time'])->diffInMinutes(Carbon::createFromFormat('H:i', $data['last_out_time'])) : 0;
        StaffAttendance::updateOrCreate(
            ['teacher_id' => $teacher->id, 'attendance_date' => $data['attendance_date'], 'batch_id' => $batch->id],
            ['user_id' => $teacher->user_id, 'staff_id' => null, 'branch_id' => $teacher->branch_id, 'first_in_time' => $data['first_in_time'] ?? null, 'last_out_time' => $data['last_out_time'] ?? null, 'worked_minutes' => $minutes, 'status' => $data['status'], 'source' => 'manual', 'remarks' => $data['remarks'] ?? null]
        );
        return redirect()->route('admin.teacher-attendances.index')->with('message', 'Teacher attendance saved successfully.');
    }
}
