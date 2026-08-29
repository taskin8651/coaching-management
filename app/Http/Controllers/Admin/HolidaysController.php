<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Branch;
use App\Models\Holiday;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HolidaysController extends Controller
{
    use AppliesErpScope;

    public function index(Request $request)
    {
        abort_if(Gate::denies('holiday_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();

        $holidays = Holiday::with('branch');

        if (! $scope['is_admin']) {
            $scope['branch_id']
                ? $holidays->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $scope['branch_id']))
                : $holidays->whereNull('branch_id');
        }

        if ($request->filled('branch_id')) {
            $holidays->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('month')) {
            $holidays->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->input('month')]);
        }

        $holidays = $holidays->orderBy('date')->get();

        $branches = Branch::where('status', 'active')
            ->when(! $scope['is_admin'], fn ($q) => $scope['branch_id'] ? $q->where('id', $scope['branch_id']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id');

        return view('admin.holidays.index', compact('holidays', 'scope', 'branches'));
    }

    public function create()
    {
        abort_if(Gate::denies('holiday_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.holidays.create', $this->formData());
    }

    public function store(StoreHolidayRequest $request)
    {
        $data = $request->validated();
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            abort_if(! $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $scope['branch_id'];
        }

        $data['created_by_id'] = auth()->id();

        Holiday::create($data);

        return redirect()->route('admin.holidays.index')->with('message', 'Holiday added successfully.');
    }

    public function show(Holiday $holiday)
    {
        abort_if(Gate::denies('holiday_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkViewAccess($holiday);

        $holiday->load(['branch', 'createdBy']);

        return view('admin.holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday)
    {
        abort_if(Gate::denies('holiday_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkManageAccess($holiday);

        return view('admin.holidays.edit', $this->formData() + compact('holiday'));
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        $this->checkManageAccess($holiday);

        $data = $request->validated();
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            abort_if(! $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $scope['branch_id'];
        }

        $holiday->update($data);

        return redirect()->route('admin.holidays.index')->with('message', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        abort_if(Gate::denies('holiday_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkManageAccess($holiday);

        $holiday->delete();

        return back()->with('message', 'Holiday deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('holiday_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();

        $query = Holiday::whereIn('id', request('ids'));

        if (! $scope['is_admin']) {
            // Branch Manager can only mass-delete their own branch's holidays — never global
            // ones, and never another branch's, even by id-guessing.
            abort_if(! $scope['is_manager'] || ! $scope['branch_id'], Response::HTTP_FORBIDDEN, '403 Forbidden');

            $query->where('branch_id', $scope['branch_id']);
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Lenient — used for viewing: global holidays or the viewer's own branch.
     */
    private function checkViewAccess(Holiday $holiday): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            $holiday->branch_id && $holiday->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    /**
     * Strict — used for edit/update/destroy. Deliberately stricter than checkViewAccess(): a
     * Branch Manager may NEVER manage a global (branch_id null) holiday, only their own branch's
     * — only Admin manages global holidays. This is intentionally different from how Events
     * scopes manager access, per the confirmed requirement for this feature.
     */
    private function checkManageAccess(Holiday $holiday): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            ! $scope['is_manager'] || $holiday->branch_id === null || $holiday->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    private function formData(): array
    {
        $scope = $this->erpScope();

        $branches = Branch::where('status', 'active')
            ->when(! $scope['is_admin'], fn ($q) => $scope['branch_id'] ? $q->where('id', $scope['branch_id']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend('All Branches', '');

        return [
            'branches' => $branches,
            'scope' => $scope,
        ];
    }
}
