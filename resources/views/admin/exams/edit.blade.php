@extends('layouts.admin')

@section('page-title', 'Edit Exam')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.exams.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Exam / Test</h2>

        <p class="admin-page-subtitle">
            Update exam details and academic mapping
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.exams.update', $exam->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>

                <div>
                    <p class="form-card-title">Exam Details</p>
                    <p class="form-card-subtitle">Basic exam/test information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Exam Title <span class="req">*</span>
                    </label>

                    <input type="text"
                           name="title"
                           id="title"
                           value="{{ old('title', $exam->title) }}"
                           required
                           class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                </div>

                <div class="field-group">
                    <label class="field-label" for="exam_type">Exam Type</label>

                    <select name="exam_type" id="exam_type" class="field-input">
                        <option value="">Please select</option>
                        @foreach($examTypes as $key => $type)
                            <option value="{{ $key }}" {{ old('exam_type', $exam->exam_type) == $key ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="exam_date">Exam Date</label>

                    <input type="date"
                           name="exam_date"
                           id="exam_date"
                           value="{{ old('exam_date', $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') : '') }}"
                           class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label" for="start_time">Start Time</label>

                    <input type="time"
                           name="start_time"
                           id="start_time"
                           value="{{ old('start_time', $exam->start_time) }}"
                           class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label" for="end_time">End Time</label>

                    <input type="time"
                           name="end_time"
                           id="end_time"
                           value="{{ old('end_time', $exam->end_time) }}"
                           class="field-input">
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Select branch, course, batch and subject</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>
                    <select name="branch_id" id="branch_id" class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id', $exam->branch_id) == $id ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">Batch</label>
                    <select name="batch_id" id="batch_id" class="field-input">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ old('batch_id', $exam->batch_id) == $id ? 'selected' : '' }}>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>
                    <select name="course_id" id="course_id" class="field-input">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ old('course_id', $exam->course_id) == $id ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">Subject</label>
                    <select name="subject_id" id="subject_id" class="field-input">
                        @foreach($subjects as $id => $subject)
                            <option value="{{ $id }}" {{ old('subject_id', $exam->subject_id) == $id ? 'selected' : '' }}>
                                {{ $subject }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-star"></i>
                </div>

                <div>
                    <p class="form-card-title">Marks & Status</p>
                    <p class="form-card-subtitle">Update marks and status</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="total_marks">
                                Total Marks <span class="req">*</span>
                            </label>

                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="total_marks"
                                   id="total_marks"
                                   value="{{ old('total_marks', $exam->total_marks) }}"
                                   required
                                   class="field-input">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="passing_marks">
                                Passing Marks <span class="req">*</span>
                            </label>

                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="passing_marks"
                                   id="passing_marks"
                                   value="{{ old('passing_marks', $exam->passing_marks) }}"
                                   required
                                   class="field-input">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="status">
                                Status <span class="req">*</span>
                            </label>

                            <select name="status" id="status" required class="field-input">
                                <option value="scheduled" {{ old('status', $exam->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ old('status', $exam->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $exam->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="5"
                              class="field-input">{{ old('remarks', $exam->remarks) }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.exams.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>
</form>

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const subjectSelect = document.getElementById('subject_id');
    const batchesByBranch = @json($batchesByBranch);
    const coursesByBatch = @json($coursesByBatch);
    const subjectsByBatch = @json($subjectsByBatch);
    const placeholder = @json(trans('global.pleaseSelect'));

    cascadeByParent(batchSelect, branchSelect, batchesByBranch, {
        placeholder,
        keepValue: @json(old('batch_id', $exam->batch_id)),
    });

    cascadeByParent(courseSelect, batchSelect, coursesByBatch, {
        placeholder,
        keepValue: @json(old('course_id', $exam->course_id)),
    });

    cascadeByParent(subjectSelect, batchSelect, subjectsByBatch, {
        placeholder,
        keepValue: @json(old('subject_id', $exam->subject_id)),
    });
});
</script>
@endsection