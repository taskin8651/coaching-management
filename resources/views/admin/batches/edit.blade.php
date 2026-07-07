@extends('layouts.admin')

@section('page-title', 'Edit Batch')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Batch</h2>

        <p class="admin-page-subtitle">
            Update batch details, course mapping and timing
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background: {{ $colors[$batch->id % count($colors)] }};">
            {{ strtoupper(substr($batch->name, 0, 1)) }}
        </div>

        <div>
            <p class="identity-title">{{ $batch->name }}</p>
            <p class="identity-subtitle">ID #{{ $batch->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.batches.update', $batch->id) }}">
    @method('PUT')
    @csrf
    @php
        $selectedBranchId = old('branch_id', $batch->branch_id);
        $selectedCourseId = old('course_id', $batch->course_id);
        $selectedSubjects = array_map('intval', old('subject_ids', $batch->subjects->pluck('id')->toArray()));
    @endphp

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Batch Information</p>
                    <p class="form-card-subtitle">Update batch details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Batch Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-layer-group icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $batch->name) }}"
                               required
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
                    <label class="field-label" for="batch_code">
                        Batch Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="batch_code"
                               id="batch_code"
                               value="{{ old('batch_code', $batch->batch_code) }}"
                               class="field-input {{ $errors->has('batch_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('batch_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_code') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="max_students">
                        Maximum Students
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <input type="number"
                               name="max_students"
                               id="max_students"
                               min="1"
                               value="{{ old('max_students', $batch->max_students) }}"
                               class="field-input {{ $errors->has('max_students') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('max_students'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('max_students') }}
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
                            <option value="active" {{ old('status', $batch->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $batch->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="completed" {{ old('status', $batch->status) == 'completed' ? 'selected' : '' }}>Completed</option>
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
                    <i class="fas fa-school"></i>
                </div>

                <div>
                    <p class="form-card-title">Branch & Course</p>
                    <p class="form-card-subtitle">Update batch mapping</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    @if(auth()->user()->is_admin)
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
                    @else
                        <input type="hidden"
                               name="branch_id"
                               id="branch_id"
                               data-selected="{{ $selectedBranchId }}"
                               value="{{ $selectedBranchId }}">

                        <div class="form-info-box">
                            <p>
                                <i class="fas fa-school"></i>
                                Branch auto selected: {{ $branches[$selectedBranchId] ?? $batch->branch->name ?? 'Your Branch' }}
                            </p>
                        </div>
                    @endif

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
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label">
                        Subjects
                    </label>

                    <div class="checkbox-grid" id="subject-checkbox-grid">
                        @forelse($subjects as $id => $subject)
                            <label class="role-checkbox-item {{ in_array((int) $id, $selectedSubjects, true) ? 'checked' : '' }}">
                                <input type="checkbox"
                                       name="subject_ids[]"
                                       value="{{ $id }}"
                                       class="role-checkbox"
                                       {{ in_array((int) $id, $selectedSubjects, true) ? 'checked' : '' }}>

                                <div class="check-icon"></div>
                                <span class="checkbox-text">{{ $subject }}</span>
                            </label>
                        @empty
                            <div class="form-info-box">
                                <p><i class="fas fa-info-circle"></i> No active subjects found for selected branch.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($errors->has('subject_ids'))
                        <p class="field-error">{{ $errors->first('subject_ids') }}</p>
                    @elseif($errors->has('subject_ids.*'))
                        <p class="field-error">{{ $errors->first('subject_ids.*') }}</p>
                    @else
                        <p class="field-hint">Select one or more subjects linked with this batch.</p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p class="meta-label">Batch Info</p>

                    <div class="meta-grid-2">
                        <div>
                            <p class="meta-small-label">Created</p>
                            <p class="meta-value-strong">
                                {{ optional($batch->created_at)->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="meta-small-label">Status</p>

                            @if($batch->status == 'active')
                                <p class="meta-value-strong meta-value-success">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </p>
                            @elseif($batch->status == 'completed')
                                <p class="meta-value-strong" style="color:#075985;">
                                    <i class="fas fa-check-double"></i>
                                    Completed
                                </p>
                            @else
                                <p class="meta-value-strong meta-value-warning">
                                    <i class="fas fa-clock"></i>
                                    Inactive
                                </p>
                            @endif
                        </div>
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
                    <p class="form-card-title">Schedule Details</p>
                    <p class="form-card-subtitle">Update batch date and timing</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="start_time">Start Time</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-clock icon"></i>

                                <input type="time"
                                       name="start_time"
                                       id="start_time"
                                       value="{{ old('start_time', $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('H:i') : '') }}"
                                       class="field-input {{ $errors->has('start_time') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('start_time'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('start_time') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="end_time">End Time</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-clock icon"></i>

                                <input type="time"
                                       name="end_time"
                                       id="end_time"
                                       value="{{ old('end_time', $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('H:i') : '') }}"
                                       class="field-input {{ $errors->has('end_time') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('end_time'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('end_time') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">
                        Description
                    </label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description', $batch->description) }}</textarea>

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

        <a href="{{ route('admin.batches.index') }}" class="btn-ghost">
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
    const subjectGrid = document.getElementById('subject-checkbox-grid');
    const coursesByBranch = @json($coursesByBranch);
    const subjectsByBranchCourse = @json($subjectsByBranchCourse);
    const placeholder = @json(trans('global.pleaseSelect'));
    let selectedSubjectIds = new Set(@json($selectedSubjects).map(String));

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

    function getSubjects() {
        const branchId = branchSelect?.value || '';
        const courseId = courseSelect?.value || '';
        const branchSubjects = branchId && subjectsByBranchCourse[branchId] ? subjectsByBranchCourse[branchId] : {};
        const commonSubjects = branchSubjects.all || [];
        const courseSubjects = courseId && branchSubjects[courseId] ? branchSubjects[courseId] : [];
        const merged = [...commonSubjects, ...courseSubjects];
        const seen = new Set();

        return merged.filter(function (subject) {
            if (seen.has(String(subject.id))) return false;
            seen.add(String(subject.id));
            return true;
        });
    }

    function renderSubjects() {
        if (!subjectGrid) return;

        const subjects = getSubjects();
        subjectGrid.innerHTML = '';

        if (!subjects.length) {
            subjectGrid.innerHTML = '<div class="form-info-box"><p><i class="fas fa-info-circle"></i> No active subjects found for selected branch/course.</p></div>';
            return;
        }

        subjects.forEach(function (subject) {
            const id = String(subject.id);
            const checked = selectedSubjectIds.has(id);
            const label = document.createElement('label');
            label.className = 'role-checkbox-item' + (checked ? ' checked' : '');
            label.innerHTML = `
                <input type="checkbox" name="subject_ids[]" value="${id}" class="role-checkbox" ${checked ? 'checked' : ''}>
                <div class="check-icon"></div>
                <span class="checkbox-text">${subject.name}</span>
            `;
            subjectGrid.appendChild(label);
        });
    }

    subjectGrid?.addEventListener('change', function (event) {
        if (!event.target.matches('input[name="subject_ids[]"]')) return;

        if (event.target.checked) {
            selectedSubjectIds.add(String(event.target.value));
            event.target.closest('.role-checkbox-item')?.classList.add('checked');
        } else {
            selectedSubjectIds.delete(String(event.target.value));
            event.target.closest('.role-checkbox-item')?.classList.remove('checked');
        }
    });

    branchSelect?.addEventListener('change', function () {
        courseSelect.dataset.selected = '';
        selectedSubjectIds = new Set();
        renderCourses();
        renderSubjects();
    });

    courseSelect?.addEventListener('change', function () {
        courseSelect.dataset.selected = courseSelect.value;
        selectedSubjectIds = new Set();
        renderSubjects();
    });

    renderCourses();
    renderSubjects();
});
</script>
@endsection
