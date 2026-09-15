<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AcademicYearsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('fee_master_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $academicYears = AcademicYear::latest()->get();

        return view('admin.academicYears.index', compact('academicYears'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_master_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.academicYears.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('fee_master_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $data['is_current'] = $request->boolean('is_current');

        $academicYear = AcademicYear::create($data);
        $this->syncCurrentYear($academicYear);

        return redirect()->route('admin.academic-years.index')->with('message', 'Academic year created successfully.');
    }

    public function edit(AcademicYear $academicYear)
    {
        abort_if(Gate::denies('fee_master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.academicYears.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        abort_if(Gate::denies('fee_master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request, $academicYear);
        $data['is_current'] = $request->boolean('is_current');

        $academicYear->update($data);
        $this->syncCurrentYear($academicYear);

        return redirect()->route('admin.academic-years.index')->with('message', 'Academic year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        abort_if(Gate::denies('fee_master_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(
            FeeStructure::where('academic_year', $academicYear->name)->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This academic year is used in fee structures and cannot be deleted. Mark it inactive instead.'
        );

        $academicYear->delete();

        return back()->with('message', 'Academic year deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_master_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        AcademicYear::whereIn('id', request('ids'))
            ->whereNotIn('name', FeeStructure::whereNotNull('academic_year')->pluck('academic_year')->unique())
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validated(Request $request, ?AcademicYear $academicYear = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_years', 'name')->ignore($academicYear?->id),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function syncCurrentYear(AcademicYear $academicYear): void
    {
        if (! $academicYear->is_current) {
            return;
        }

        AcademicYear::where('id', '!=', $academicYear->id)->update(['is_current' => false]);
    }
}
