function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;

    const icon = btn.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

function initPasswordStrength() {
    const password = document.getElementById('password');
    const text = document.getElementById('strength-text');
    const bars = document.querySelectorAll('.strength-bar');

    if (!password || !text || !bars.length) return;

    password.addEventListener('input', function () {
        const val = this.value;
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ['#EF4444', '#F59E0B', '#10B981', '#0855A1'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];

        bars.forEach((bar, index) => {
            bar.style.background = index < score ? colors[score - 1] : '#E2E8F0';
        });

        if (val.length === 0) {
            text.textContent = '';
            text.style.color = '#94A3B8';
        } else {
            text.textContent = labels[score - 1] || 'Weak';
            text.style.color = colors[score - 1] || '#EF4444';
        }
    });
}
function initAdminCheckboxes() {
    document.querySelectorAll('.role-checkbox-item, .admin-checkbox-item').forEach(item => {
        const checkbox = item.querySelector('input[type=checkbox]');

        if (!checkbox) return;

        const syncState = () => {
            item.classList.toggle('checked', checkbox.checked);
        };

        syncState();

        checkbox.addEventListener('change', syncState);

        item.addEventListener('click', function () {
            setTimeout(syncState, 0);
        });
    });

    document.querySelectorAll('[data-check-all]').forEach(button => {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-check-all') || '.role-checkbox-item';

            document.querySelectorAll(target).forEach(item => {
                const checkbox = item.querySelector('input[type=checkbox]');

                if (!checkbox) return;

                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            });
        });
    });

    document.querySelectorAll('[data-uncheck-all]').forEach(button => {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-uncheck-all') || '.role-checkbox-item';

            document.querySelectorAll(target).forEach(item => {
                const checkbox = item.querySelector('input[type=checkbox]');

                if (!checkbox) return;

                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof initPasswordStrength === 'function') {
        initPasswordStrength();
    }

    initAdminCheckboxes();
});

/**
 * Repopulates `childSelect` whenever `parentSelect` changes, using `dataMap[parentSelect.value]`
 * as the flat list of {id, name} options. Used for one-level cascades like Branch -> Course.
 * Returns the render function so callers can trigger it manually if needed.
 */
function cascadeByParent(childSelect, parentSelect, dataMap, options = {}) {
    if (!childSelect || !parentSelect) return function () {};

    const placeholder = options.placeholder ?? 'Select';
    let keepValue = options.keepValue ?? '';

    function render() {
        const parentId = parentSelect.value;
        const items = (parentId && dataMap[parentId]) ? dataMap[parentId] : [];
        const keep = keepValue;
        keepValue = '';

        childSelect.innerHTML = '';
        childSelect.appendChild(new Option(placeholder, ''));

        items.forEach(function (item) {
            const option = new Option(item.name, item.id);
            option.selected = keep !== '' && String(item.id) === String(keep);
            childSelect.appendChild(option);
        });

        if (typeof options.onRender === 'function') options.onRender();
    }

    parentSelect.addEventListener('change', render);
    render();

    return render;
}

/**
 * Repopulates `childSelect` whenever `branchSelect` (and optionally `courseSelect`) change, using
 * `dataMap[branchId]` — a bucket keyed by course_id plus an 'all' bucket for course-independent
 * items (e.g. a subject with no specific course, or a batch open to the whole branch). The 'all'
 * bucket is always merged in alongside whichever course is currently selected. Used for two-level
 * cascades like Branch+Course -> Batch or Branch+Course -> Subject.
 */
function cascadeByBranchCourse(childSelect, branchSelect, courseSelect, dataMap, options = {}) {
    if (!childSelect || !branchSelect) return function () {};

    const placeholder = options.placeholder ?? 'Select';
    let keepValue = options.keepValue ?? (options.multiple ? [] : '');

    function items() {
        const branchId = branchSelect.value;
        const courseId = courseSelect ? courseSelect.value : '';
        const branchBucket = (branchId && dataMap[branchId]) ? dataMap[branchId] : {};
        const common = branchBucket.all || [];
        const specific = (courseId && branchBucket[courseId]) ? branchBucket[courseId] : [];
        const seen = new Set();

        return [...common, ...specific].filter(function (item) {
            const id = String(item.id);
            if (seen.has(id)) return false;
            seen.add(id);
            return true;
        });
    }

    function render() {
        const list = items();
        const keep = keepValue;
        keepValue = options.multiple ? [] : '';

        childSelect.innerHTML = '';

        if (!options.multiple) {
            childSelect.appendChild(new Option(placeholder, ''));
        }

        const keepSet = options.multiple ? new Set((Array.isArray(keep) ? keep : []).map(String)) : null;

        list.forEach(function (item) {
            const option = new Option(item.name, item.id);
            option.selected = options.multiple
                ? keepSet.has(String(item.id))
                : (keep !== '' && String(item.id) === String(keep));
            childSelect.appendChild(option);
        });

        if (typeof options.onRender === 'function') options.onRender();
    }

    branchSelect.addEventListener('change', render);
    if (courseSelect) courseSelect.addEventListener('change', render);
    render();

    return render;
}

/**
 * Single-level version of cascadeCheckboxGrid: repopulates a checkbox-card grid `container`
 * whenever `parentSelect` changes, using the flat list `dataMap[parentSelect.value]`. Used for
 * one-level cascades like Course -> Batches (multi-select).
 */
function cascadeCheckboxGridByParent(container, parentSelect, dataMap, options = {}) {
    if (!container || !parentSelect) return function () {};

    const inputName = options.name || 'items[]';
    const extraClass = options.className || '';
    let keepValues = options.keepValue || [];

    function render() {
        const parentId = parentSelect.value;
        const list = (parentId && dataMap[parentId]) ? dataMap[parentId] : [];
        const keepSet = new Set((Array.isArray(keepValues) ? keepValues : []).map(String));
        keepValues = [];

        container.innerHTML = '';

        if (!list.length) {
            if (options.emptyHtml) {
                container.innerHTML = options.emptyHtml;
            }

            if (typeof options.onRender === 'function') options.onRender();
            return;
        }

        list.forEach(function (item) {
            const id = String(item.id);
            const checked = keepSet.has(id);

            const label = document.createElement('label');
            label.className = 'role-checkbox-item' + (checked ? ' checked' : '');

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = inputName;
            input.value = id;
            input.className = extraClass ? `role-checkbox ${extraClass}` : 'role-checkbox';
            input.checked = checked;
            input.addEventListener('change', function () {
                label.classList.toggle('checked', input.checked);
            });

            const checkIcon = document.createElement('div');
            checkIcon.className = 'check-icon';

            const text = document.createElement('span');
            text.className = 'checkbox-text';
            text.textContent = item.name;

            label.appendChild(input);
            label.appendChild(checkIcon);
            label.appendChild(text);
            container.appendChild(label);
        });

        if (typeof options.onRender === 'function') options.onRender();
    }

    parentSelect.addEventListener('change', render);
    render();

    return render;
}

/**
 * Same branch+course cascading as cascadeByBranchCourse (merging the 'all' bucket with the
 * course-specific bucket), but renders into a `container` <div> as a checkbox-card grid
 * (matching the app's `.role-checkbox-item` / `.check-icon` / `.checkbox-text` pattern) instead
 * of a native <select multiple>. Used for multi-select fields like Student Batches.
 */
function cascadeCheckboxGrid(container, branchSelect, courseSelect, dataMap, options = {}) {
    if (!container || !branchSelect) return function () {};

    const inputName = options.name || 'items[]';
    const extraClass = options.className || '';
    let keepValues = options.keepValue || [];

    function items() {
        const branchId = branchSelect.value;
        const courseId = courseSelect ? courseSelect.value : '';
        const branchBucket = (branchId && dataMap[branchId]) ? dataMap[branchId] : {};
        const common = branchBucket.all || [];
        const specific = (courseId && branchBucket[courseId]) ? branchBucket[courseId] : [];
        const seen = new Set();

        return [...common, ...specific].filter(function (item) {
            const id = String(item.id);
            if (seen.has(id)) return false;
            seen.add(id);
            return true;
        });
    }

    function render() {
        const list = items();
        const keepSet = new Set((Array.isArray(keepValues) ? keepValues : []).map(String));
        keepValues = [];

        container.innerHTML = '';

        if (!list.length) {
            if (options.emptyHtml) {
                container.innerHTML = options.emptyHtml;
            }

            if (typeof options.onRender === 'function') options.onRender();
            return;
        }

        list.forEach(function (item) {
            const id = String(item.id);
            const checked = keepSet.has(id);

            const label = document.createElement('label');
            label.className = 'role-checkbox-item' + (checked ? ' checked' : '');

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = inputName;
            input.value = id;
            input.className = extraClass ? `role-checkbox ${extraClass}` : 'role-checkbox';
            input.checked = checked;
            input.addEventListener('change', function () {
                label.classList.toggle('checked', input.checked);
            });

            const checkIcon = document.createElement('div');
            checkIcon.className = 'check-icon';

            const text = document.createElement('span');
            text.className = 'checkbox-text';
            text.textContent = item.name;

            label.appendChild(input);
            label.appendChild(checkIcon);
            label.appendChild(text);
            container.appendChild(label);
        });

        if (typeof options.onRender === 'function') options.onRender();
    }

    branchSelect.addEventListener('change', render);
    if (courseSelect) courseSelect.addEventListener('change', render);
    render();

    return render;
}
