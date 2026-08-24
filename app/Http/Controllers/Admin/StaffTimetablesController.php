<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\StaffTimetable;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffTimetablesController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('staff_timetable_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();

        $timetables = StaffTimetable::with(['branch', 'staff.user']);

        if ($scope['is_staff'] && $scope['staff_id']) {
            $timetables->where('staff_id', $scope['staff_id']);
        } elseif (! $scope['is_admin']) {
            $this->scopeBranchQuery($timetables);
        }

        $timetables = $timetables->latest()->get();

        $staffWiseTimetables = $timetables->groupBy('staff_id');

        $staffList = $this->scopeBranchQuery(Staff::with('user'))
            ->get()
            ->mapWithKeys(fn ($member) => [$member->id => $member->user->name ?? 'Staff #' . $member->id])
            ->prepend('All Staff', '');

        return view('admin.staffTimetables.index', compact('timetables', 'staffWiseTimetables', 'staffList'));
    }

    public function create()
    {
        abort_if(Gate::denies('staff_timetable_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.staffTimetables.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('staff_timetable_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
        }

        StaffTimetable::create($data);

        return redirect()
            ->route('admin.staff-timetables.index')
            ->with('message', 'Staff duty schedule saved successfully.');
    }

    public function edit(StaffTimetable $staffTimetable)
    {
        abort_if(Gate::denies('staff_timetable_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($staffTimetable);

        return view('admin.staffTimetables.edit', $this->formData() + compact('staffTimetable'));
    }

    public function update(Request $request, StaffTimetable $staffTimetable)
    {
        abort_if(Gate::denies('staff_timetable_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($staffTimetable);

        $data = $this->validated($request);
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
        }

        $staffTimetable->update($data);

        return redirect()
            ->route('admin.staff-timetables.index')
            ->with('message', 'Staff duty schedule updated successfully.');
    }

    public function destroy(StaffTimetable $staffTimetable)
    {
        abort_if(Gate::denies('staff_timetable_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($staffTimetable);

        $staffTimetable->delete();

        return back()->with('message', 'Staff duty schedule deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('staff_timetable_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = StaffTimetable::whereIn('id', request('ids'));

        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $this->scopeBranchQuery($query);
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function formData(): array
    {
        return [
            'branches' => $this->scopeBranchQuery(Branch::query(), 'id')
                ->pluck('name', 'id')
                ->prepend('Optional', ''),

            'staffMembers' => $this->scopeBranchQuery(Staff::with('user'))
                ->get()
                ->mapWithKeys(fn ($member) => [$member->id => $member->user->name ?? 'Staff #' . $member->id])
                ->prepend(trans('global.pleaseSelect'), ''),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'staff_id' => ['required', 'exists:staff,id'],
            'day_of_week' => ['nullable', 'string', 'max:20'],
            'schedule_date' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,cancelled'],
            'remarks' => ['nullable', 'string'],
        ]);
    }
}
