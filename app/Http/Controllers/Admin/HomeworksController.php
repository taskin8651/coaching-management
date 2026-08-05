<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class HomeworksController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('homework_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $homeworks = Homework::with(['batch','subject','teacher.user']); $scope = $this->erpScope(); if ($scope['is_student'] && $scope['student_id']) { $homeworks->whereHas('submissions', fn ($q) => $q->where('student_id', $scope['student_id'])); } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) { $homeworks->whereHas('submissions', fn ($q) => $q->whereIn('student_id', $scope['parent_student_ids'])); } elseif ($scope['is_teacher'] && $scope['teacher_id']) { $homeworks->where('teacher_id', $scope['teacher_id']); } elseif (! $scope['is_admin']) { $this->scopeBranchQuery($homeworks); } $homeworks = $homeworks->latest()->get(); return view('admin.homeworks.index', compact('homeworks')); }
    public function create() { abort_if(Gate::denies('homework_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); return view('admin.homeworks.create', $this->formData()); }
    public function store(Request $request, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('homework_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $this->validated($request);
        $scope = $this->erpScope();
        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
            if ($scope['is_teacher'] && $scope['teacher_id']) {
                $data['teacher_id'] = $scope['teacher_id'];
            }
        }

        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        if (! empty($data['subject_id'])) {
            abort_if(! $this->scopeBranchQuery(Subject::query())->where('id', $data['subject_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }

        $homework = Homework::create($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $homework->addMedia($file)->toMediaCollection('homework_attachments');
            }
        }

        $students = Student::where(function ($query) use ($homework) {
            $query->where('batch_id', $homework->batch_id)
                ->orWhereHas('studentBatches', fn ($q) => $q->where('batch_id', $homework->batch_id)->where('status', 'active'));
        })
            ->when($homework->branch_id, fn ($query) => $query->where('branch_id', $homework->branch_id))
            ->get();

        foreach ($students as $student) {
            HomeworkSubmission::firstOrCreate(
                ['unique_key' => HomeworkSubmission::makeUniqueKey($homework->id, $student->id)],
                ['homework_id' => $homework->id, 'student_id' => $student->id]
            );
            $whatsapp->sendStudentGuardianMessage($student, 'homework', 'Homework assigned: '.$homework->title.' due on '.optional($homework->due_date)->format('d M Y'));
        }
        return redirect()->route('admin.homeworks.index')->with('message', 'Homework assigned successfully.');
    }
    public function show(Homework $homework)
    {
        abort_if(Gate::denies('homework_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertBranchAccess($homework);
        $homework->load(['submissions.student.user', 'batch', 'subject']);

        $scope = $this->erpScope();

        if ($scope['is_student']) {
            // Student ko sirf apni hi submission dikhe, poori batch ki nahi.
            $homework->setRelation(
                'submissions',
                $scope['student_id'] ? $homework->submissions->where('student_id', $scope['student_id'])->values() : $homework->submissions->take(0)
            );
        } elseif ($scope['is_parent']) {
            // Parent ko sirf apne bacchon ki submissions dikhein.
            $homework->setRelation('submissions', $homework->submissions->whereIn('student_id', $scope['parent_student_ids'])->values());
        }

        return view('admin.homeworks.show', compact('homework'));
    }
    public function edit(Homework $homework)
    {
        abort_if(Gate::denies('homework_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertHomeworkOwnership($homework);
        $homework->load(['batch', 'subject', 'teacher.user']);
        return view('admin.homeworks.edit', ['homework' => $homework] + $this->formData());
    }

    public function update(Request $request, Homework $homework)
    {
        abort_if(Gate::denies('homework_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertHomeworkOwnership($homework);

        $data = $this->validated($request);
        $scope = $this->erpScope();
        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
            if ($scope['is_teacher'] && $scope['teacher_id']) {
                $data['teacher_id'] = $scope['teacher_id'];
            }
        }

        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        if (! empty($data['subject_id'])) {
            abort_if(! $this->scopeBranchQuery(Subject::query())->where('id', $data['subject_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }

        $homework->update($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $homework->addMedia($file)->toMediaCollection('homework_attachments');
            }
        }

        return redirect()->route('admin.homeworks.index')->with('message', 'Homework updated successfully.');
    }

    public function destroy(Homework $homework)
    {
        abort_if(Gate::denies('homework_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertHomeworkOwnership($homework);
        $homework->delete();
        return back()->with('message', 'Homework deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('homework_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Homework::whereIn('id', $request->input('ids', []));
        $scope = $this->erpScope();
        if (! $scope['is_admin']) {
            $this->scopeBranchQuery($query);
            if ($scope['is_teacher'] && $scope['teacher_id']) {
                $query->where('teacher_id', $scope['teacher_id']);
            }
        }
        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('homework_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $homework = $media->model;
        if ($homework instanceof Homework) {
            $this->assertHomeworkOwnership($homework);
        }

        $media->delete();

        return back()->with('message', 'Attachment deleted successfully.');
    }

    private function assertHomeworkOwnership(Homework $homework): void
    {
        $this->assertBranchAccess($homework);

        $scope = $this->erpScope();
        if ($scope['is_teacher'] && ! $scope['is_admin'] && ! $scope['is_manager']) {
            abort_if($homework->teacher_id != $scope['teacher_id'], Response::HTTP_FORBIDDEN, '403 Forbidden');
        }
    }

    private function formData(): array { return ['batches'=>$this->scopeBatchQuery(Batch::query())->pluck('name','id')->prepend(trans('global.pleaseSelect'),''),'subjects'=>$this->scopeBranchQuery(Subject::query())->pluck('name','id')->prepend('Optional',''),'teachers'=>$this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn($t)=>[$t->id=>$t->user->name ?? 'Teacher #'.$t->id])->prepend('Optional',''),'subjectsByBatch'=>$this->subjectsByBatch()]; }
    private function validated(Request $request): array { return $request->validate(['branch_id'=>['nullable','exists:branches,id'],'batch_id'=>['required','exists:batches,id'],'subject_id'=>['nullable','exists:subjects,id'],'teacher_id'=>['nullable','exists:teachers,id'],'title'=>['required','string','max:255'],'details'=>['nullable','string'],'homework_date'=>['nullable','date'],'due_date'=>['nullable','date'],'status'=>['required','in:active,closed'],'attachments.*'=>['nullable','file','mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,zip','max:10240']]); }
}
