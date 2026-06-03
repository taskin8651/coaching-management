<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InventoryItemsController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('inventory_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $items = InventoryItem::with('branch'); $scope = $this->erpScope(); if (! $scope['is_admin']) { $this->scopeBranchQuery($items); } $items = $items->latest()->get(); return view('admin.inventoryItems.index', compact('items')); }
    public function create() { abort_if(Gate::denies('inventory_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); return view('admin.inventoryItems.create', ['branches'=>$this->scopeBranchQuery(Branch::query(), 'id')->pluck('name','id')->prepend('Optional','')]); }
    public function store(Request $request) { abort_if(Gate::denies('inventory_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $data=$this->validated($request); $scope = $this->erpScope(); if (! $scope['is_admin']) { $data['branch_id'] = $scope['branch_id']; } $data['current_stock']=$data['opening_stock'] ?? 0; InventoryItem::create($data); return redirect()->route('admin.inventory-items.index')->with('message','Inventory item saved successfully.'); }
    public function edit(InventoryItem $inventoryItem) { abort_if(Gate::denies('inventory_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($inventoryItem); return view('admin.inventoryItems.edit', ['inventoryItem'=>$inventoryItem,'branches'=>$this->scopeBranchQuery(Branch::query(), 'id')->pluck('name','id')->prepend('Optional','')]); }
    public function update(Request $request, InventoryItem $inventoryItem) { abort_if(Gate::denies('inventory_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($inventoryItem); $data = $this->validated($request); $scope = $this->erpScope(); if (! $scope['is_admin']) { $data['branch_id'] = $scope['branch_id']; } $inventoryItem->update($data); return redirect()->route('admin.inventory-items.index')->with('message','Inventory item updated successfully.'); }
    public function transaction(Request $request, InventoryItem $inventoryItem) { abort_if(Gate::denies('inventory_transaction_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($inventoryItem); $data=$request->validate(['transaction_type'=>['required','in:stock_in,stock_out,adjustment'],'quantity'=>['required','integer','min:1'],'transaction_date'=>['nullable','date'],'reference'=>['nullable','string'],'remarks'=>['nullable','string']]); DB::transaction(function() use ($inventoryItem,$data){ InventoryTransaction::create($data+['inventory_item_id'=>$inventoryItem->id,'branch_id'=>$inventoryItem->branch_id,'created_by_id'=>auth()->id()]); $qty=$data['quantity']; $inventoryItem->current_stock = $data['transaction_type']==='stock_out' ? max($inventoryItem->current_stock-$qty,0) : ($data['transaction_type']==='stock_in' ? $inventoryItem->current_stock+$qty : $qty); $inventoryItem->save(); }); return back()->with('message','Stock transaction saved successfully.'); }
    private function validated(Request $request): array { return $request->validate(['branch_id'=>['nullable','exists:branches,id'],'name'=>['required','string','max:255'],'category'=>['nullable','string','max:255'],'unit'=>['nullable','string','max:50'],'opening_stock'=>['nullable','integer','min:0'],'low_stock_level'=>['nullable','integer','min:0'],'unit_cost'=>['nullable','numeric','min:0'],'status'=>['required','in:active,inactive']]); }
}
