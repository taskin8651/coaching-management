@extends('layouts.admin')

@section('page-title', 'Edit Admission')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.admissions.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Admission</h2>
        <p class="admin-page-subtitle">
            Update admission record
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.admissions.update', $admission->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-check"></i>
                </div>

                <div>
                    <p class="form-card-title">Admission Details</p>
                    <p class="form-card-subtitle">Basic admission information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Student <span class="req">*</span></label>
                    <select name="student_id" class="field-input" required>
                        @foreach($students as $id => $student)
                            <option value="{{ $id }}" {{ old('student_id', $admission->student_id) == $id ? 'selected' : '' }}>{{ $student }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Enquiry</label>
                    <select name="enquiry_id" class="field-input">
                        @foreach($enquiries as $id => $enquiry)
                            <option value="{{ $id }}" {{ old('enquiry_id', $admission->enquiry_id) == $id ? 'selected' : '' }}>{{ $enquiry }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Admission Date</label>
                    <input type="date"
                           name="admission_date"
                           value="{{ old('admission_date', $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('Y-m-d') : '') }}"
                           class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Admission Source</label>
                    <select name="admission_source" class="field-input">
                        @foreach($sources as $key => $source)
                            <option value="{{ $key }}" {{ old('admission_source', $admission->admission_source) == $key ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" {{ old('status', $admission->status) == $key ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-school"></i>
                </div>

                <div>
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Branch, course and batch</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Branch <span class="req">*</span></label>
                    <select name="branch_id" class="field-input" required>
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id', $admission->branch_id) == $id ? 'selected' : '' }}>{{ $branch }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Course</label>
                    <select name="course_id" class="field-input">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ old('course_id', $admission->course_id) == $id ? 'selected' : '' }}>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Batch</label>
                    <select name="batch_id" class="field-input">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ old('batch_id', $admission->batch_id) == $id ? 'selected' : '' }}>{{ $batch }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-users"></i>
                </div>

                <div>
                    <p class="form-card-title">Guardian Details</p>
                    <p class="form-card-subtitle">Parent and emergency contact</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Father Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $admission->father_name) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Mother Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name', $admission->mother_name) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Guardian Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $admission->guardian_name) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Guardian Relation</label>
                    <input type="text" name="guardian_relation" value="{{ old('guardian_relation', $admission->guardian_relation) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Guardian Phone</label>
                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $admission->guardian_phone) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Parent Email</label>
                    <input type="email" name="parent_email" value="{{ old('parent_email', $admission->parent_email) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $admission->emergency_contact) }}" class="field-input">
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Fee Snapshot</p>
                    <p class="form-card-subtitle">Admission time fee details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label">Course Fee</label>
                    <input type="number" step="0.01" name="course_fee" value="{{ old('course_fee', $admission->course_fee) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Admission Fee</label>
                    <input type="number" step="0.01" name="admission_fee" value="{{ old('admission_fee', $admission->admission_fee) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Discount</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', $admission->discount) }}" class="field-input">
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Current payable: ₹{{ number_format($admission->payable_amount, 2) }}
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Previous Education & Documents</p>
                    <p class="form-card-subtitle">Upload admission documents</p>
                </div>
            </div>

            <div class="form-card-body">

                @if($admission->documents && count($admission->documents))
                    <div class="form-info-box mb-3">
                        <p class="mb-2">
                            <i class="fas fa-file"></i>
                            Uploaded Documents
                        </p>

                        @foreach($admission->documents as $document)
                            <p>
                                <a href="{{ $document['url'] }}" target="_blank">{{ $document['name'] }}</a>
                            </p>
                        @endforeach
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label">Previous School</label>
                            <input type="text" name="previous_school" value="{{ old('previous_school', $admission->previous_school) }}" class="field-input">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label">Previous Class</label>
                            <input type="text" name="previous_class" value="{{ old('previous_class', $admission->previous_class) }}" class="field-input">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label">Qualification</label>
                            <input type="text" name="qualification" value="{{ old('qualification', $admission->qualification) }}" class="field-input">
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Add More Documents</label>
                    <input type="file" name="documents[]" multiple class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Remarks</label>
                    <textarea name="remarks" rows="4" class="field-input">{{ old('remarks', $admission->remarks) }}</textarea>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.admissions.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection