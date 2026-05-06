@extends('layouts.admin')

@section('page-title', 'Add Study Material')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.study-materials.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Study Material</h2>

        <p class="admin-page-subtitle">
            Upload notes, PDFs, assignments, practice papers or video links
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.study-materials.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book-reader"></i>
                </div>

                <div>
                    <p class="form-card-title">Material Details</p>
                    <p class="form-card-subtitle">Basic study material information</p>
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
                               placeholder="Example: Physics Chapter 1 Notes"
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
                                <option value="{{ $key }}" {{ old('material_type') == $key ? 'selected' : '' }}>
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
                               value="{{ old('external_link') }}"
                               placeholder="https://example.com/video-or-resource"
                               class="field-input {{ $errors->has('external_link') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('external_link'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('external_link') }}
                        </p>
                    @else
                        <p class="field-hint">Use this for YouTube, Google Drive, video class link etc.</p>
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
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
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
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Select branch, course, batch and subject</p>
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
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">Subject</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <select name="subject_id" id="subject_id" class="field-input">
                            @foreach($subjects as $id => $subject)
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
                                    {{ $subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="uploaded_by_id">Uploaded By</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <select name="uploaded_by_id" id="uploaded_by_id" class="field-input">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('uploaded_by_id', auth()->id()) == $id ? 'selected' : '' }}>
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
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Files & Description</p>
                    <p class="form-card-subtitle">Upload material files and add description</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="files">Upload Files</label>

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
                        <p class="field-hint">
                            Allowed: PDF, DOC, PPT, Excel, Images, ZIP. Max: 10MB each.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">Description</label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              placeholder="Enter material description"
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

        <a href="{{ route('admin.study-materials.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection