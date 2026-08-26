<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExternalContactRequest;
use App\Http\Requests\UpdateExternalContactRequest;
use App\Models\ExternalContact;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExternalContactsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('external_contact_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $externalContacts = ExternalContact::withCount('enrollments')->latest()->get();

        return view('admin.externalContacts.index', compact('externalContacts'));
    }

    public function create()
    {
        abort_if(Gate::denies('external_contact_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.externalContacts.create');
    }

    public function store(StoreExternalContactRequest $request)
    {
        $data = $request->validated();
        $data['created_by_id'] = auth()->id();

        $externalContact = ExternalContact::create($data);

        return redirect()->route('admin.external-contacts.show', $externalContact)->with('message', 'External contact created successfully.');
    }

    public function show(ExternalContact $externalContact)
    {
        abort_if(Gate::denies('external_contact_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $externalContact->load(['enrollments.event', 'enrollments.feePayments', 'createdBy']);

        return view('admin.externalContacts.show', compact('externalContact'));
    }

    public function edit(ExternalContact $externalContact)
    {
        abort_if(Gate::denies('external_contact_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.externalContacts.edit', compact('externalContact'));
    }

    public function update(UpdateExternalContactRequest $request, ExternalContact $externalContact)
    {
        $externalContact->update($request->validated());

        return redirect()->route('admin.external-contacts.show', $externalContact)->with('message', 'External contact updated successfully.');
    }

    public function destroy(ExternalContact $externalContact)
    {
        abort_if(Gate::denies('external_contact_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(
            $externalContact->enrollments()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This contact has event enrollment history and cannot be deleted.'
        );

        $externalContact->delete();

        return back()->with('message', 'External contact deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('external_contact_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ExternalContact::whereIn('id', request('ids'))->whereDoesntHave('enrollments')->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Small JSON lookup used by the event enrollment form's "find an existing contact before
     * creating a new one" step — so the same external participant doesn't get duplicated across
     * events.
     */
    public function search(Request $request)
    {
        abort_if(Gate::denies('external_contact_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $contacts = ExternalContact::where('mobile', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'name', 'mobile', 'school_name']);

        return response()->json($contacts);
    }
}
