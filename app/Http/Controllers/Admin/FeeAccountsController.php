<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeAccountRequest;
use App\Http\Requests\UpdateFeeAccountRequest;
use App\Models\Branch;
use App\Models\FeeAccount;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeeAccountsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('fee_account_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feeAccounts = FeeAccount::with('branch');
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $scope['branch_id']
                ? $feeAccounts->where(function ($q) use ($scope) {
                    $q->where('branch_id', $scope['branch_id'])->orWhereNull('branch_id');
                })
                : $feeAccounts->whereRaw('1 = 0');
        }

        $feeAccounts = $feeAccounts->latest()->get();

        return view('admin.feeAccounts.index', compact('feeAccounts'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_account_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeAccounts.create', ['branches' => $this->branchOptions()]);
    }

    public function store(StoreFeeAccountRequest $request)
    {
        $data = $this->applyBranchScope($request->validated());

        $feeAccount = FeeAccount::create($data);

        if ($request->hasFile('qr_code')) {
            $feeAccount->addMediaFromRequest('qr_code')->toMediaCollection('fee_account_qr');
        }

        return redirect()->route('admin.fee-accounts.index')->with('message', 'Fee account created successfully.');
    }

    public function show(FeeAccount $feeAccount)
    {
        abort_if(Gate::denies('fee_account_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeAccount);

        $feeAccount->load('branch');

        return view('admin.feeAccounts.show', compact('feeAccount'));
    }

    public function edit(FeeAccount $feeAccount)
    {
        abort_if(Gate::denies('fee_account_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeAccount);

        return view('admin.feeAccounts.edit', ['feeAccount' => $feeAccount, 'branches' => $this->branchOptions()]);
    }

    public function update(UpdateFeeAccountRequest $request, FeeAccount $feeAccount)
    {
        $this->checkAccess($feeAccount);

        $data = $this->applyBranchScope($request->validated());

        $feeAccount->update($data);

        if ($request->hasFile('qr_code')) {
            $feeAccount->clearMediaCollection('fee_account_qr');
            $feeAccount->addMediaFromRequest('qr_code')->toMediaCollection('fee_account_qr');
        }

        return redirect()->route('admin.fee-accounts.index')->with('message', 'Fee account updated successfully.');
    }

    public function destroy(FeeAccount $feeAccount)
    {
        abort_if(Gate::denies('fee_account_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeAccount);

        abort_if(
            $feeAccount->structureInstallments()->exists() || $feeAccount->feeInstallments()->exists() || $feeAccount->feePayments()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This fee account is in use by a fee structure, installment or payment and cannot be deleted. Mark it inactive instead.'
        );

        $feeAccount->delete();

        return back()->with('message', 'Fee account deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_account_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        FeeAccount::whereIn('id', request('ids'))
            ->whereDoesntHave('structureInstallments')
            ->whereDoesntHave('feeInstallments')
            ->whereDoesntHave('feePayments')
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function branchOptions()
    {
        $scope = $this->erpScope();

        return Branch::where('status', 'active')
            ->when(! $scope['is_admin'], function ($query) use ($scope) {
                $scope['branch_id'] ? $query->where('id', $scope['branch_id']) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend('Shared / All Branches', '');
    }

    private function applyBranchScope(array $data): array
    {
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            abort_if(! $scope['branch_id'], Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $scope['branch_id'];
        }

        return $data;
    }

    private function checkAccess(FeeAccount $feeAccount): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            $feeAccount->branch_id && $feeAccount->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
