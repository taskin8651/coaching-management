@extends('layouts.admin')

@section('page-title', 'Add Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Fee Structure</h2>
        <p class="admin-page-subtitle">
            Define course and batch wise fee structure
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-structures.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-list-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Basic Details</p>
                    <p class="form-card-subtitle">Branch, course and batch mapping</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Title <span class="req">*</span></label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           placeholder="Example: Class 10 Annual Fee"
                           required
                           class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                </div>

                <div class="field-group">
                    <label class="field-label">Branch <span class="req">*</span></label>
                    <select name="branch_id" id="branch_id" required class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Course <span class="req">*</span></label>
                    <select name="course_id" id="course_id" required class="field-input">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="field-input">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                    <p class="field-hint">Empty means applicable for all batches of selected course.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Fee Details</p>
                    <p class="form-card-subtitle">Fee breakup and total calculation</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Admission Fee</label>
                    <input type="number" step="0.01" name="admission_fee" value="{{ old('admission_fee', 0) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Tuition Fee</label>
                    <input type="number" step="0.01" name="tuition_fee" value="{{ old('tuition_fee', 0) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Exam Fee</label>
                    <input type="number" step="0.01" name="exam_fee" value="{{ old('exam_fee', 0) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Material Fee</label>
                    <input type="number" step="0.01" name="material_fee" value="{{ old('material_fee', 0) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Other Fee</label>
                    <input type="number" step="0.01" name="other_fee" value="{{ old('other_fee', 0) }}" class="field-input fee-calc">
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-calculator"></i>
                        Total Fee:
                        <strong id="totalFeePreview">₹0.00</strong>
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-align-left"></i>
                </div>

                <div>
                    <p class="form-card-title">Description</p>
                    <p class="form-card-subtitle">Optional notes for this fee structure</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Description</label>
                    <textarea name="description" rows="4" class="field-input">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.fee-structures.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>
</form>

<script>
function calculateTotalFee() {
    let total = 0;

    document.querySelectorAll('.fee-calc').forEach(input => {
        total += parseFloat(input.value || 0);
    });

    document.getElementById('totalFeePreview').innerText = '₹' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {
    calculateTotalFee();

    document.querySelectorAll('.fee-calc').forEach(input => {
        input.addEventListener('input', calculateTotalFee);
    });

    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const coursesByBranch = @json($coursesByBranch);
    const batchesByBranchCourse = @json($batchesByBranchCourse);

    cascadeByParent(courseSelect, branchSelect, coursesByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('course_id')),
    });

    cascadeByBranchCourse(batchSelect, branchSelect, courseSelect, batchesByBranchCourse, {
        placeholder: 'All Batches / Optional',
        keepValue: @json(old('batch_id')),
    });
});
</script>

@endsection