<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryFollowUpRequest;
use App\Http\Requests\StoreEnquiryRequest;
use App\Http\Requests\UpdateEnquiryRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnquiriesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('enquiry_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $enquiries = Enquiry::with(['branch', 'course', 'assignedTo'])
            ->latest()
            ->get();

        return view('admin.enquiries.index', compact('enquiries'));
    }

    public function create()
    {
        abort_if(Gate::denies('enquiry_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $sources = [
            'Walk-in' => 'Walk-in',
            'Phone Call' => 'Phone Call',
            'Website' => 'Website',
            'Facebook' => 'Facebook',
            'Instagram' => 'Instagram',
            'Google' => 'Google',
            'Reference' => 'Reference',
            'WhatsApp' => 'WhatsApp',
            'Other' => 'Other',
        ];

        return view('admin.enquiries.create', compact('branches', 'courses', 'users', 'sources'));
    }

    public function store(StoreEnquiryRequest $request)
    {
        Enquiry::create($request->validated());

        return redirect()->route('admin.enquiries.index')->with('message', 'Enquiry created successfully.');
    }

    public function show(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $enquiry->load(['branch', 'course', 'assignedTo', 'followUps.followedBy']);

        $followUpTypes = [
            'Call' => 'Call',
            'WhatsApp' => 'WhatsApp',
            'SMS' => 'SMS',
            'Email' => 'Email',
            'Visit' => 'Visit',
            'Demo Class' => 'Demo Class',
            'Other' => 'Other',
        ];

        return view('admin.enquiries.show', compact('enquiry', 'followUpTypes'));
    }

    public function edit(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $sources = [
            'Walk-in' => 'Walk-in',
            'Phone Call' => 'Phone Call',
            'Website' => 'Website',
            'Facebook' => 'Facebook',
            'Instagram' => 'Instagram',
            'Google' => 'Google',
            'Reference' => 'Reference',
            'WhatsApp' => 'WhatsApp',
            'Other' => 'Other',
        ];

        $enquiry->load(['branch', 'course', 'assignedTo']);

        return view('admin.enquiries.edit', compact('enquiry', 'branches', 'courses', 'users', 'sources'));
    }

    public function update(UpdateEnquiryRequest $request, Enquiry $enquiry)
    {
        $enquiry->update($request->validated());

        return redirect()->route('admin.enquiries.index')->with('message', 'Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $enquiry->delete();

        return back()->with('message', 'Enquiry deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('enquiry_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Enquiry::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeFollowUp(StoreEnquiryFollowUpRequest $request, Enquiry $enquiry)
    {
        $followUp = $enquiry->followUps()->create([
            'followed_by_id'       => auth()->id(),
            'follow_up_date'       => $request->follow_up_date,
            'follow_up_type'       => $request->follow_up_type,
            'response'             => $request->response,
            'next_follow_up_date'  => $request->next_follow_up_date,
            'status'               => $request->status,
            'remarks'              => $request->remarks,
        ]);

        $enquiry->update([
            'status' => $request->status,
            'next_follow_up_date' => $request->next_follow_up_date,
        ]);

        return back()->with('message', 'Follow-up added successfully.');
    }
}