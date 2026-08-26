@extends('layouts.admin')

@section('page-title', 'Show Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $feeStructure->title }} <span class="table-sub-text">v{{ $feeStructure->version_no }}</span></h2>
        <p class="admin-page-subtitle">
            Fee structure details, line items and installment plan
        </p>
    </div>

    @can('fee_structure_edit')
        <a href="{{ route('admin.fee-structures.edit', $feeStructure->id) }}" class="btn-primary">
            <i class="fas fa-pencil-alt"></i>
            Edit Fee Structure
        </a>
    @endcan
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#10B981;">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <p class="profile-title">{{ $feeStructure->title }}</p>
                <p class="profile-subtitle">{{ $feeStructure->course->name ?? '-' }} — {{ $feeStructure->academic_year }}</p>

                @if($feeStructure->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Total Fee</p>
                        <p class="stat-mini-value-sm">₹{{ number_format($feeStructure->total_fee, 2) }}</p>
                    </div>
                    <div class="stat-mini">
                        <p class="stat-mini-label">Students Assigned</p>
                        <p class="stat-mini-value-sm">{{ $feeStructure->ledgers()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad mb-3">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('fee_structure_edit')
                    <a href="{{ route('admin.fee-structures.edit', $feeStructure->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Fee Structure
                    </a>
                @endcan

                <a href="{{ route('admin.fee-structures.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Fee Structures
                </a>
            </div>
        </div>

        @if($versions->count() > 1)
        <div class="detail-card detail-card-pad">
            <p class="quick-title">Version History</p>

            <div class="quick-list">
                @foreach($versions as $version)
                    <a href="{{ route('admin.fee-structures.show', $version->id) }}" class="quick-link {{ $version->id == $feeStructure->id ? 'primary' : '' }}">
                        <i class="fas fa-code-branch"></i>
                        v{{ $version->version_no }} — {{ ucfirst($version->status) }}
                        {{ $version->id == $feeStructure->id ? '(current)' : '' }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Basic Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Title</span><span class="detail-value">{{ $feeStructure->title }}</span></div>
                <div class="detail-row"><span class="detail-label">Branch</span><span class="detail-value">{{ $feeStructure->branch->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Course</span><span class="detail-value">{{ $feeStructure->course->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Batch</span><span class="detail-value">{{ $feeStructure->batch->name ?? 'All Batches' }}</span></div>
                <div class="detail-row"><span class="detail-label">Academic Year</span><span class="detail-value">{{ $feeStructure->academic_year }}</span></div>
                <div class="detail-row"><span class="detail-label">Board / Standard</span><span class="detail-value">{{ $feeStructure->board ?? '-' }} {{ $feeStructure->standard ? '/ ' . $feeStructure->standard : '' }}</span></div>
                <div class="detail-row"><span class="detail-label">Effective</span><span class="detail-value">{{ optional($feeStructure->effective_from)->format('d M Y') }} — {{ optional($feeStructure->effective_to)->format('d M Y') ?? 'ongoing' }}</span></div>
                <div class="detail-row"><span class="detail-label">Version</span><span class="detail-value">v{{ $feeStructure->version_no }}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($feeStructure->status) }}</span></div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-rupee-sign"></i></div>
                <p class="detail-section-title">Fee Line Items</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Fee Head</th>
                            <th>Amount</th>
                            <th>GST</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeStructure->items as $item)
                            <tr>
                                <td>{{ $item->feeHead->name ?? '-' }}</td>
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                                <td>{{ $item->gst_applicable ? number_format($item->gst_percent, 1) . '% (₹' . number_format($item->gst_amount, 2) . ')' : '-' }}</td>
                                <td><strong>₹{{ number_format($item->line_total, 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No fee line items.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
                            <td><strong>₹{{ number_format($feeStructure->total_fee, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-calendar-alt"></i></div>
                <p class="detail-section-title">Installment Plan</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Fee Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeStructure->installmentTemplates as $installment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $installment->title }}</td>
                                <td>
                                    {{ $installment->amount_type == 'percentage' ? number_format($installment->percentage, 1) . '%' : '₹' . number_format($installment->amount, 2) }}
                                </td>
                                <td>{{ optional($installment->due_date)->format('d M Y') ?? 'No fixed due date' }}</td>
                                <td>{{ $installment->feeAccount->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No installment plan — full amount is due as a single payment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('student_fee_ledger_create')
        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-user-plus"></i></div>
                <p class="detail-section-title">Assign to Students</p>
            </div>

            <div class="detail-section-body">
                @if($unassignedStudents->isEmpty())
                    <p class="table-sub-text">All matching students are already assigned to this fee structure.</p>
                @else
                    <form method="POST" action="{{ route('admin.fee-structures.assign', $feeStructure->id) }}">
                        @csrf

                        <div class="field-group">
                            <label class="field-label">Select Students</label>
                            <select name="student_ids[]" multiple size="8" class="field-input">
                                @foreach($unassignedStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->name ?? $student->student_code ?? 'Student #' . $student->id }}</option>
                                @endforeach
                            </select>
                            <p class="field-hint">Hold Ctrl/Cmd to select multiple students, or select all.</p>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i>
                            Assign Selected Students
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endcan
    </div>

</div>

@endsection
