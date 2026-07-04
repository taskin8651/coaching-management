@extends('layouts.admin')

@section('page-title', 'Edit Student Batch')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Student Batch</h2>

        <p class="admin-page-subtitle">
            Update student batch, subject, duration and assignment status
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.update', $studentBatch->id) }}">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Assignment</p>
                    <p class="form-card-subtitle">Update student, batch and subject mapping</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">
                        Student <span class="req">*</span>
                    </label>

                    <div class="checkbox-grid">
                        @foreach($students as $id => $name)
                            @if($id)
                                <label class="role-checkbox-item {{ old('student_id', $studentBatch->student_id) == $id ? 'checked' : '' }}">
                                    <input type="radio"
                                           name="student_id"
                                           value="{{ $id }}"
                                           class="role-checkbox"
                                           required
                                           {{ old('student_id', $studentBatch->student_id) == $id ? 'checked' : '' }}>

                                    <div class="check-icon"></div>
                                    <span class="checkbox-text">{{ $name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    @if($errors->has('student_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_id') }}
                        </p>
                    @endif
                </div>

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
                                <option value="{{ $id }}" {{ old('batch_id', $studentBatch->batch_id) == $id ? 'selected' : '' }}>
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
                    <label class="field-label">
                        Subject
                    </label>

                    <div id="subjectRadioGrid" class="checkbox-grid">
                        <div class="form-info-box">
                            <p><i class="fas fa-info-circle"></i> Batch select karne ke baad subjects yahan show honge.</p>
                        </div>
                    </div>

                    @if($errors->has('subject_ids'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_ids') }}
                        </p>
                    @elseif($errors->has('subject_ids.*'))
                        <p class="field-error">{{ $errors->first('subject_ids.*') }}</p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Multiple subjects select kar sakte ho. Extra selected subjects ke liye new assignment create ho jayega.
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-lightbulb"></i>
                        Student ko multiple batches me assign kar sakte ho. Is page se selected mapping update hoga.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Batch Duration</p>
                    <p class="form-card-subtitle">Update start date, end date and status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="start_date">
                        Start Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <input type="date"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date', $studentBatch->start_date ? \Carbon\Carbon::parse($studentBatch->start_date)->format('Y-m-d') : '') }}"
                               class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('start_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('start_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="end_date">
                        End Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="end_date"
                               id="end_date"
                               value="{{ old('end_date', $studentBatch->end_date ? \Carbon\Carbon::parse($studentBatch->end_date)->format('Y-m-d') : '') }}"
                               class="field-input {{ $errors->has('end_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('end_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('end_date') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-clock"></i>
                            End date blank rahe to batch active/current maana jayega
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="active" {{ old('status', $studentBatch->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive" {{ old('status', $studentBatch->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Inactive karne par student selected batch ke active attendance, homework aur timetable mapping se remove maana jayega.
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

        <a href="{{ route('admin.student-batches.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchSubjects = @json($batchSubjects);
    const selectedSubjects = @json(old('subject_ids', $studentBatch->subject_id ? [$studentBatch->subject_id] : []));
    const batchSelect = document.getElementById('batch_id');
    const subjectWrapper = document.getElementById('subjectRadioGrid');

    function renderSubjects() {
        const subjects = batchSubjects[batchSelect.value] || [];
        subjectWrapper.innerHTML = '';

        subjects.forEach(function (subject) {
            const checked = selectedSubjects.map(String).includes(String(subject.id));
            const label = document.createElement('label');
            label.className = 'role-checkbox-item ' + (checked ? 'checked' : '');
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'subject_ids[]';
            input.value = subject.id;
            input.className = 'role-checkbox';
            input.checked = checked;
            const icon = document.createElement('div');
            icon.className = 'check-icon';
            const text = document.createElement('span');
            text.className = 'checkbox-text';
            text.textContent = subject.name;
            label.appendChild(input);
            label.appendChild(icon);
            label.appendChild(text);
            subjectWrapper.appendChild(label);
        });

        if (! subjects.length) {
            subjectWrapper.innerHTML += '<div class="form-info-box"><p><i class="fas fa-info-circle"></i> No subjects linked with selected batch.</p></div>';
        }
    }

    batchSelect.addEventListener('change', renderSubjects);
    document.addEventListener('change', function (event) {
        if (! event.target.classList.contains('role-checkbox')) {
            return;
        }

        const label = event.target.closest('.role-checkbox-item');
        if (label) {
            label.classList.toggle('checked', event.target.checked);
        }
    });
    renderSubjects();
});
</script>
@endsection
