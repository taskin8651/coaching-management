@php
    $selectedUserId = old('user_id', $selectedUserId ?? '');
@endphp

<div class="field-group">
    <label class="field-label" for="user_id">Select User (Optional)</label>

    <div class="input-icon-wrap">
        <i class="fas fa-user-check icon"></i>

        <select name="user_id"
                id="user_id"
                class="field-input {{ $errors->has('user_id') ? 'error' : '' }}">
            @foreach($users as $id => $user)
                <option value="{{ $id }}" {{ (string) $selectedUserId === (string) $id ? 'selected' : '' }}>
                    {{ $user }}
                </option>
            @endforeach
        </select>
    </div>

    @if($errors->has('user_id'))
        <p class="field-error">{{ $errors->first('user_id') }}</p>
    @else
        <p class="field-hint">User select karte hi registered name, email, phone, branch aur biometric code auto-fill ho jayega.</p>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userDetails = @json($userDetails ?? []);
    const userSelect = document.getElementById('user_id');

    if (! userSelect) {
        return;
    }

    const setValue = (id, value) => {
        const field = document.getElementById(id);

        if (! field || value === null || value === undefined) {
            return;
        }

        field.value = value;
    };

    userSelect.addEventListener('change', function () {
        const user = userDetails[this.value];

        if (! user) {
            return;
        }

        setValue('account_name', user.name);
        setValue('account_email', user.email);
        setValue('phone', user.phone);
        setValue('branch_id', user.branch_id);
        setValue('biometric_id', user.biometric_id);

        const password = document.getElementById('account_password');

        if (password) {
            password.value = '';
            password.placeholder = 'Existing user selected - leave blank to keep password';
        }
    });
});
</script>
