<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceRequestsController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('maintenance_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $maintenanceRequests = MaintenanceRequest::with(['branch','reportedBy','assignedTo','expense']); $scope = $this->erpScope(); if (! $scope['is_admin']) { $this->scopeBranchQuery($maintenanceRequests); } $maintenanceRequests = $maintenanceRequests->latest()->get(); return view('admin.maintenanceRequests.index', compact('maintenanceRequests')); }
    public function create() { abort_if(Gate::denies('maintenance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); return view('admin.maintenanceRequests.create', $this->formData()); }
    public function store(Request $request) { abort_if(Gate::denies('maintenance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $data = $this->validated($request); $scope = $this->erpScope(); if (! $scope['is_admin']) { $data['branch_id'] = $scope['branch_id']; } MaintenanceRequest::create($data+['reported_by_id'=>auth()->id()]); return redirect()->route('admin.maintenance-requests.index')->with('message','Maintenance request saved successfully.'); }
    public function edit(MaintenanceRequest $maintenanceRequest) { abort_if(Gate::denies('maintenance_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($maintenanceRequest); return view('admin.maintenanceRequests.edit', $this->formData($maintenanceRequest)+compact('maintenanceRequest')); }
    public function update(Request $request, MaintenanceRequest $maintenanceRequest) { abort_if(Gate::denies('maintenance_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($maintenanceRequest); $data = $this->validated($request); $scope = $this->erpScope(); if (! $scope['is_admin']) { $data['branch_id'] = $scope['branch_id']; } $maintenanceRequest->update($data); return redirect()->route('admin.maintenance-requests.index')->with('message','Maintenance request updated successfully.'); }

    private function formData(?MaintenanceRequest $maintenanceRequest = null): array
    {
        $usersByBranch = $this->usersByBranch();

        // Never silently drop the request's existing "Assigned To" user just because their
        // role/branch assignment has since changed — otherwise saving without touching this
        // field would lose the original value.
        if ($maintenanceRequest && $maintenanceRequest->assigned_to_id && $maintenanceRequest->branch_id) {
            $bucket = collect($usersByBranch[$maintenanceRequest->branch_id] ?? []);

            if (! $bucket->contains('id', $maintenanceRequest->assigned_to_id) && $maintenanceRequest->assignedTo) {
                $usersByBranch[$maintenanceRequest->branch_id] = $bucket
                    ->push(['id' => $maintenanceRequest->assignedTo->id, 'name' => $maintenanceRequest->assignedTo->name])
                    ->values()
                    ->toArray();
            }
        }

        return [
            'branches' => $this->scopeBranchQuery(Branch::query(), 'id')->pluck('name', 'id')->prepend('Optional', ''),
            'usersByBranch' => $usersByBranch,
            'expenses' => Expense::pluck('title', 'id')->prepend('Optional', ''),
        ];
    }
    private function validated(Request $request): array { return $request->validate(['branch_id'=>['nullable','exists:branches,id'],'assigned_to_id'=>['nullable','exists:users,id'],'expense_id'=>['nullable','exists:expenses,id'],'title'=>['required','string','max:255'],'category'=>['nullable','string','max:255'],'priority'=>['required','in:low,medium,high,urgent'],'status'=>['required','in:open,assigned,in_progress,resolved,closed'],'description'=>['nullable','string'],'repair_notes'=>['nullable','string'],'reported_date'=>['nullable','date'],'resolved_date'=>['nullable','date']]); }
}
