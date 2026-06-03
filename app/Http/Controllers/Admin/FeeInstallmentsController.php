<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\FeeInstallment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeeInstallmentsController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('fee_installment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $installments=FeeInstallment::with(['student.user','feeStructure']); $scope = $this->erpScope(); if ($scope['is_student'] && $scope['student_id']) { $installments->where('student_id', $scope['student_id']); } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) { $installments->whereIn('student_id', $scope['parent_student_ids']); } elseif (! $scope['is_admin']) { $installments->whereHas('student', fn ($q) => $this->scopeStudentQuery($q)); } $installments=$installments->latest()->get(); return view('admin.feeInstallments.index', compact('installments')); }
    public function create() { abort_if(Gate::denies('fee_installment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); return view('admin.feeInstallments.create', $this->formData()); }
    public function store(Request $request) { abort_if(Gate::denies('fee_installment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $data=$this->prepare($this->validated($request)); FeeInstallment::create($data); return redirect()->route('admin.fee-installments.index')->with('message','Fee installment saved successfully.'); }
    public function remind(FeeInstallment $feeInstallment, WhatsappService $whatsapp) { abort_if(Gate::denies('fee_installment_remind'), Response::HTTP_FORBIDDEN, '403 Forbidden'); abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $feeInstallment->student_id)->exists(), Response::HTTP_FORBIDDEN, '403 Forbidden'); $whatsapp->sendStudentGuardianMessage($feeInstallment->student,'fee_due','Fee installment due: '.$feeInstallment->title.' amount '.$feeInstallment->due_amount.' due on '.optional($feeInstallment->due_date)->format('d M Y')); $feeInstallment->update(['reminded_at'=>now()]); return back()->with('message','Fee reminder sent/logged.'); }
    private function formData(): array { return ['students'=>$this->scopeStudentQuery(Student::with('user'))->get()->mapWithKeys(fn($s)=>[$s->id=>$s->user->name ?? $s->student_code ?? 'Student #'.$s->id])->prepend(trans('global.pleaseSelect'),''),'feeStructures'=>FeeStructure::pluck('title','id')->prepend('Optional','')]; }
    private function validated(Request $request): array { return $request->validate(['student_id'=>['required','exists:students,id'],'fee_structure_id'=>['nullable','exists:fee_structures,id'],'title'=>['required','string','max:255'],'amount'=>['required','numeric','min:0'],'paid_amount'=>['nullable','numeric','min:0'],'due_date'=>['nullable','date'],'status'=>['required','in:pending,partial,paid,overdue']]); }
    private function prepare(array $data): array { $data['paid_amount']=(float)($data['paid_amount']??0); $data['due_amount']=max(((float)$data['amount'])-$data['paid_amount'],0); if($data['due_amount']<=0){$data['status']='paid';} elseif($data['paid_amount']>0){$data['status']='partial';} return $data; }
}
