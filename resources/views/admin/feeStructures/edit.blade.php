@extends('layouts.admin')

@section('page-title', 'Edit Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Fee Structure</h2>
        <p class="admin-page-subtitle">
            Update fee structure details
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-structures.update', $feeStructure->id) }}">
    @csrf
    @method('PUT')

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
                           value="{{ old('title', $feeStructure->title) }}"
                           required
                           class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Branch <span class="req">*</span></label>
                    <select name="branch_id" required class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id', $feeStructure->branch_id) == $id ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Course <span class="req">*</span></label>
                    <select name="course_id" required class="field-input">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ old('course_id', $feeStructure->course_id) == $id ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Batch</label>
                    <select name="batch_id" class="field-input">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ old('batch_id', $feeStructure->batch_id) == $id ? 'selected' : '' }}>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                    <p class="field-hint">Empty means applicable for all batches of selected course.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="active" {{ old('status', $feeStructure->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $feeStructure->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <input type="number" step="0.01" name="admission_fee" value="{{ old('admission_fee', $feeStructure->admission_fee) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Tuition Fee</label>
                    <input type="number" step="0.01" name="tuition_fee" value="{{ old('tuition_fee', $feeStructure->tuition_fee) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Exam Fee</label>
                    <input type="number" step="0.01" name="exam_fee" value="{{ old('exam_fee', $feeStructure->exam_fee) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Material Fee</label>
                    <input type="number" step="0.01" name="material_fee" value="{{ old('material_fee', $feeStructure->material_fee) }}" class="field-input fee-calc">
                </div>

                <div class="field-group">
                    <label class="field-label">Other Fee</label>
                    <input type="number" step="0.01" name="other_fee" value="{{ old('other_fee', $feeStructure->other_fee) }}" class="field-input fee-calc">
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-calculator"></i>
                        Total Fee:
                        <strong id="totalFeePreview">₹{{ number_format($feeStructure->total_fee, 2) }}</strong>
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
                    <textarea name="description" rows="4" class="field-input">{{ old('description', $feeStructure->description) }}</textarea>
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
});
</script>

@endsection