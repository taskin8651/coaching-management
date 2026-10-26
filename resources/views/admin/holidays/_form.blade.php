
@php
    $isEdit = (bool) $holiday;

    $val = fn ($field, $default = null) => old(
        $field,
        $isEdit ? $holiday->{$field} : $default
    );

    $targetMode = old(
        'target_mode',
        $isEdit
            ? ($holiday->target_mode ?? ($holiday->branch_id ? 'branch_wise' : 'all_branches'))
            : ($scope['is_admin'] ? 'all_branches' : 'branch_wise')
    );

    $targetEntity = match ($targetMode) {
        'branch_wise' => 'branch',
        'batch_wise' => 'batch',
        'selected_students' => 'student',
        'selected_teachers', 'teacher_wise', 'single_branch_teachers' => 'teacher',
        'selected_staff', 'single_branch_staff' => 'staff',
        default => '',
    };

    $selectedTargets = old(
        'target_ids',
        isset($holidayTargets)
            ? $holidayTargets
                ->where('target_type', $targetEntity)
                ->pluck('target_id')
                ->all()
            : []
    );

    $selectedTargets = array_map('strval', (array) $selectedTargets);

    $audiences = old(
        'audiences',
        $isEdit ? ($holiday->audiences ?? []) : ['students', 'teachers', 'staff']
    );

    if (is_string($audiences)) {
        $audiences = json_decode($audiences, true) ?: [];
    }

    $audiences = is_array($audiences) ? $audiences : [];

    $displayName = fn ($record) =>
        data_get($record, 'user.name')
        ?? data_get($record, 'name')
        ?? (is_object($record) ? '#' . $record->id : (string) $record);

    $branchDefault = old(
        'target_branch_id',
        $isEdit
            ? ($holiday->branch_id ?? '')
            : ($scope['is_admin'] ? '' : ($scope['branch_id'] ?? ''))
    );

    $targetOptions = [
        'all_branches' => 'All Branches',
        'branch_wise' => 'Branch Wise',
        'batch_wise' => 'Batch Wise',
        'selected_students' => 'Selected Students',
        'selected_teachers' => 'Teachers Wise',
        'all_branch_teachers' => 'All Branch Teachers',
        'single_branch_teachers' => 'Single Branch Teachers',
        'teacher_wise' => 'Teacher Wise',
        'all_branch_staff' => 'All Branch Staff',
        'single_branch_staff' => 'Single Branch Staff',
        'selected_staff' => 'Selected Staff',
    ];
@endphp

