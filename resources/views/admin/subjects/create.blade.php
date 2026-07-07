@extends('layouts.admin')

@section('page-title', 'Add Subject')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.subjects.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Subject</h2>

        <p class="admin-page-subtitle">
            Create a subject under selected branch and course
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.subjects.store') }}">
    @csrf
    @php
        $selectedBranchId = old('branch_id', $defaultBranchId ?? null);
        $selectedCourseId = old('course_id');
    @endphp

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <div>
                    <p class="form-card-title">Subject Information</p>
                    <p class="form-card-subtitle">Basic subject details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Subject Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="Example: Physics, Maths, Biology"
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_code">
                        Subject Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="subject_code"
                               id="subject_code"
                               value="{{ old('subject_code') }}"
                               placeholder="SUB-001"
                               class="field-input {{ $errors->has('subject_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('subject_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_code') }}
                        </p>
                    @else
                        <p class="field-hint">Subject code should be unique.</p>
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
                    <p class="form-card-title">Branch & Course</p>
                    <p class="form-card-subtitle">Link subject with course</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                data-selected="{{ $selectedBranchId }}"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ $selectedBranchId == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('branch_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">
                        Course
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id"
                                id="course_id"
                                data-selected="{{ $selectedCourseId }}"
                                class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ $selectedCourseId == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('course_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('course_id') }}
                        </p>
                    @else
                        <p class="field-hint">Subject will be used inside selected course.</p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Later this subject can be assigned to teacher and batch.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-align-left"></i>
                </div>

                <div>
                    <p class="form-card-title">Description</p>
                    <p class="form-card-subtitle">Subject overview</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label" for="description">
                        Description
                    </label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              placeholder="Enter subject description"
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

        <a href="{{ route('admin.subjects.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const coursesByBranch = @json($coursesByBranch);
    const placeholder = @json(trans('global.pleaseSelect'));

    function renderCourses() {
        if (!branchSelect || !courseSelect) return;

        const branchId = branchSelect.value;
        const selectedCourseId = courseSelect.dataset.selected || '';
        const courses = branchId && coursesByBranch[branchId] ? coursesByBranch[branchId] : [];

        courseSelect.innerHTML = '';
        courseSelect.appendChild(new Option(placeholder, ''));

        courses.forEach(function (course) {
            const option = new Option(course.name, course.id);
            option.selected = String(course.id) === String(selectedCourseId);
            courseSelect.appendChild(option);
        });

        if (!courses.some(course => String(course.id) === String(selectedCourseId))) {
            courseSelect.value = '';
        }
    }

    branchSelect?.addEventListener('change', function () {
        courseSelect.dataset.selected = '';
        renderCourses();
    });

    renderCourses();
});
</script>
@endsection
