@php
    $selectedBatchId = $selectedBatchId ?? '';
    $selectedStatus = $selectedStatus ?? 'active';
    $oldAssignments = $oldAssignments ?? [];
@endphp

<div class="page-card p-4">
    <div class="assignment-toolbar">
        <div class="d-flex flex-wrap gap-3 align-items-end">
            <div class="field-group">
                <label class="field-label" for="batch_id">Batch <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-users icon"></i>
                    <select name="batch_id"
                            id="batch_id"
                            required
                            class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                        @foreach($batches as $id => $name)
                            <option value="{{ $id }}" {{ (string) $selectedBatchId === (string) $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('batch_id'))
                    <p class="field-error">{{ $errors->first('batch_id') }}</p>
                @endif
            </div>

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

        <div class="matrix-stats">
            <div class="matrix-stat">Students: <span id="studentTotal">0</span></div>
            <div class="matrix-stat">Subjects: <span id="subjectTotal">0</span></div>
        </div>
    </div>

    @if($errors->has('assignments') || $errors->has('assignments.*') || $errors->has('assignments.*.*'))
        <div class="alert-error mb-3">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first('assignments') ?: $errors->first('assignments.*') ?: $errors->first('assignments.*.*') }}
        </div>
    @endif

    <div class="matrix-card">
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
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchSelect = document.getElementById('batch_id');
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
    const studentTotal = document.getElementById('studentTotal');
    const subjectTotal = document.getElementById('subjectTotal');
    const hiddenInputs = document.getElementById('assignmentHiddenInputs');
    const form = document.getElementById('assignmentForm');
    const matrixUrl = @json(route('admin.student-batches.matrix'));
    const oldAssignments = @json($oldAssignments);

    let students = [];
    let subjects = [];
    let assignmentState = {};
    let currentPage = 1;
    const perPage = 25;

    function normalizeAssignments(raw) {
        const normalized = {};

        Object.entries(raw || {}).forEach(([studentId, subjectIds]) => {
            normalized[String(studentId)] = new Set((subjectIds || []).map(String));
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

    function isChecked(studentId, subjectId) {
        return assignmentState[String(studentId)]?.has(String(subjectId)) || false;
    }

    function setChecked(studentId, subjectId, checked) {
        const sId = String(studentId);
        const subId = String(subjectId);

        if (!assignmentState[sId]) {
            assignmentState[sId] = new Set();
        }

        if (checked) {
            assignmentState[sId].add(subId);
        } else {
            assignmentState[sId].delete(subId);
        }
    }

    function renderHeader() {
        const tr = document.createElement('tr');
        const studentTh = document.createElement('th');
        studentTh.className = 'student-col';
        studentTh.textContent = 'Student';
        tr.appendChild(studentTh);

        subjects.forEach(subject => {
            const th = document.createElement('th');
            const wrap = document.createElement('div');
            wrap.className = 'subject-head';

            const title = document.createElement('span');
            title.textContent = subject.name;

            const label = document.createElement('label');
            label.className = 'd-flex align-items-center gap-1 mb-0';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'matrix-check select-all-subject';
            input.dataset.subjectId = subject.id;
            input.checked = students.length > 0 && students.every(student => isChecked(student.id, subject.id));

            const small = document.createElement('small');
            small.textContent = '';

            label.appendChild(input);
            label.appendChild(small);
            wrap.appendChild(title);
            wrap.appendChild(label);
            th.appendChild(wrap);
            tr.appendChild(th);
        });

        head.innerHTML = '';
        head.appendChild(tr);
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

            subjects.forEach(subject => {
                const td = document.createElement('td');
                td.className = 'text-center';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'matrix-check assignment-checkbox';
                checkbox.dataset.studentId = student.id;
                checkbox.dataset.subjectId = subject.id;
                checkbox.checked = isChecked(student.id, subject.id);
                td.appendChild(checkbox);
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
        studentTotal.textContent = students.length;
        subjectTotal.textContent = subjects.length;

        if (!students.length || !subjects.length) {
            tableWrap.classList.add('d-none');
            pagination.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.innerHTML = '<i class="fas fa-info-circle fa-2x mb-2"></i><p class="mb-0">Selected batch ke liye students ya subjects nahi mile.</p>';
            return;
        }

        empty.classList.add('d-none');
        tableWrap.classList.remove('d-none');
        renderHeader();
        renderBody();
        renderPagination();
    }

    async function loadMatrix() {
        const batchId = batchSelect.value;

        students = [];
        subjects = [];
        assignmentState = {};
        currentPage = 1;
        studentTotal.textContent = '0';
        subjectTotal.textContent = '0';

        if (!batchId) {
            tableWrap.classList.add('d-none');
            pagination.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.innerHTML = '';
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(`${matrixUrl}?batch_id=${encodeURIComponent(batchId)}`, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            });

            if (!response.ok) {
                throw new Error('Unable to load matrix.');
            }

            const data = await response.json();
            students = data.students || [];
            subjects = data.subjects || [];
            assignmentState = Object.keys(oldAssignments || {}).length
                ? normalizeAssignments(oldAssignments)
                : normalizeAssignments(data.assignments || {});
        } catch (error) {
            empty.classList.remove('d-none');
            empty.innerHTML = '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p class="mb-0">Matrix load nahi ho paya. Please batch dobara select karein.</p>';
        } finally {
            setLoading(false);
            renderMatrix();
        }
    }

    body.addEventListener('change', function (event) {
        if (!event.target.classList.contains('assignment-checkbox')) return;

        setChecked(event.target.dataset.studentId, event.target.dataset.subjectId, event.target.checked);
        renderHeader();
    });

    head.addEventListener('change', function (event) {
        if (!event.target.classList.contains('select-all-subject')) return;

        const subjectId = event.target.dataset.subjectId;
        const checked = event.target.checked;

        students.forEach(student => setChecked(student.id, subjectId, checked));
        renderBody();
        renderHeader();
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

    batchSelect.addEventListener('change', loadMatrix);

    form.addEventListener('submit', function () {
        hiddenInputs.innerHTML = '';

        Object.entries(assignmentState).forEach(([studentId, subjectSet]) => {
            subjectSet.forEach(subjectId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `assignments[${studentId}][]`;
                input.value = subjectId;
                hiddenInputs.appendChild(input);
            });
        });
    });

    loadMatrix();
});
</script>
@endsection
