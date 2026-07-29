@php
    $selectedBatchIds = collect($selectedBatchIds ?? [])->map(fn ($id) => (string) $id)->all();
    $selectedStatus = $selectedStatus ?? 'active';
    $oldAssignments = $oldAssignments ?? [];
    $managedStudentId = $managedStudentId ?? null;
    $selectedCourseId = old('course_id', $selectedCourseId ?? '');
@endphp

@if($managedStudentId)
    <input type="hidden" name="only_student_id" value="{{ $managedStudentId }}">
@endif

@section('styles')
<style>
.matrix-loader{display:none;padding:48px;text-align:center;color:#64748B;font-weight:600;font-size:14px}
.matrix-loader.active{display:block}
.matrix-loader i{color:var(--accent);margin-right:8px}

.empty-matrix{padding:48px 20px;text-align:center;color:#64748B;font-size:14px}
.empty-matrix i{font-size:38px;color:#CBD5E1;margin-bottom:12px;display:block}

.matrix-scroll{max-height:70vh;overflow:auto;background:#fff;border-top:1px solid #F1F5F9}
.matrix-scroll::-webkit-scrollbar{width:9px;height:9px}
.matrix-scroll::-webkit-scrollbar-thumb{background:#CBD5E1;border-radius:20px}
.matrix-scroll::-webkit-scrollbar-thumb:hover{background:#94A3B8}

.assignment-matrix{margin:0;min-width:960px;border-collapse:separate;border-spacing:0;background:#fff}

.assignment-matrix th,
.assignment-matrix td{
    padding:12px 14px!important;
    border:1px solid #EEF2F7!important;
    vertical-align:middle;
}

.assignment-matrix thead th{
    position:sticky;
    z-index:30;
    background:var(--accent);
    color:#fff;
    text-align:center;
    font-size:12.5px;
    font-weight:700;
    letter-spacing:.02em;
    white-space:nowrap;
}

.assignment-matrix thead tr:first-child th{
    top:0;
    background:var(--accent-dark);
}

.assignment-matrix thead tr:last-child th{
    top:41px;
}

.assignment-matrix tbody tr:nth-child(even){background:#FAFBFF}
.assignment-matrix tbody tr:hover td{background:#F1F6FC}

.assignment-matrix .student-col{
    position:sticky;
    left:0;
    z-index:20;
    background:#fff;
    min-width:240px;
    text-align:left;
    box-shadow:6px 0 16px rgba(15,23,42,.04);
}

.assignment-matrix tbody tr:nth-child(even) .student-col{background:#FAFBFF}

.assignment-matrix thead .student-col{
    background:var(--accent-dark);
    color:#fff;
    z-index:40;
}

.student-name{font-size:13.5px;font-weight:700;color:#0F172A;margin-bottom:2px}
.student-code{font-size:11.5px;color:#94A3B8}

.subject-head{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:6px;
    min-width:110px;
}

.subject-head span{font-weight:700;font-size:12.5px}

.matrix-check{
    width:18px;
    height:18px;
    cursor:pointer;
    accent-color:var(--accent);
}

.assignment-matrix thead .matrix-check{accent-color:#fff}

.assignment-matrix td{text-align:center}
.assignment-matrix td.student-col{text-align:left}
.assignment-matrix td.cell-disabled{color:#CBD5E1;font-weight:700}

.pagination-wrap{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    padding:14px 18px;
    border-top:1px solid #E2E8F0;
    background:#fff;
}

.page-info{font-size:13px;font-weight:600;color:#475569}

.pagination-wrap .btn{border-radius:9px;font-weight:700}

@media(max-width:992px){
    .assignment-matrix{min-width:820px}
}

@media(max-width:768px){
    .matrix-scroll{max-height:62vh}
    .student-col{min-width:190px!important}
    .subject-head{min-width:88px}
    .assignment-matrix th,.assignment-matrix td{padding:9px!important}
}
</style>
@endsection

<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon">
            <i class="fas fa-layer-group"></i>
        </div>

        <div>
            <p class="form-card-title">Select Course &amp; Batches</p>
            <p class="form-card-subtitle">Course choose karein, phir uske batches se assignment matrix banayein</p>
        </div>
    </div>

    <div class="form-card-body">
        <div class="field-group">
            <label class="field-label" for="matrix_course_id">
                Course <span class="req">*</span>
            </label>

            <div class="input-icon-wrap">
                <i class="fas fa-book icon"></i>

                <select name="course_id"
                        id="matrix_course_id"
                        class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                    @foreach($courses as $id => $name)
                        <option value="{{ $id }}" {{ (string) $selectedCourseId === (string) $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($errors->has('course_id'))
                <p class="field-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('course_id') }}
                </p>
            @else
                <p class="field-hint">Course select karne par uske batches niche dikhenge.</p>
            @endif
        </div>

        <div class="field-group">
            <label class="field-label">
                Batches <span class="req">*</span>
            </label>

            <div class="checkbox-grid" id="batch-checkbox-grid">
                <div class="form-info-box">
                    <p><i class="fas fa-info-circle"></i> Pehle course select karein.</p>
                </div>
            </div>

            @if($errors->has('batch_ids'))
                <p class="field-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('batch_ids') }}
                </p>
            @else
                <p class="field-error d-none" id="batchSelectError">
                    <i class="fas fa-exclamation-circle"></i>
                    Please select at least one batch.
                </p>
                <p class="field-hint">Ek course ke multiple batches select karke student ko un sabme assign kar sakte hain.</p>
            @endif
        </div>

        <div class="admin-form-grid">
            <div class="field-group">
                <label class="field-label" for="status">Status</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-toggle-on icon"></i>
                    <select name="status"
                            id="status"
                            class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                        <option value="active" {{ $selectedStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $selectedStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                @if($errors->has('status'))
                    <p class="field-error">{{ $errors->first('status') }}</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="studentSearch">Search Student</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-search icon"></i>
                    <input type="text"
                           id="studentSearch"
                           class="field-input"
                           placeholder="Search by name or code">
                </div>
            </div>
        </div>

        <div class="stats-grid mb-0">
            <div class="stat-card">
                <p class="stat-label">Batches</p>
                <p class="stat-value" id="batchTotal">0</p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Students</p>
                <p class="stat-value" id="studentTotal">0</p>
            </div>

            <div class="stat-card">
                <p class="stat-label">Subjects</p>
                <p class="stat-value" id="subjectTotal">0</p>
            </div>
        </div>
    </div>
</div>

@if($errors->has('assignments') || $errors->has('assignments.*') || $errors->has('assignments.*.*') || $errors->has('assignments.*.*.*'))
    <div class="alert-error mt-4">
        <i class="fas fa-exclamation-circle"></i>
        {{ $errors->first('assignments') ?: ($errors->first('assignments.*') ?: ($errors->first('assignments.*.*') ?: $errors->first('assignments.*.*.*'))) }}
    </div>
@endif

<div class="page-card mt-4">
    <div class="page-card-header">
        <div>
            <p class="page-card-title">Assignment Matrix</p>
            <p class="page-card-note">Tick a subject to assign it to a student for that batch</p>
        </div>
    </div>

    <div id="matrixLoader" class="matrix-loader">
        <i class="fas fa-spinner fa-spin"></i>
        Loading students and subjects...
    </div>

    <div id="matrixEmpty" class="empty-matrix">

    </div>

    <div id="matrixTableWrap" class="matrix-scroll d-none">
        <table class="table table-bordered table-hover assignment-matrix">
            <thead id="matrixHead"></thead>
            <tbody id="matrixBody"></tbody>
        </table>
    </div>

    <div id="matrixPagination" class="pagination-wrap d-none">
        <button type="button" class="btn btn-sm btn-outline-primary" id="prevPage">
            <i class="fas fa-chevron-left"></i> Prev
        </button>
        <span class="page-info" id="pageInfo">Page 1</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="nextPage">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<div id="assignmentHiddenInputs"></div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchGrid = document.getElementById('batch-checkbox-grid');
    const courseSelect = document.getElementById('matrix_course_id');
    const batchSelectError = document.getElementById('batchSelectError');
    const searchInput = document.getElementById('studentSearch');
    const loader = document.getElementById('matrixLoader');
    const empty = document.getElementById('matrixEmpty');
    const tableWrap = document.getElementById('matrixTableWrap');
    const pagination = document.getElementById('matrixPagination');
    const head = document.getElementById('matrixHead');
    const body = document.getElementById('matrixBody');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    const batchTotal = document.getElementById('batchTotal');
    const studentTotal = document.getElementById('studentTotal');
    const subjectTotal = document.getElementById('subjectTotal');
    const hiddenInputs = document.getElementById('assignmentHiddenInputs');
    const form = document.getElementById('assignmentForm');
    const matrixUrl = @json(route('admin.student-batches.matrix'));
    const oldAssignments = @json($oldAssignments);
    const managedStudentId = @json($managedStudentId);

    let batches = [];
    let students = [];
    let columns = [];
    let eligibleSets = {};
    let assignmentState = {};
    let currentPage = 1;
    const perPage = 25;

    function selectedBatchIds() {
        return Array.from(batchGrid.querySelectorAll('input.batch-checkbox:checked')).map(input => input.value);
    }

    function normalizeAssignments(raw) {
        const normalized = {};

        Object.entries(raw || {}).forEach(([studentId, byBatch]) => {
            normalized[String(studentId)] = {};

            Object.entries(byBatch || {}).forEach(([batchId, subjectIds]) => {
                normalized[String(studentId)][String(batchId)] = new Set((subjectIds || []).map(String));
            });
        });

        return normalized;
    }

    function setLoading(active) {
        loader.classList.toggle('active', active);
        if (active) {
            empty.classList.add('d-none');
            tableWrap.classList.add('d-none');
            pagination.classList.add('d-none');
        }
    }

    function filteredStudents() {
        const term = (searchInput.value || '').trim().toLowerCase();

        if (!term) return students;

        return students.filter(student => {
            return String(student.name || '').toLowerCase().includes(term)
                || String(student.code || '').toLowerCase().includes(term);
        });
    }

    function paginatedStudents() {
        const list = filteredStudents();
        const start = (currentPage - 1) * perPage;
        return list.slice(start, start + perPage);
    }

    function totalPages() {
        return Math.max(1, Math.ceil(filteredStudents().length / perPage));
    }

    function ensurePage() {
        currentPage = Math.min(Math.max(currentPage, 1), totalPages());
    }

    function isEligible(studentId, batchId) {
        return eligibleSets[String(batchId)]?.has(String(studentId)) || false;
    }

    function isChecked(studentId, batchId, subjectId) {
        return assignmentState[String(studentId)]?.[String(batchId)]?.has(String(subjectId)) || false;
    }

    function setChecked(studentId, batchId, subjectId, checked) {
        const sId = String(studentId);
        const bId = String(batchId);
        const subId = String(subjectId);

        if (!assignmentState[sId]) {
            assignmentState[sId] = {};
        }

        if (!assignmentState[sId][bId]) {
            assignmentState[sId][bId] = new Set();
        }

        if (checked) {
            assignmentState[sId][bId].add(subId);
        } else {
            assignmentState[sId][bId].delete(subId);
        }
    }

    function renderHeader() {
        const batchRow = document.createElement('tr');
        const studentTh = document.createElement('th');
        studentTh.className = 'student-col';
        studentTh.textContent = 'Student';
        studentTh.rowSpan = 2;
        batchRow.appendChild(studentTh);

        batches.forEach(batch => {
            const th = document.createElement('th');
            th.colSpan = Math.max(batch.subjects.length, 1);
            th.textContent = batch.name;
            batchRow.appendChild(th);
        });

        const subjectRow = document.createElement('tr');

        columns.forEach(column => {
            const th = document.createElement('th');
            const wrap = document.createElement('div');
            wrap.className = 'subject-head';

            const title = document.createElement('span');
            title.textContent = column.subjectName;

            const label = document.createElement('label');
            label.className = 'd-flex align-items-center gap-1 mb-0';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'matrix-check select-all-subject';
            input.dataset.batchId = column.batchId;
            input.dataset.subjectId = column.subjectId;

            const eligibleStudents = students.filter(student => isEligible(student.id, column.batchId));
            input.checked = eligibleStudents.length > 0 && eligibleStudents.every(student => isChecked(student.id, column.batchId, column.subjectId));
            input.disabled = eligibleStudents.length === 0;

            label.appendChild(input);
            wrap.appendChild(title);
            wrap.appendChild(label);
            th.appendChild(wrap);
            subjectRow.appendChild(th);
        });

        head.innerHTML = '';
        head.appendChild(batchRow);
        head.appendChild(subjectRow);
    }

    function renderBody() {
        body.innerHTML = '';

        paginatedStudents().forEach(student => {
            const tr = document.createElement('tr');

            const nameTd = document.createElement('td');
            nameTd.className = 'student-col';
            const name = document.createElement('div');
            name.className = 'student-name';
            name.textContent = student.name || ('Student #' + student.id);
            const code = document.createElement('div');
            code.className = 'student-code';
            code.textContent = student.code ? ('Code: ' + student.code) : ('ID #' + student.id);
            nameTd.appendChild(name);
            nameTd.appendChild(code);
            tr.appendChild(nameTd);

            columns.forEach(column => {
                const td = document.createElement('td');

                if (isEligible(student.id, column.batchId)) {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'matrix-check assignment-checkbox';
                    checkbox.dataset.studentId = student.id;
                    checkbox.dataset.batchId = column.batchId;
                    checkbox.dataset.subjectId = column.subjectId;
                    checkbox.checked = isChecked(student.id, column.batchId, column.subjectId);
                    td.appendChild(checkbox);
                } else {
                    td.classList.add('cell-disabled');
                    td.textContent = '—';
                }

                tr.appendChild(td);
            });

            body.appendChild(tr);
        });
    }

    function renderPagination() {
        ensurePage();
        const total = totalPages();
        pageInfo.textContent = `Page ${currentPage} of ${total} · Showing ${filteredStudents().length} student(s)`;
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= total;
        pagination.classList.toggle('d-none', students.length === 0);
    }

    function renderMatrix() {
        batchTotal.textContent = batches.length;
        studentTotal.textContent = students.length;
        subjectTotal.textContent = columns.length;

        if (!students.length || !columns.length) {
            tableWrap.classList.add('d-none');
            pagination.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.innerHTML = '<i class="fas fa-info-circle"></i><p class="mb-0">Selected batches ke liye students ya subjects nahi mile.</p>';
            return;
        }

        empty.classList.add('d-none');
        tableWrap.classList.remove('d-none');
        renderHeader();
        renderBody();
        renderPagination();
    }

    async function loadMatrix() {
        const batchIds = selectedBatchIds();

        batches = [];
        students = [];
        columns = [];
        eligibleSets = {};
        assignmentState = {};
        currentPage = 1;
        batchTotal.textContent = '0';
        studentTotal.textContent = '0';
        subjectTotal.textContent = '0';

        if (!batchIds.length) {
            tableWrap.classList.add('d-none');
            pagination.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.innerHTML = '';
            return;
        }

        setLoading(true);

        try {
            let params = batchIds.map(id => `batch_ids[]=${encodeURIComponent(id)}`).join('&');

            if (managedStudentId) {
                params += `&student_id=${encodeURIComponent(managedStudentId)}`;
            }

            const response = await fetch(`${matrixUrl}?${params}`, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            });

            if (!response.ok) {
                throw new Error('Unable to load matrix.');
            }

            const data = await response.json();
            batches = data.batches || [];
            students = data.students || [];

            batches.forEach(batch => {
                eligibleSets[String(batch.id)] = new Set((batch.student_ids || []).map(String));

                (batch.subjects || []).forEach(subject => {
                    columns.push({
                        batchId: batch.id,
                        batchName: batch.name,
                        subjectId: subject.id,
                        subjectName: subject.name,
                    });
                });
            });

            assignmentState = Object.keys(oldAssignments || {}).length
                ? normalizeAssignments(oldAssignments)
                : normalizeAssignments(data.assignments || {});
        } catch (error) {
            empty.classList.remove('d-none');
            empty.innerHTML = '<i class="fas fa-exclamation-triangle"></i><p class="mb-0">Matrix load nahi ho paya. Please batches dobara select karein.</p>';
        } finally {
            setLoading(false);
            renderMatrix();
        }
    }

    body.addEventListener('change', function (event) {
        if (!event.target.classList.contains('assignment-checkbox')) return;

        setChecked(event.target.dataset.studentId, event.target.dataset.batchId, event.target.dataset.subjectId, event.target.checked);
        renderHeader();
    });

    head.addEventListener('change', function (event) {
        if (!event.target.classList.contains('select-all-subject')) return;

        const batchId = event.target.dataset.batchId;
        const subjectId = event.target.dataset.subjectId;
        const checked = event.target.checked;

        students.forEach(student => {
            if (isEligible(student.id, batchId)) {
                setChecked(student.id, batchId, subjectId, checked);
            }
        });

        renderBody();
        renderHeader();
    });

    batchGrid.addEventListener('change', function (event) {
        if (!event.target.classList.contains('batch-checkbox')) return;

        event.target.closest('.role-checkbox-item')?.classList.toggle('checked', event.target.checked);

        if (event.target.checked) {
            batchSelectError.classList.add('d-none');
        }

        loadMatrix();
    });

    searchInput.addEventListener('input', function () {
        currentPage = 1;
        renderBody();
        renderPagination();
    });

    prevBtn.addEventListener('click', function () {
        currentPage--;
        ensurePage();
        renderBody();
        renderPagination();
    });

    nextBtn.addEventListener('click', function () {
        currentPage++;
        ensurePage();
        renderBody();
        renderPagination();
    });

    form.addEventListener('submit', function (event) {
        if (!selectedBatchIds().length) {
            event.preventDefault();
            batchSelectError.classList.remove('d-none');
            batchGrid.scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }

        hiddenInputs.innerHTML = '';

        Object.entries(assignmentState).forEach(([studentId, byBatch]) => {
            Object.entries(byBatch).forEach(([batchId, subjectSet]) => {
                subjectSet.forEach(subjectId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `assignments[${studentId}][${batchId}][]`;
                    input.value = subjectId;
                    hiddenInputs.appendChild(input);
                });
            });
        });
    });

    const batchesByCourse = @json($batchesByCourse);

    cascadeCheckboxGridByParent(batchGrid, courseSelect, batchesByCourse, {
        name: 'batch_ids[]',
        className: 'batch-checkbox',
        keepValue: @json($selectedBatchIds),
        emptyHtml: '<div class="form-info-box"><p><i class="fas fa-info-circle"></i> Is course ke liye koi active batch nahi mila.</p></div>',
        onRender: function () {
            loadMatrix();
        },
    });
});
</script>
@endsection
