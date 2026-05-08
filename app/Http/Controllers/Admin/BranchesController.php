<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('branch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::with(['manager'])
            ->when(! auth()->user()->is_admin, function ($query) {
                $query->where('manager_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /*
         * Branch create sirf Admin ko allow karna best hai.
         * Manager ko branch create nahi dena chahiye.
         */
        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $managers = User::whereHas('roles', function ($query) {
            $query->where('title', 'Branch Manager');
        })->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.branches.create', compact('managers'));
    }

    public function store(StoreBranchRequest $request)
    {
        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branch = Branch::create($request->validated());

        if ($request->hasFile('logo')) {
            $branch->addMediaFromRequest('logo')->toMediaCollection('branch_logo');
        }

        return redirect()->route('admin.branches.index')->with('message', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        abort_if(Gate::denies('branch_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBranchAccess($branch);

        $branch->load('manager');

        return view('admin.branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        abort_if(Gate::denies('branch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /*
         * Branch edit bhi sirf Admin ko dena recommended hai.
         * Agar manager ko apni branch edit karwana hai, to neeche wali admin check hata sakte ho.
         */
        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBranchAccess($branch);

        $managers = User::whereHas('roles', function ($query) {
            $query->where('title', 'Branch Manager');
        })->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $branch->load('manager');

        return view('admin.branches.edit', compact('branch', 'managers'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBranchAccess($branch);

        $branch->update($request->validated());

        if ($request->hasFile('logo')) {
            $branch->clearMediaCollection('branch_logo');
            $branch->addMediaFromRequest('logo')->toMediaCollection('branch_logo');
        }

        return redirect()->route('admin.branches.index')->with('message', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        abort_if(Gate::denies('branch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /*
         * Branch delete sirf Admin.
         */
        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branch->delete();

        return back()->with('message', 'Branch deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('branch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(! auth()->user()->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        Branch::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function checkBranchAccess(Branch $branch): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        abort_if($branch->manager_id != auth()->id(), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}