@extends('layouts.admin')

@section('page-title', 'Add Notice')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.notices.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Notice / Announcement</h2>

        <p class="admin-page-subtitle">
            Publish notices for students, teachers, staff, branch, course or batch
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>

                <div>
                    <p class="form-card-title">Notice Details</p>
                    <p class="form-card-subtitle">Basic announcement information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Notice Title <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-heading icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Example: Monthly Test Notice"
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
                    <label class="field-label" for="notice_type">Notice Type</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-list icon"></i>

                        <select name="notice_type"
                                id="notice_type"
                                class="field-input {{ $errors->has('notice_type') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($noticeTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('notice_type') == $key ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('notice_type'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('notice_type') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="target_audience">
                        Target Audience <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="target_audience"
                                id="target_audience"
                                required
                                class="field-input {{ $errors->has('target_audience') ? 'error' : '' }}">
                            @foreach($targetAudiences as $key => $audience)
                                <option value="{{ $key }}" {{ old('target_audience', 'all') == $key ? 'selected' : '' }}>
                                    {{ $audience }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('target_audience'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('target_audience') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                required
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Target Mapping</p>
                    <p class="form-card-subtitle">Select branch, course or batch if needed</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="field-hint">Use when target audience is Specific Branch.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">Batch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id" id="batch_id" class="field-input">
                            @foreach($batches as $id => $batch)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="field-hint">Use when target audience is Specific Batch.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id" id="course_id" class="field-input">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="field-hint">Use when target audience is Specific Course.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="created_by_id">Created By</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <select name="created_by_id" id="created_by_id" class="field-input">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('created_by_id', auth()->id()) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Publish Date, Attachment & Description</p>
                    <p class="form-card-subtitle">Set notice validity and upload files</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="publish_date">Publish Date</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar icon"></i>

                                <input type="date"
                                       name="publish_date"
                                       id="publish_date"
                                       value="{{ old('publish_date', date('Y-m-d')) }}"
                                       class="field-input {{ $errors->has('publish_date') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('publish_date'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('publish_date') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="expiry_date">Expiry Date</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar-times icon"></i>

                                <input type="date"
                                       name="expiry_date"
                                       id="expiry_date"
                                       value="{{ old('expiry_date') }}"
                                       class="field-input {{ $errors->has('expiry_date') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('expiry_date'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('expiry_date') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="attachments">Attachments</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-file icon"></i>

                        <input type="file"
                               name="attachments[]"
                               id="attachments"
                               multiple
                               class="field-input {{ $errors->has('attachments') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('attachments'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('attachments') }}
                        </p>
                    @else
                        <p class="field-hint">Allowed: PDF, DOC, Images. Max: 10MB each.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">Description</label>

                    <textarea name="description"
                              id="description"
                              rows="6"
                              placeholder="Write notice description..."
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description') }}</textarea>

                    @if($errors->has('description'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('description') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.notices.index') }}" class="btn-ghost">
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
    const batchesByBranch = @json($batchesByBranch);
    const coursesByBatch = @json($coursesByBatch);
    const placeholder = @json(trans('global.pleaseSelect'));

    cascadeByParent(batchSelect, branchSelect, batchesByBranch, {
        placeholder,
        keepValue: @json(old('batch_id')),
    });

    cascadeByParent(courseSelect, batchSelect, coursesByBatch, {
        placeholder,
        keepValue: @json(old('course_id')),
    });
});
</script>
@endsection