@extends('layouts.admin')

@section('page-title', 'Edit Inventory Item')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.inventory-items.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Inventory Item</h2>

        <p class="admin-page-subtitle">
            Update stock item with branch, category, unit, stock level and status
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.inventory-items.update', $inventoryItem->id) }}">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-boxes"></i>
                </div>

                <div>
                    <p class="form-card-title">Item Information</p>
                    <p class="form-card-subtitle">Update item name, branch, category and unit</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-box icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               required
                               value="{{ old('name', $inventoryItem->name) }}"
                               placeholder="Example: Whiteboard Marker"
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('name') }}
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
                                <option value="{{ $id }}" {{ old('branch_id', $inventoryItem->branch_id) == $id ? 'selected' : '' }}>
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
                               value="{{ old('category', $inventoryItem->category) }}"
                               placeholder="Example: Stationery, Furniture, Lab Item"
                               class="field-input {{ $errors->has('category') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('category'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('category') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="unit">
                        Unit
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-balance-scale icon"></i>

                        <input type="text"
                               name="unit"
                               id="unit"
                               value="{{ old('unit', $inventoryItem->unit) }}"
                               placeholder="Example: pcs, box, kg, set"
                               class="field-input {{ $errors->has('unit') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('unit'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('unit') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Unit stock calculation and transaction entry me use hoga.
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-warehouse"></i>
                </div>

                <div>
                    <p class="form-card-title">Stock & Cost</p>
                    <p class="form-card-subtitle">Update opening stock, alert level and item cost</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="opening_stock">
                        Opening Stock
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-cubes icon"></i>

                        <input type="number"
                               name="opening_stock"
                               id="opening_stock"
                               min="0"
                               value="{{ old('opening_stock', $inventoryItem->opening_stock ?? 0) }}"
                               class="field-input {{ $errors->has('opening_stock') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('opening_stock'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('opening_stock') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="low_stock_level">
                        Low Stock Level
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-exclamation-triangle icon"></i>

                        <input type="number"
                               name="low_stock_level"
                               id="low_stock_level"
                               min="0"
                               value="{{ old('low_stock_level', $inventoryItem->low_stock_level ?? 0) }}"
                               class="field-input {{ $errors->has('low_stock_level') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('low_stock_level'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('low_stock_level') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-bell"></i>
                            Current stock is level se kam ya equal hoga to low stock alert maana jayega.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="unit_cost">
                        Unit Cost
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="unit_cost"
                               id="unit_cost"
                               value="{{ old('unit_cost', $inventoryItem->unit_cost ?? 0) }}"
                               class="field-input {{ $errors->has('unit_cost') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('unit_cost'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('unit_cost') }}
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
                            <option value="active" {{ old('status', $inventoryItem->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive" {{ old('status', $inventoryItem->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
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

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Opening stock update karne se base stock value change hogi. Stock in/out transaction separately manage hoga.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>

                <div>
                    <p class="form-card-title">Stock Preview</p>
                    <p class="form-card-subtitle">Review stock level, value and item status before updating</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <p class="stat-label">Opening Stock</p>
                        <p class="stat-value" id="previewStock" style="font-size:22px;">0</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Low Stock Level</p>
                        <p class="stat-value" id="previewLowStock" style="font-size:22px;">0</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Approx Value</p>
                        <p class="stat-value" id="previewValue" style="font-size:22px;">₹0.00</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Status</p>
                        <p class="stat-value" id="previewStatus" style="font-size:22px;">-</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.inventory-items.index') }}" class="btn-ghost">
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

function updateInventoryPreview() {
    const openingStock = document.getElementById('opening_stock');
    const lowStockLevel = document.getElementById('low_stock_level');
    const unitCost = document.getElementById('unit_cost');
    const status = document.getElementById('status');

    const stock = parseFloat(openingStock && openingStock.value ? openingStock.value : 0);
    const lowStock = parseFloat(lowStockLevel && lowStockLevel.value ? lowStockLevel.value : 0);
    const cost = parseFloat(unitCost && unitCost.value ? unitCost.value : 0);
    const value = stock * cost;

    document.getElementById('previewStock').innerText = stock;
    document.getElementById('previewLowStock').innerText = lowStock;
    document.getElementById('previewValue').innerText = '₹' + value.toFixed(2);
    document.getElementById('previewStatus').innerText = formatText(status ? status.value : '');
}

document.addEventListener('DOMContentLoaded', function () {
    ['opening_stock', 'low_stock_level', 'unit_cost', 'status'].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateInventoryPreview);
            el.addEventListener('change', updateInventoryPreview);
        }
    });

    updateInventoryPreview();
});
</script>
@endsection