<style>
    .holiday-layout {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: start;
        gap: 20px;
    }

    .holiday-layout > .form-card {
        min-width: 0;
        height: 100%;
    }

    .holiday-layout .form-card-body,
    .holiday-layout .field-group {
        min-width: 0;
    }

    .holiday-layout .field-input {
        max-width: 100%;
    }

    .holiday-layout .checkbox-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .holiday-layout .role-checkbox-item {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .holiday-layout .form-actions {
        grid-column: 1 / -1;
    }

    .holiday-layout .holiday-target-field {
        grid-column: 1 / -1;
    }

    @media (max-width: 767px) {
        .holiday-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .holiday-layout .checkbox-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="admin-form-grid holiday-layout">

    {{-- LEFT SIDE: HOLIDAY DETAILS --}}
    <div class="form-card">

        <div class="form-card-header">
            <div class="form-card-icon">
                <i class="fas fa-umbrella-beach"></i>
            </div>

            <div>
                <p class="form-card-title">Holiday Details</p>
                <p class="form-card-subtitle">
                    Enter holiday information and dates
                </p>
            </div>
        </div>

        <div class="form-card-body">

            <div class="field-group">
                <label class="field-label" for="name">
                    Holiday Name <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-calendar-alt icon"></i>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ $val('name') }}"
                        maxlength="255"
                        required
                        placeholder="Enter holiday name"
                        class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                    >
                </div>

                @error('name')
                    <p class="field-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="field-group">
                <label class="field-label" for="category">
                    Holiday Category <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-list icon"></i>

                    <select
                        name="category"
                        id="category"
                        required
                        class="field-input {{ $errors->has('category') ? 'error' : '' }}"
                    >
                        @foreach([
                            'festival' => 'Festival',
                            'public_holiday' => 'Public Holiday',
                            'institute_break' => 'Institute Break',
                            'sports_event' => 'Sports / Event',
                            'other' => 'Other',
                        ] as $key => $label)
                            <option
                                value="{{ $key }}"
                                @selected($val('category', 'other') === $key)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('category')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-group">
                <label class="field-label" for="holiday_start_date">
                    Start Date <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-calendar-day icon"></i>

                    <input
                        type="date"
                        name="start_date"
                        id="holiday_start_date"
                        value="{{ $val('start_date', request('date')) ? \Illuminate\Support\Carbon::parse($val('start_date', request('date')))->format('Y-m-d') : '' }}"
                        required
                        class="field-input {{ $errors->has('start_date') ? 'error' : '' }}"
                    >
                </div>

                @error('start_date')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-group">
                <label class="field-label" for="holiday_end_date">
                    End Date <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-calendar-check icon"></i>

                    <input
                        type="date"
                        name="end_date"
                        id="holiday_end_date"
                        value="{{ $val('end_date', request('date')) ? \Illuminate\Support\Carbon::parse($val('end_date', request('date')))->format('Y-m-d') : '' }}"
                        required
                        class="field-input {{ $errors->has('end_date') ? 'error' : '' }}"
                    >
                </div>

                @error('end_date')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-group">
                <label class="field-label" for="type">
                    Holiday Type <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-tag icon"></i>

                    <select
                        name="type"
                        id="type"
                        required
                        class="field-input {{ $errors->has('type') ? 'error' : '' }}"
                    >
                        <option
                            value="mandatory"
                            @selected($val('type', 'mandatory') === 'mandatory')
                        >
                            Mandatory
                        </option>

                        <option
                            value="optional"
                            @selected($val('type') === 'optional')
                        >
                            Optional
                        </option>
                    </select>
                </div>

                <p class="field-hint">
                    <i class="fas fa-info-circle"></i>
                    Existing holiday type is preserved.
                </p>

                @error('type')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label" for="description">
                    Description / Remarks
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    placeholder="Enter holiday details..."
                    class="field-input {{ $errors->has('description') ? 'error' : '' }}"
                >{{ $val('description') }}</textarea>

                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- RIGHT SIDE: AUDIENCE AND TARGETING --}}
    <div class="form-card">

        <div class="form-card-header">
            <div class="form-card-icon">
                <i class="fas fa-users"></i>
            </div>

            <div>
                <p class="form-card-title">Audience and Targeting</p>
                <p class="form-card-subtitle">
                    Choose who will receive this holiday
                </p>
            </div>
        </div>

        <div class="form-card-body">

            {{-- Holiday For --}}
            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label">
                    Holiday For <span class="req">*</span>
                </label>

                <div class="checkbox-grid">
                    @foreach([
                        'students' => 'Students',
                        'teachers' => 'Teachers',
                        'staff' => 'Staff',
                    ] as $key => $label)

                        <label class="role-checkbox-item {{ in_array($key, $audiences) ? 'checked' : '' }}">
                            <input
                                type="checkbox"
                                name="audiences[]"
                                value="{{ $key }}"
                                class="role-checkbox"
                                @checked(in_array($key, $audiences))
                            >

                            <div class="check-icon"></div>

                            <span class="checkbox-text">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('audiences')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                @error('audiences.*')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Apply To --}}
            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label" for="holiday_target_mode">
                    Apply To <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-bullseye icon"></i>

                    <select
                        name="target_mode"
                        id="holiday_target_mode"
                        required
                        class="field-input {{ $errors->has('target_mode') ? 'error' : '' }}"
                    >
                        @foreach($targetOptions as $key => $label)
                            @if(
                                $scope['is_admin']
                                || !in_array($key, [
                                    'all_branches',
                                    'all_branch_teachers',
                                    'all_branch_staff',
                                ])
                            )
                                <option
                                    value="{{ $key }}"
                                    @selected($targetMode === $key)
                                >
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                @error('target_mode')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Branch for branch-dependent modes --}}
            <div
                class="field-group holiday-branch-field"
                style="grid-column: 1 / -1;"
            >
                <label class="field-label" for="holiday_target_branch">
                    Select Branch <span class="req">*</span>
                </label>

                <div class="input-icon-wrap">
                    <i class="fas fa-school icon"></i>

                    <select
                        name="target_branch_id"
                        id="holiday_target_branch"
                        class="field-input {{ $errors->has('target_branch_id') ? 'error' : '' }}"
                    >
                        <option value="">Choose a branch</option>

                        @foreach($branches as $id => $branchName)
                            @if($id !== '')
                                <option
                                    value="{{ $id }}"
                                    @selected((string) $branchDefault === (string) $id)
                                >
                                    {{ $branchName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                @error('target_branch_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Searchable checkbox lists --}}
            @foreach([
                'branch' => ['Branches', $branches],
                'batch' => ['Batches', $batches],
                'student' => ['Students', $students],
                'teacher' => ['Teachers', $teachers],
                'staff' => ['Staff Members', $staff],
            ] as $entity => [$label, $records])

                <div
                    class="field-group holiday-target-field"
                    data-entity="{{ $entity }}"
                    style="grid-column: 1 / -1;"
                >
                    <label class="field-label">
                        Select {{ $label }} <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap mb-2">
                        <i class="fas fa-search icon"></i>

                        <input
                            type="search"
                            class="field-input"
                            placeholder="Search {{ strtolower($label) }}..."
                            data-search="{{ $entity }}"
                        >
                    </div>

                    <div class="form-mini-actions mb-2">
                        <button
                            type="button"
                            class="btn-mini-primary"
                            data-select-all="{{ $entity }}"
                        >
                            Select All
                        </button>

                        <button
                            type="button"
                            class="btn-mini-ghost"
                            data-clear="{{ $entity }}"
                        >
                            Clear Selection
                        </button>
                    </div>

                    <div
                        class="checkbox-grid"
                        data-checkbox-list="{{ $entity }}"
                    >
                        @foreach($records as $id => $record)
                            @php
                                $optionId = is_string($record)
                                    ? $id
                                    : $record->id;

                                $recordBranchId = is_string($record)
                                    ? ''
                                    : ($record->branch_id ?? '');

                                $recordBranch = is_string($record)
                                    ? ''
                                    : ($record->branch->name ?? '');

                                $recordName = is_string($record)
                                    ? $record
                                    : $displayName($record);

                                $isSelected = in_array(
                                    (string) $optionId,
                                    $selectedTargets
                                );
                            @endphp

                            <label
                                class="role-checkbox-item {{ $isSelected ? 'checked' : '' }}"
                                data-target-item="{{ $entity }}"
                                data-branch="{{ $recordBranchId }}"
                            >
                                <input
                                    type="checkbox"
                                    name="target_ids[]"
                                    value="{{ $optionId }}"
                                    class="role-checkbox"
                                    data-target-checkbox="{{ $entity }}"
                                    data-target-branch="{{ $recordBranchId }}"
                                    @checked($isSelected)
                                >

                                <div class="check-icon"></div>

                                <span class="checkbox-text">
                                    {{ $recordName }}

                                    @if($recordBranch)
                                        <small> — Branch {{ $recordBranch }}</small>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <p class="field-hint">
                        <i class="fas fa-info-circle"></i>
                        <span data-count="{{ $entity }}">0 selected</span>
                    </p>

                    @error('target_ids')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    @error('target_ids.*')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

        </div>
    </div>

    {{-- FORM ACTIONS --}}
    <div class="form-actions" style="grid-column: 1 / -1;">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.holidays.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</div>

<script>
(function () {
    const mode = document.getElementById('holiday_target_mode');
    const branch = document.getElementById('holiday_target_branch');
    const start = document.getElementById('holiday_start_date');
    const end = document.getElementById('holiday_end_date');

    if (!mode || !branch || !start || !end) {
        return;
    }

    const targetMap = {
        branch_wise: 'branch',
        batch_wise: 'batch',
        selected_students: 'student',
        selected_teachers: 'teacher',
        teacher_wise: 'teacher',
        single_branch_teachers: 'teacher',
        selected_staff: 'staff',
        single_branch_staff: 'staff'
    };

    const branchModes = [
        'batch_wise',
        'single_branch_teachers',
        'single_branch_staff'
    ];

    function updateCount(entity) {
        const list = document.querySelector(
            '[data-checkbox-list="' + entity + '"]'
        );

        const counter = document.querySelector(
            '[data-count="' + entity + '"]'
        );

        if (!list || !counter) return;

        counter.textContent =
            list.querySelectorAll('[data-target-checkbox]:checked').length
            + ' selected';
    }

    function filterTargets() {
        const selectedBranch = branch.value;
        const currentMode = mode.value;

        document.querySelectorAll('[data-checkbox-list]').forEach(list => {
            const entity = list.dataset.checkboxList;

            const search = document.querySelector(
                '[data-search="' + entity + '"]'
            );

            const term = search ? search.value.toLowerCase().trim() : '';

            const shouldFilter =
                (currentMode === 'batch_wise' && entity === 'batch') ||
                (currentMode === 'single_branch_teachers' && entity === 'teacher') ||
                (currentMode === 'single_branch_staff' && entity === 'staff');

            list.querySelectorAll('[data-target-item]').forEach(item => {
                const checkbox = item.querySelector('[data-target-checkbox]');
                const itemBranch = checkbox.dataset.targetBranch || '';

                const branchMatches = !shouldFilter ||
                    (selectedBranch !== '' && itemBranch === selectedBranch);

                const searchMatches = item.textContent
                    .toLowerCase()
                    .includes(term);

                item.style.display =
                    branchMatches && searchMatches ? '' : 'none';

                checkbox.disabled =
                    !branchMatches ||
                    list.closest('.holiday-target-field').style.display === 'none';

                if (!branchMatches && checkbox.checked) {
                    checkbox.checked = false;
                    item.classList.remove('checked');
                }
            });

            updateCount(entity);
        });
    }

    function updateForm() {
        const activeEntity = targetMap[mode.value] || '';

        document.querySelectorAll('.holiday-target-field').forEach(field => {
            const entity = field.dataset.entity;
            const active = entity === activeEntity;

            field.style.display = active ? '' : 'none';

            field.querySelectorAll('[data-target-checkbox]').forEach(checkbox => {
                checkbox.disabled = !active;
            });
        });

        const needsBranch = branchModes.includes(mode.value);
        const branchField = document.querySelector('.holiday-branch-field');

        branchField.style.display = needsBranch ? '' : 'none';
        branch.disabled = !needsBranch;
        branch.required = needsBranch;

        filterTargets();
    }

    document.querySelectorAll('[data-target-checkbox]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            this.closest('.role-checkbox-item')
                ?.classList.toggle('checked', this.checked);

            updateCount(this.dataset.targetCheckbox);
        });
    });

    document.querySelectorAll('[data-search]').forEach(input => {
        input.addEventListener('input', filterTargets);
    });

    document.querySelectorAll('[data-select-all]').forEach(button => {
        button.addEventListener('click', function () {
            const entity = this.dataset.selectAll;

            document.querySelectorAll(
                '[data-target-checkbox="' + entity + '"]'
            ).forEach(checkbox => {
                const item = checkbox.closest('[data-target-item]');

                if (
                    !checkbox.disabled &&
                    item &&
                    item.style.display !== 'none'
                ) {
                    checkbox.checked = true;
                    item.classList.add('checked');
                }
            });

            updateCount(entity);
        });
    });

    document.querySelectorAll('[data-clear]').forEach(button => {
        button.addEventListener('click', function () {
            const entity = this.dataset.clear;

            document.querySelectorAll(
                '[data-target-checkbox="' + entity + '"]'
            ).forEach(checkbox => {
                checkbox.checked = false;

                checkbox.closest('.role-checkbox-item')
                    ?.classList.remove('checked');
            });

            updateCount(entity);
        });
    });

    document.querySelectorAll('.role-checkbox[name="audiences[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            this.closest('.role-checkbox-item')
                ?.classList.toggle('checked', this.checked);
        });
    });

    mode.addEventListener('change', updateForm);
    branch.addEventListener('change', filterTargets);

    start.addEventListener('change', function () {
        end.min = this.value;

        if (end.value && end.value < this.value) {
            end.value = this.value;
        }
    });

    end.addEventListener('change', function () {
        this.setCustomValidity(
            start.value && this.value && this.value < start.value
                ? 'End date cannot be before the start date.'
                : ''
        );
    });

    if (start.value) {
        end.min = start.value;
    }

    document.querySelectorAll('[data-checkbox-list]').forEach(list => {
        updateCount(list.dataset.checkboxList);
    });

    updateForm();
})();
</script>
