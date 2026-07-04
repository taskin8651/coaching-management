@extends('layouts.admin')

@section('page-title', 'Assign Student Batch')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Assign Student Batch</h2>

        <p class="admin-page-subtitle">
            Assign student to batch, subject and active study period
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Assignment</p>
                    <p class="form-card-subtitle">Select student, batch and subject mapping</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">
                        Students <span class="req">*</span>
                    </label>

                    <div class="checkbox-grid">
                        @foreach($students as $id => $name)
                            @if($id)
                                <label class="role-checkbox-item {{ in_array($id, old('student_ids', [])) ? 'checked' : '' }}">
                                    <input type="checkbox"
                                           name="student_ids[]"
                                           value="{{ $id }}"
                                           class="role-checkbox"
                                           {{ in_array($id, old('student_ids', [])) ? 'checked' : '' }}>

                                    <div class="check-icon"></div>
                                    <span class="checkbox-text">{{ $name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    @if($errors->has('student_ids'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_ids') }}
                        </p>
                    @elseif($errors->has('student_ids.*'))
                        <p class="field-error">{{ $errors->first('student_ids.*') }}</p>
                    @else
                        <p class="field-hint">Hold Ctrl/Command to select multiple students.</p>
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
                    <label class="field-label">
                        Subjects
                    </label>

                    <div id="subjectCheckboxGrid" class="checkbox-grid">
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
                            Batch select karne ke baad sirf us batch ke linked subjects show honge. Hold Ctrl/Command to select multiple.
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-lightbulb"></i>
                        Ek student ko multiple batches me assign kar sakte ho, jaise 9-10 aur 10-11 dono batch.
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
                    <p class="form-card-subtitle">Set start date, end date and assignment status</p>
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
                               value="{{ old('start_date') }}"
                               class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('start_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('start_date') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Leave blank if assignment starts immediately
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
                               value="{{ old('end_date') }}"
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
                            Leave blank if batch is currently active
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
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
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
                        Active status hone par student selected batch ke attendance, homework aur timetable me include hoga.
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
    const oldSubjects = @json(old('subject_ids', []));
    const batchSelect = document.getElementById('batch_id');
    const subjectWrapper = document.getElementById('subjectCheckboxGrid');

    function renderSubjects() {
        const selectedBatch = batchSelect.value;
        const subjects = batchSubjects[selectedBatch] || [];
        subjectWrapper.innerHTML = '';

        subjects.forEach(function (subject) {
            const label = document.createElement('label');
            label.className = 'role-checkbox-item';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'subject_ids[]';
            input.value = subject.id;
            input.className = 'role-checkbox';

            if (oldSubjects.map(String).includes(String(subject.id))) {
                input.checked = true;
                label.classList.add('checked');
            }

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
            subjectWrapper.innerHTML = '<div class="form-info-box"><p><i class="fas fa-info-circle"></i> No subjects linked with selected batch.</p></div>';
        }
    }

    batchSelect.addEventListener('change', function () {
        oldSubjects.splice(0, oldSubjects.length);
        renderSubjects();
    });

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
