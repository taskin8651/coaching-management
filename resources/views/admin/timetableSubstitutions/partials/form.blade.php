@php
    $substitution = $substitution ?? null;
@endphp

<div class="admin-form-grid">
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>

            <div>
                <p class="form-card-title">Timetable & Date</p>
                <p class="form-card-subtitle">Select the lecture where substitute teacher is required.</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label" for="timetable_id">Timetable <span class="req">*</span></label>

                <select name="timetable_id"
                        id="timetable_id"
                        required
                        class="field-input {{ $errors->has('timetable_id') ? 'error' : '' }}">
                    @foreach($timetables as $id => $label)
                        <option value="{{ $id }}" {{ old('timetable_id', $substitution->timetable_id ?? '') == $id ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @if($errors->has('timetable_id'))
                    <p class="field-error">{{ $errors->first('timetable_id') }}</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="substitution_date">Substitution Date <span class="req">*</span></label>

                <div class="input-icon-wrap">
                    <i class="fas fa-calendar-check icon"></i>
                    <input type="date"
                           name="substitution_date"
                           id="substitution_date"
                           value="{{ old('substitution_date', ! empty($substitution?->substitution_date) ? \Carbon\Carbon::parse($substitution->substitution_date)->format('Y-m-d') : '') }}"
                           required
                           class="field-input {{ $errors->has('substitution_date') ? 'error' : '' }}">
                </div>

                @if($errors->has('substitution_date'))
                    <p class="field-error">{{ $errors->first('substitution_date') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon">
                <i class="fas fa-user-clock"></i>
            </div>

            <div>
                <p class="form-card-title">Substitute Details</p>
                <p class="form-card-subtitle">Choose replacement teacher and note the reason.</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label" for="substitute_teacher_id">Substitute Teacher <span class="req">*</span></label>

                <select name="substitute_teacher_id"
                        id="substitute_teacher_id"
                        required
                        data-selected="{{ old('substitute_teacher_id', $substitution->substitute_teacher_id ?? '') }}"
                        class="field-input {{ $errors->has('substitute_teacher_id') ? 'error' : '' }}">
                    <option value="">Select timetable and date first</option>
                </select>

                @if($errors->has('substitute_teacher_id'))
                    <p class="field-error">{{ $errors->first('substitute_teacher_id') }}</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="reason">Reason</label>
                <textarea name="reason"
                          id="reason"
                          rows="4"
                          class="field-input {{ $errors->has('reason') ? 'error' : '' }}"
                          placeholder="Reason for substitution">{{ old('reason', $substitution->reason ?? '') }}</textarea>

                @if($errors->has('reason'))
                    <p class="field-error">{{ $errors->first('reason') }}</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="change_note">Internal Change Note</label>
                <textarea name="change_note"
                          id="change_note"
                          rows="4"
                          class="field-input {{ $errors->has('change_note') ? 'error' : '' }}"
                          placeholder="Optional admin note">{{ old('change_note', $substitution->change_note ?? '') }}</textarea>

                @if($errors->has('change_note'))
                    <p class="field-error">{{ $errors->first('change_note') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timetableSelect = document.getElementById('timetable_id');
    const dateInput = document.getElementById('substitution_date');
    const teacherSelect = document.getElementById('substitute_teacher_id');
    const selectedTeacher = teacherSelect.dataset.selected || '';
    const ignoreSubstitutionId = @json($substitution->id ?? null);
    const endpoint = @json(route('admin.timetable-substitutions.free-teachers'));

    function setPlaceholder(text) {
        teacherSelect.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = text;
        teacherSelect.appendChild(option);
    }

    function loadFreeTeachers() {
        if (! timetableSelect.value || ! dateInput.value) {
            setPlaceholder('Select timetable and date first');
            return;
        }

        setPlaceholder('Loading free teachers...');

        const url = new URL(endpoint);
        url.searchParams.set('timetable_id', timetableSelect.value);
        url.searchParams.set('substitution_date', dateInput.value);

        if (ignoreSubstitutionId) {
            url.searchParams.set('ignore_substitution_id', ignoreSubstitutionId);
        }

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(data => {
                teacherSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = data.teachers && data.teachers.length ? 'Select Substitute Teacher' : 'No free teacher found';
                teacherSelect.appendChild(defaultOption);

                (data.teachers || []).forEach(function (teacher) {
                    const option = document.createElement('option');
                    option.value = teacher.id;
                    option.textContent = teacher.name;
                    option.selected = String(selectedTeacher) === String(teacher.id);
                    teacherSelect.appendChild(option);
                });
            })
            .catch(() => setPlaceholder('Unable to load free teachers'));
    }

    timetableSelect.addEventListener('change', loadFreeTeachers);
    dateInput.addEventListener('change', loadFreeTeachers);
    loadFreeTeachers();
});
</script>

<div class="form-actions">
    <button type="submit" class="btn-primary">
        <i class="fas fa-check"></i>
        {{ $submitText ?? trans('global.save') }}
    </button>

    <a href="{{ route('admin.timetable-substitutions.index') }}" class="btn-ghost">
        {{ trans('global.cancel') }}
    </a>
</div>
