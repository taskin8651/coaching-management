@extends('layouts.admin')

@section('page-title', 'Edit Study Material')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.study-materials.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Study Material</h2>

        <p class="admin-page-subtitle">
            Update notes, files, links and academic mapping
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#4F46E5;">
            <i class="fas fa-book-reader"></i>
        </div>

        <div>
            <p class="identity-title">{{ $studyMaterial->title }}</p>
            <p class="identity-subtitle">ID #{{ $studyMaterial->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.study-materials.update', $studyMaterial->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book-reader"></i>
                </div>

                <div>
                    <p class="form-card-title">Material Details</p>
                    <p class="form-card-subtitle">Update study material information</p>
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
                               value="{{ old('title', $studyMaterial->title) }}"
                               required
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
                    <label class="field-label" for="material_type">Material Type</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-list icon"></i>

                        <select name="material_type"
                                id="material_type"
                                class="field-input {{ $errors->has('material_type') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($materialTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('material_type', $studyMaterial->material_type) == $key ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('material_type'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('material_type') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="external_link">External / Video Link</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-link icon"></i>

                        <input type="url"
                               name="external_link"
                               id="external_link"
                               value="{{ old('external_link', $studyMaterial->external_link) }}"
                               class="field-input {{ $errors->has('external_link') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('external_link'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('external_link') }}
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
                            <option value="active" {{ old('status', $studyMaterial->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $studyMaterial->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Update branch, course, batch and subject</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <select name="branch_id" id="branch_id" class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id', $studyMaterial->branch_id) == $id ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">Batch</label>

                    <select name="batch_id" id="batch_id" class="field-input">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ old('batch_id', $studyMaterial->batch_id) == $id ? 'selected' : '' }}>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>

                    <select name="course_id" id="course_id" class="field-input">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ old('course_id', $studyMaterial->course_id) == $id ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">Subject</label>

                    <select name="subject_id" id="subject_id" class="field-input">
                        @foreach($subjects as $id => $subject)
                            <option value="{{ $id }}" {{ old('subject_id', $studyMaterial->subject_id) == $id ? 'selected' : '' }}>
                                {{ $subject }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="uploaded_by_id">Uploaded By</label>

                    <select name="uploaded_by_id" id="uploaded_by_id" class="field-input">
                        @foreach($users as $id => $user)
                            <option value="{{ $id }}" {{ old('uploaded_by_id', $studyMaterial->uploaded_by_id) == $id ? 'selected' : '' }}>
                                {{ $user }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Files & Description</p>
                    <p class="form-card-subtitle">Add more files and update description</p>
                </div>
            </div>

            <div class="form-card-body">

                @if($studyMaterial->files && count($studyMaterial->files))
                    <div class="form-info-box" style="margin-bottom:18px;">
                        <p class="meta-label">Uploaded Files</p>

                        @foreach($studyMaterial->getMedia('study_material_files') as $file)
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid #E2E8F0;">
                                <a href="{{ $file->getUrl() }}" target="_blank">
                                    <i class="fas fa-file"></i>
                                    {{ $file->file_name }}
                                </a>

                                @can('study_material_edit')
                                    <form action="{{ route('admin.study-materials.media.destroy', $file->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf

                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                            Remove
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="field-group">
                    <label class="field-label" for="files">Add More Files</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-file icon"></i>

                        <input type="file"
                               name="files[]"
                               id="files"
                               multiple
                               class="field-input {{ $errors->has('files') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('files'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('files') }}
                        </p>
                    @else
                        <p class="field-hint">New files will be added with existing files.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">Description</label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description', $studyMaterial->description) }}</textarea>

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

        <a href="{{ route('admin.study-materials.index') }}" class="btn-ghost">
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
        keepValue: @json(old('batch_id', $studyMaterial->batch_id)),
    });

    cascadeByParent(courseSelect, batchSelect, coursesByBatch, {
        placeholder,
        keepValue: @json(old('course_id', $studyMaterial->course_id)),
    });

    cascadeByParent(subjectSelect, batchSelect, subjectsByBatch, {
        placeholder,
        keepValue: @json(old('subject_id', $studyMaterial->subject_id)),
    });
});
</script>
@endsection