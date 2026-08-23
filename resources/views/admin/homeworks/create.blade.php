@extends('layouts.admin')

@section('page-title', 'Add Homework')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.homeworks.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Homework</h2>

        <p class="admin-page-subtitle">
            Create homework assignment with batch, subject, teacher and due date
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.homeworks.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <div>
                    <p class="form-card-title">Homework Information</p>
                    <p class="form-card-subtitle">Basic homework title and description</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Title <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-heading icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Enter homework title"
                               class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('title'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('title') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="details">
                        Details
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="details"
                                  id="details"
                                  rows="6"
                                  placeholder="Enter homework details, instructions or task description"
                                  class="field-input {{ $errors->has('details') ? 'error' : '' }}">{{ old('details') }}</textarea>
                    </div>

                    @if($errors->has('details'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('details') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Add clear instructions for students
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="attachments">
                        Attachments
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-paperclip icon"></i>

                        <input type="file"
                               name="attachments[]"
                               id="attachments"
                               multiple
                               class="field-input {{ $errors->has('attachments') || $errors->has('attachments.*') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('attachments') || $errors->has('attachments.*'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('attachments') ?: $errors->first('attachments.*') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Optional: worksheet, PDF, image etc. (max 10MB each, multiple files allowed)
                        </p>
                    @endif
                </div>

                <input type="hidden" name="status" value="{{ old('status', 'active') }}">

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Select batch, subject and teacher</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="batch_id">
                        Batch <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                required
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            <option value="">Select Batch</option>

                            @foreach($batches as $id => $name)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">
                        Subject
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="subject_id"
                                id="subject_id"
                                class="field-input {{ $errors->has('subject_id') ? 'error' : '' }}">
                            <option value="">Select Subject</option>

                            @foreach($subjects as $id => $name)
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('subject_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="teacher_id">
                        Teacher
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-chalkboard-teacher icon"></i>

                        <select name="teacher_id"
                                id="teacher_id"
                                class="field-input {{ $errors->has('teacher_id') ? 'error' : '' }}">
                            <option value="">Select Teacher</option>

                            @foreach($teachers as $id => $name)
                                <option value="{{ $id }}" {{ old('teacher_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('teacher_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('teacher_id') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Homework will be visible to students of selected batch.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Date & Status</p>
                    <p class="form-card-subtitle">Set homework date, due date and active status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="homework_date">
                        Homework Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <input type="date"
                               name="homework_date"
                               id="homework_date"
                               value="{{ old('homework_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('homework_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('homework_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('homework_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="due_date">
                        Due Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="due_date"
                               id="due_date"
                               value="{{ old('due_date') }}"
                               class="field-input {{ $errors->has('due_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('due_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('due_date') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-clock"></i>
                            Students should complete homework before due date
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label">
                        Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <input type="text"
                               value="Active"
                               disabled
                               class="field-input">
                    </div>

                    <p class="field-hint">
                        <i class="fas fa-check-circle"></i>
                        New homework will be active by default
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.homeworks.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchSelect = document.getElementById('batch_id');
    const subjectSelect = document.getElementById('subject_id');
    const teacherSelect = document.getElementById('teacher_id');
    const subjectsByBatch = @json($subjectsByBatch);
    const teachersBySubject = @json($teachersBySubject);

    const renderTeachers = cascadeByParent(teacherSelect, subjectSelect, teachersBySubject, {
        placeholder: 'Optional',
        keepValue: @json(old('teacher_id')),
    });

    cascadeByParent(subjectSelect, batchSelect, subjectsByBatch, {
        placeholder: 'Optional',
        keepValue: @json(old('subject_id')),
        onRender: renderTeachers,
    });
});
</script>
@endsection