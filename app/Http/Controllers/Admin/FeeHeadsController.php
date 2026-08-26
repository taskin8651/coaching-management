<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeHeadRequest;
use App\Http\Requests\UpdateFeeHeadRequest;
use App\Models\FeeHead;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeeHeadsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('fee_master_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feeHeads = FeeHead::latest()->get();

        return view('admin.feeHeads.index', compact('feeHeads'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_master_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeHeads.create');
    }

    public function store(StoreFeeHeadRequest $request)
    {
        FeeHead::create($request->validated());

        return redirect()->route('admin.fee-heads.index')->with('message', 'Fee head created successfully.');
    }

    public function show(FeeHead $feeHead)
    {
        abort_if(Gate::denies('fee_master_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeHeads.show', compact('feeHead'));
    }

    public function edit(FeeHead $feeHead)
    {
        abort_if(Gate::denies('fee_master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeHeads.edit', compact('feeHead'));
    }

    public function update(UpdateFeeHeadRequest $request, FeeHead $feeHead)
    {
        $feeHead->update($request->validated());

        return redirect()->route('admin.fee-heads.index')->with('message', 'Fee head updated successfully.');
    }

    public function destroy(FeeHead $feeHead)
    {
        abort_if(Gate::denies('fee_master_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(
            $feeHead->items()->exists() || $feeHead->installmentItems()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This fee head is used in a fee structure and cannot be deleted. Mark it inactive instead.'
        );

        $feeHead->delete();

        return back()->with('message', 'Fee head deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_master_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        FeeHead::whereIn('id', request('ids'))
            ->whereDoesntHave('items')
            ->whereDoesntHave('installmentItems')
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
