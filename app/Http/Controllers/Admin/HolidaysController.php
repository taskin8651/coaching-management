<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
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
            $monthStart = \Illuminate\Support\Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()->toDateString();
            $monthEnd = \Illuminate\Support\Carbon::createFromFormat('Y-m', $request->input('month'))->endOfMonth()->toDateString();
            $holidays->whereRaw('COALESCE(start_date, date) <= ?', [$monthEnd])
                ->whereRaw('COALESCE(end_date, date) >= ?', [$monthStart]);
        }

        $holidays = $holidays->orderByRaw('COALESCE(start_date, date)')->get();

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
        $data['date'] = $data['start_date'];

        DB::transaction(function () use ($data) {
            $targetIds = $this->validatedTargets($data);
            $data['branch_id'] = $this->legacyBranchId($data);
            unset($data['target_ids'], $data['target_branch_id']);
            $holiday = Holiday::create($data);
            foreach ($targetIds as $id) {
                $holiday->targets()->create(['target_type' => $this->targetEntity($data['target_mode']), 'target_id' => $id]);
            }
            return $holiday;
        });

        return redirect()->route('admin.holidays.index')->with('message', 'Holiday added successfully.');
    }

    public function show(Holiday $holiday)
    {
        abort_if(Gate::denies('holiday_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkViewAccess($holiday);

        $holiday->load(['branch', 'createdBy', 'targets']);
        

        return view('admin.holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday)
    {
        abort_if(Gate::denies('holiday_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkManageAccess($holiday);

        $holiday->load('targets');
        return view('admin.holidays.edit', $this->formData($holiday) + compact('holiday'));
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        $this->checkManageAccess($holiday);

        $data = $request->validated();
        $data['date'] = $data['start_date'];
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            abort_if(! $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $scope['branch_id'];
        }

        DB::transaction(function () use ($holiday, $data) {
            $targetIds = $this->validatedTargets($data);
            $data['branch_id'] = $this->legacyBranchId($data);
            unset($data['target_ids'], $data['target_branch_id']);
            $holiday->update($data);
            $holiday->targets()->delete();
            foreach ($targetIds as $id) {
                $holiday->targets()->create(['target_type' => $this->targetEntity($data['target_mode']), 'target_id' => $id]);
            }
        });

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

    private function formData(?Holiday $holiday = null): array
    {
        $scope = $this->erpScope();

        $branches = Branch::where('status', 'active')
            ->when(! $scope['is_admin'], fn ($q) => $scope['branch_id'] ? $q->where('id', $scope['branch_id']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id');

        $students = Student::with('user')->when(! $scope['is_admin'], fn ($q) => $q->where('branch_id', $scope['branch_id']))->orderBy('id')->get();
        $teachers = Teacher::with('user')->when(! $scope['is_admin'], fn ($q) => $q->where('branch_id', $scope['branch_id']))->orderBy('id')->get();
        $staff = Staff::with('user')->when(! $scope['is_admin'], fn ($q) => $q->where('branch_id', $scope['branch_id']))->orderBy('id')->get();
        $batches = Batch::when(! $scope['is_admin'], fn ($q) => $q->where('branch_id', $scope['branch_id']))->orderBy('name')->get();

        $holidayTargets = $holiday ? $holiday->targets : collect();

        return [
            'branches' => $branches,
            'scope' => $scope,
            'students' => $students,
            'teachers' => $teachers,
            'staff' => $staff,
            'batches' => $batches,
            'holidayTargets' => $holidayTargets,
        ];
    }

    private function targetEntity(string $mode): string
    {
        return match ($mode) {
            'branch_wise' => 'branch', 'batch_wise' => 'batch',
            'selected_students' => 'student',
            'selected_teachers', 'teacher_wise', 'single_branch_teachers' => 'teacher',
            'selected_staff', 'single_branch_staff' => 'staff',
            default => 'none',
        };
    }

    private function legacyBranchId(array $data): ?int
    {
        $scope = $this->erpScope();
        if (! $scope['is_admin']) return $scope['branch_id'];
        return in_array($data['target_mode'], ['single_branch_teachers', 'single_branch_staff']) ? (int) $data['target_branch_id'] : null;
    }

    private function validatedTargets(array $data): array
    {
        $scope = $this->erpScope();
        $mode = $data['target_mode'];
        $requiredAudience = match ($mode) {
            'batch_wise', 'selected_students' => 'students',
            'selected_teachers', 'all_branch_teachers', 'single_branch_teachers', 'teacher_wise' => 'teachers',
            'all_branch_staff', 'single_branch_staff', 'selected_staff' => 'staff',
            default => null,
        };
        abort_if($requiredAudience && ! in_array($requiredAudience, $data['audiences'] ?? [], true), 422, 'The selected target mode must be included in Holiday For.');
        $ids = array_values(array_unique(array_map('intval', $data['target_ids'] ?? [])));
        $needsIds = in_array($mode, ['branch_wise', 'batch_wise', 'selected_students', 'selected_teachers', 'single_branch_teachers', 'teacher_wise', 'single_branch_staff', 'selected_staff']);
        abort_if($needsIds && ! count($ids), 422, 'Select at least one target.');
        abort_if(! $scope['is_admin'] && in_array($mode, ['all_branches', 'all_branch_teachers', 'all_branch_staff']), 403, 'Only administrators may create global holidays.');

        if (in_array($mode, ['single_branch_teachers', 'single_branch_staff'])) {
            abort_if(! $data['target_branch_id'] || (! $scope['is_admin'] && (int) $data['target_branch_id'] !== (int) $scope['branch_id']), 403, 'Branch access denied.');
        }
        abort_if($mode === 'batch_wise' && empty($data['target_branch_id']), 422, 'Choose a branch before selecting batches.');

        $entity = $this->targetEntity($mode);
        if ($entity === 'none' || ! $ids) return [];
        $model = ['branch' => Branch::class, 'batch' => Batch::class, 'student' => Student::class, 'teacher' => Teacher::class, 'staff' => Staff::class][$entity];
        $query = $model::query()->whereIn('id', $ids);
        if ($entity !== 'branch' && ! $scope['is_admin']) $query->where('branch_id', $scope['branch_id']);
        if ($entity === 'branch' && ! $scope['is_admin']) $query->where('id', $scope['branch_id']);
        if (in_array($mode, ['single_branch_teachers', 'single_branch_staff'])) $query->where('branch_id', $data['target_branch_id']);
        if ($entity === 'batch') {
            $branchId = $data['target_branch_id'] ?? null;
            if ($branchId) $query->where('branch_id', $branchId);
        }
        abort_if($query->count() !== count($ids), 422, 'One or more selected targets are invalid for your branch.');
        return $ids;
    }
}
