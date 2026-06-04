@extends('layouts.admin')

@section('page-title', 'Add Maintenance')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.maintenance-requests.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Maintenance</h2>

        <p class="admin-page-subtitle">
            Create maintenance request with branch, priority, assigned person and repair notes
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.maintenance-requests.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-tools"></i>
                </div>

                <div>
                    <p class="form-card-title">Maintenance Information</p>
                    <p class="form-card-subtitle">Add issue title, category and branch details</p>
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
                               required
                               value="{{ old('title') }}"
                               placeholder="Example: AC not working in classroom"
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
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-code-branch icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            <option value="">Select Branch</option>

                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
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
                    <label class="field-label" for="category">
                        Category
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-tags icon"></i>

                        <input type="text"
                               name="category"
                               id="category"
                               value="{{ old('category') }}"
                               placeholder="Example: Electrical, Furniture, Plumbing"
                               class="field-input {{ $errors->has('category') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('category'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('category') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Maintenance request branch-wise track hoga aur assigned person ko follow-up ke liye use kiya jayega.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-cog"></i>
                </div>

                <div>
                    <p class="form-card-title">Assignment & Status</p>
                    <p class="form-card-subtitle">Set assigned person, priority and current status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="assigned_to_id">
                        Assigned To
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-check icon"></i>

                        <select name="assigned_to_id"
                                id="assigned_to_id"
                                class="field-input {{ $errors->has('assigned_to_id') ? 'error' : '' }}">
                            <option value="">Select User</option>

                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ old('assigned_to_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('assigned_to_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('assigned_to_id') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Optional: assign this request to staff or manager
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="priority">
                        Priority
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-exclamation-triangle icon"></i>

                        <select name="priority"
                                id="priority"
                                class="field-input {{ $errors->has('priority') ? 'error' : '' }}">
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                                Low
                            </option>

                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>
                                Medium
                            </option>

                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                                High
                            </option>

                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                                Urgent
                            </option>
                        </select>
                    </div>

                    @if($errors->has('priority'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('priority') }}
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
                            <option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>
                                Open
                            </option>

                            <option value="assigned" {{ old('status') === 'assigned' ? 'selected' : '' }}>
                                Assigned
                            </option>

                            <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>
                                In Progress
                            </option>

                            <option value="resolved" {{ old('status') === 'resolved' ? 'selected' : '' }}>
                                Resolved
                            </option>

                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>
                                Closed
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

                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <p class="stat-label">Priority</p>
                        <p class="stat-value" id="previewPriority" style="font-size:22px;">Medium</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Status</p>
                        <p class="stat-value" id="previewStatus" style="font-size:22px;">Open</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>

                <div>
                    <p class="form-card-title">Description & Repair Notes</p>
                    <p class="form-card-subtitle">Add detailed issue description and repair updates</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="description">
                        Description
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="description"
                                  id="description"
                                  rows="5"
                                  placeholder="Describe the maintenance issue..."
                                  class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description') }}</textarea>
                    </div>

                    @if($errors->has('description'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('description') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Add location, issue type and required action clearly
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="repair_notes">
                        Repair Notes
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-tools icon"></i>

                        <textarea name="repair_notes"
                                  id="repair_notes"
                                  rows="5"
                                  placeholder="Enter repair notes or solution update..."
                                  class="field-input {{ $errors->has('repair_notes') ? 'error' : '' }}">{{ old('repair_notes') }}</textarea>
                    </div>

                    @if($errors->has('repair_notes'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('repair_notes') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-wrench"></i>
                            Repair notes can be updated after work progress
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

        <a href="{{ route('admin.maintenance-requests.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function formatText(value) {
    if (!value) return '-';

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function updateMaintenancePreview() {
    const priority = document.getElementById('priority');
    const status = document.getElementById('status');

    document.getElementById('previewPriority').innerText = formatText(priority ? priority.value : 'medium');
    document.getElementById('previewStatus').innerText = formatText(status ? status.value : 'open');
}

document.addEventListener('DOMContentLoaded', function () {
    ['priority', 'status'].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateMaintenancePreview);
            el.addEventListener('change', updateMaintenancePreview);
        }
    });

    updateMaintenancePreview();
});
</script>
@endsection