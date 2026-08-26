<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeStructureRequest;
use App\Http\Requests\UpdateFeeStructureRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\FeeAccount;
use App\Models\FeeHead;
use App\Models\FeeInstallment;
use App\Models\FeeInstallmentItem;
use App\Models\FeeStructure;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use App\Models\Teacher;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeeStructuresController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('fee_structure_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feeStructures = FeeStructure::with(['branch', 'course', 'batch']);

        if (auth()->user()->is_admin) {
            // Admin all
        } else {
            $branchId = $this->getUserBranchId();

            $branchId
                ? $feeStructures->where('branch_id', $branchId)
                : $feeStructures->whereRaw('1 = 0');
        }

        $feeStructures = $feeStructures->latest()->get();

        return view('admin.feeStructures.index', compact('feeStructures'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_structure_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeStructures.create', $this->formData());
    }

    public function store(StoreFeeStructureRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        $this->assertItemGstBelongsToFeeHead($data['items']);

        $feeStructure = DB::transaction(function () use ($data) {
            $feeStructure = FeeStructure::create($this->structureAttributes($data) + [
                'version_no' => 1,
                'root_fee_structure_id' => null,
            ]);

            $this->syncItemsAndInstallments($feeStructure, $data);

            $this->checkInstallmentAllocation($feeStructure, $data);

            return $feeStructure;
        });

        return redirect()->route('admin.fee-structures.show', $feeStructure)->with('message', 'Fee structure created successfully.');
    }

    public function show(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $feeStructure->load(['branch', 'course', 'batch', 'items.feeHead', 'installmentTemplates.feeAccount']);

        $versions = $feeStructure->versions()->get();

        $unassignedStudents = Student::with('user')
            ->where('branch_id', $feeStructure->branch_id)
            ->when($feeStructure->course_id, fn ($q) => $q->where('course_id', $feeStructure->course_id))
            ->when($feeStructure->batch_id, fn ($q) => $q->where('batch_id', $feeStructure->batch_id))
            ->whereDoesntHave('feeLedgers', fn ($q) => $q->where('fee_structure_id', $feeStructure->id))
            ->get();

        return view('admin.feeStructures.show', compact('feeStructure', 'versions', 'unassignedStudents'));
    }

    public function edit(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $feeStructure->load(['branch', 'course', 'batch', 'items.feeHead', 'installmentTemplates.feeAccount']);

        $hasLedgers = $feeStructure->ledgers()->exists();

        return view('admin.feeStructures.edit', $this->formData() + compact('feeStructure', 'hasLedgers'));
    }

    public function update(UpdateFeeStructureRequest $request, FeeStructure $feeStructure)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        $this->assertItemGstBelongsToFeeHead($data['items']);

        $hasLedgers = $feeStructure->ledgers()->exists();

        if ($hasLedgers) {
            $newVersion = DB::transaction(function () use ($feeStructure, $data) {
                $newVersion = FeeStructure::create($this->structureAttributes($data) + [
                    'version_no' => $feeStructure->version_no + 1,
                    'root_fee_structure_id' => $feeStructure->root_fee_structure_id ?? $feeStructure->id,
                ]);

                $this->syncItemsAndInstallments($newVersion, $data);

                $this->checkInstallmentAllocation($newVersion, $data);

                $feeStructure->update([
                    'status' => 'inactive',
                    'effective_to' => $feeStructure->effective_to ?? now()->format('Y-m-d'),
                ]);

                return $newVersion;
            });

            return redirect()->route('admin.fee-structures.show', $newVersion)
                ->with('message', 'Students are already assigned to this fee structure, so a new version (v' . $newVersion->version_no . ') was created instead of editing it directly. The previous version has been marked inactive; existing student ledgers are unaffected.');
        }

        DB::transaction(function () use ($feeStructure, $data) {
            $feeStructure->update($this->structureAttributes($data));

            $this->syncItemsAndInstallments($feeStructure, $data);

            $this->checkInstallmentAllocation($feeStructure, $data);
        });

        return redirect()->route('admin.fee-structures.show', $feeStructure)->with('message', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        abort_if(
            $feeStructure->ledgers()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Students are assigned to this fee structure and it cannot be deleted. Mark it inactive instead.'
        );

        $feeStructure->delete();

        return back()->with('message', 'Fee structure deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_structure_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FeeStructure::whereIn('id', request('ids'))->whereDoesntHave('ledgers');

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Assigns this fee structure to one or more students: creates a StudentFeeLedger and, for
     * each installment template (in sequence), a FeeInstallment + its per-fee-head
     * FeeInstallmentItem breakdown, proportionally allocated from the structure's line items.
     */
    public function assignToStudents(FeeStructure $feeStructure, Request $request)
    {
        abort_if(Gate::denies('student_fee_ledger_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $studentIds = collect($request->input('student_ids', []))->filter()->unique()->values();

        abort_if($studentIds->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Select at least one student.');

        $feeStructure->load(['items', 'installmentTemplates.feeAccount']);

        $students = $this->scopeStudentQuery(Student::query())
            ->whereIn('id', $studentIds)
            ->where('branch_id', $feeStructure->branch_id)
            ->get();

        $assigned = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $alreadyAssigned = StudentFeeLedger::where('student_id', $student->id)
                ->where('fee_structure_id', $feeStructure->id)
                ->exists();

            if ($alreadyAssigned) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($feeStructure, $student) {
                $ledger = StudentFeeLedger::create([
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'fee_structure_version' => $feeStructure->version_no,
                    'net_payable' => $feeStructure->total_fee,
                    'assigned_by_id' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => 'active',
                ]);

                $this->generateInstallmentsForLedger($feeStructure, $ledger, $student);

                $ledger->recalculate();
            });

            $assigned++;
        }

        return back()->with('message', "Assigned to {$assigned} student(s)." . ($skipped ? " Skipped {$skipped} already assigned." : ''));
    }

    private function generateInstallmentsForLedger(FeeStructure $feeStructure, StudentFeeLedger $ledger, Student $student): void
    {
        $templates = $feeStructure->installmentTemplates;
        $items = $feeStructure->items;
        $structureTotal = (float) $feeStructure->total_fee;

        if ($templates->isEmpty()) {
            // No installment plan defined — the whole structure becomes a single installment.
            $installment = FeeInstallment::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_fee_ledger_id' => $ledger->id,
                'fee_structure_installment_id' => null,
                'fee_account_id' => null,
                'title' => $feeStructure->title ?? 'Fee',
                'amount' => $structureTotal,
                'paid_amount' => 0,
                'due_amount' => $structureTotal,
                'due_date' => null,
                'status' => 'pending',
            ]);

            $this->createInstallmentItems($installment, $items, $structureTotal, $structureTotal);

            return;
        }

        $resolvedAmounts = $templates->map(fn ($template) => $template->resolvedAmount($structureTotal));
        $allocatedSoFar = 0;

        foreach ($templates as $index => $template) {
            $isLast = $index === $templates->count() - 1;
            $amount = $isLast
                ? round($structureTotal - $allocatedSoFar, 2)
                : round($resolvedAmounts[$index], 2);

            $allocatedSoFar += $amount;

            $installment = FeeInstallment::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_fee_ledger_id' => $ledger->id,
                'fee_structure_installment_id' => $template->id,
                'fee_account_id' => $template->fee_account_id,
                'title' => $template->title,
                'amount' => max($amount, 0),
                'paid_amount' => 0,
                'due_amount' => max($amount, 0),
                'due_date' => $template->due_date,
                'status' => 'pending',
                'late_fee_enabled' => $template->late_fee_enabled,
                'late_fee_type' => $template->late_fee_type,
                'late_fee_amount' => $template->late_fee_amount,
                'late_fee_percentage' => $template->late_fee_percentage,
                'late_fee_grace_days' => $template->late_fee_grace_days,
                'late_fee_max_amount' => $template->late_fee_max_amount,
            ]);

            $this->createInstallmentItems($installment, $items, $amount, $structureTotal);
        }
    }

    /**
     * Allocates each structure line item proportionally onto one installment. The last
     * installment absorbs the rounding remainder so item sums always reconcile exactly to the
     * structure's item totals — same "resum not delta" philosophy as recalculateFromPayments().
     */
    private function createInstallmentItems(FeeInstallment $installment, $items, float $installmentAmount, float $structureTotal): void
    {
        if ($structureTotal <= 0) {
            return;
        }

        $ratio = $installmentAmount / $structureTotal;

        foreach ($items as $item) {
            $amount = round(((float) $item->line_total) * $ratio, 2);

            FeeInstallmentItem::create([
                'fee_installment_id' => $installment->id,
                'fee_head_id' => $item->fee_head_id,
                'amount' => round(((float) $item->amount) * $ratio, 2),
                'gst_percent' => $item->gst_percent,
                'gst_amount' => round(((float) $item->gst_amount) * $ratio, 2),
                'line_total' => $amount,
            ]);
        }
    }

    private function structureAttributes(array $data): array
    {
        return [
            'branch_id' => $data['branch_id'],
            'course_id' => $data['course_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'title' => $data['title'],
            'academic_year' => $data['academic_year'],
            'board' => $data['board'] ?? null,
            'standard' => $data['standard'] ?? null,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'installment_allocation_override' => (bool) ($data['installment_allocation_override'] ?? false),
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'total_fee' => 0,
        ];
    }

    private function syncItemsAndInstallments(FeeStructure $feeStructure, array $data): void
    {
        $feeStructure->items()->delete();
        $feeStructure->installmentTemplates()->delete();

        foreach (array_values($data['items']) as $index => $item) {
            $amount = (float) $item['amount'];
            $gstApplicable = (bool) ($item['gst_applicable'] ?? false);
            $gstPercent = $gstApplicable ? (float) ($item['gst_percent'] ?? 0) : 0;
            $gstAmount = $gstApplicable ? round($amount * $gstPercent / 100, 2) : 0;

            $feeStructure->items()->create([
                'fee_head_id' => $item['fee_head_id'],
                'amount' => $amount,
                'gst_applicable' => $gstApplicable,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'line_total' => round($amount + $gstAmount, 2),
                'sort_order' => $index,
            ]);
        }

        foreach (array_values($data['installments'] ?? []) as $index => $installment) {
            $lateFeeEnabled = (bool) ($installment['late_fee_enabled'] ?? false);

            $feeStructure->installmentTemplates()->create([
                'fee_account_id' => $installment['fee_account_id'],
                'title' => $installment['title'],
                'sequence' => $index,
                'amount_type' => $installment['amount_type'],
                'amount' => $installment['amount_type'] === 'fixed' ? ($installment['amount'] ?? 0) : null,
                'percentage' => $installment['amount_type'] === 'percentage' ? ($installment['percentage'] ?? 0) : null,
                'due_date' => $installment['due_date'] ?? null,
                'late_fee_enabled' => $lateFeeEnabled,
                'late_fee_type' => $lateFeeEnabled ? ($installment['late_fee_type'] ?? null) : null,
                'late_fee_amount' => $lateFeeEnabled ? ($installment['late_fee_amount'] ?? null) : null,
                'late_fee_percentage' => $lateFeeEnabled ? ($installment['late_fee_percentage'] ?? null) : null,
                'late_fee_grace_days' => $lateFeeEnabled ? ($installment['late_fee_grace_days'] ?? 0) : 0,
                'late_fee_max_amount' => $lateFeeEnabled ? ($installment['late_fee_max_amount'] ?? null) : null,
            ]);
        }

        $feeStructure->recalculateTotal();
    }

    private function checkInstallmentAllocation(FeeStructure $feeStructure, array $data): void
    {
        if (empty($data['installments']) || ($data['installment_allocation_override'] ?? false)) {
            return;
        }

        $feeStructure->refresh()->load('installmentTemplates');

        $total = (float) $feeStructure->total_fee;
        $allocated = $feeStructure->installmentTemplates->sum(fn ($installment) => $installment->resolvedAmount($total));

        abort_if(
            abs($allocated - $total) > 1,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            "Installment allocation (₹{$allocated}) does not match the fee structure total (₹{$total}). Adjust the installments or check \"Allow installment total mismatch\"."
        );
    }

    private function assertItemGstBelongsToFeeHead(array $items): void
    {
        $feeHeadIds = collect($items)->pluck('fee_head_id')->unique();

        abort_if(
            FeeHead::whereIn('id', $feeHeadIds)->count() !== $feeHeadIds->count(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'One or more selected fee heads are invalid.'
        );
    }

    private function formData(): array
    {
        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend('All Batches / Optional', '');

        $feeHeads = FeeHead::where('status', 'active')->orderBy('name')->get(['id', 'name', 'gst_applicable', 'default_gst_percent']);

        $feeAccounts = FeeAccount::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where(fn ($q) => $q->where('branch_id', $branchId)->orWhereNull('branch_id')) : $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return [
            'branches' => $branches,
            'courses' => $courses,
            'batches' => $batches,
            'feeHeads' => $feeHeads,
            'feeAccounts' => $feeAccounts,
            'coursesByBranch' => $this->coursesByBranch(),
            'batchesByBranchCourse' => $this->batchesByBranchCourse(),
        ];
    }

    private function checkAccess(FeeStructure $feeStructure): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $feeStructure->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchData(array $data, $branchId): void
    {
        if (! empty($data['course_id'])) {
            abort_if(
                ! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid course for your branch.'
            );
        }

        if (! empty($data['batch_id'])) {
            abort_if(
                ! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid batch for your branch.'
            );
        }
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}
