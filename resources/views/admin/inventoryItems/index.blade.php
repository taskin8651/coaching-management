@extends('layouts.admin')

@section('page-title', 'Inventory')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Inventory</h2>
        <p class="admin-page-subtitle">
            Stock master, current stock, low stock alert and item transactions
        </p>
    </div>

    @can('inventory_create')
        <a href="{{ route('admin.inventory-items.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Item
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Items</p>
        <p class="stat-value">{{ $items->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active Items</p>
        <p class="stat-value">{{ $items->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Low Stock</p>
        <p class="stat-value">{{ $items->where('is_low_stock', 1)->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Stock</p>
        <p class="stat-value">{{ $items->sum('current_stock') }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Inventory Items</p>

        <span class="page-card-note">
            <i class="fas fa-boxes"></i>
            Manage stock in, stock out and adjustment entries
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Inventory">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Branch</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Low Alert</th>
                    <th>Status</th>
                    <th>Transaction</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $itemName = $item->name ?? 'Item';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($itemName, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $itemName }}</p>
                                    <p class="table-sub-text">Inventory Item</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->branch->name ?? '-' }}</p>
                            <p class="table-sub-text">Branch</p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ ucfirst(str_replace('_', ' ', $item->category ?? '-')) }}
                            </span>
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $item->current_stock ?? 0 }} {{ $item->unit ?? '' }}
                            </p>
                            <p class="table-sub-text">Current Stock</p>
                        </td>

                        <td>
                            @if($item->is_low_stock)
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Low Stock</span>
                            @else
                                <span class="status-pill success">OK</span>
                            @endif
                        </td>

                        <td>
                            @if($item->status === 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($item->status === 'inactive')
                                <span class="status-pill warning">Inactive</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @can('inventory_transaction_create')
                                <form method="POST"
                                      action="{{ route('admin.inventory-items.transaction', $item->id) }}"
                                      class="action-row"
                                      style="justify-content:flex-start; gap:8px;">
                                    @csrf

                                    <select name="transaction_type"
                                            class="field-input"
                                            required
                                            style="width:120px; min-height:38px;">
                                        <option value="stock_in">In</option>
                                        <option value="stock_out">Out</option>
                                        <option value="adjustment">Set</option>
                                    </select>

                                    <input type="number"
                                           name="quantity"
                                           min="1"
                                           required
                                           placeholder="Qty"
                                           class="field-input"
                                           style="width:90px; min-height:38px;">

                                    <button type="submit" class="btn-outline">
                                        <i class="fas fa-save"></i>
                                        Save
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endcan
                        </td>

                        <td>
                            <div class="action-row">
                                @can('inventory_show')
                                    <a class="btn-outline" href="{{ route('admin.inventory-items.show', $item->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('inventory_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.inventory-items.edit', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('inventory_delete')
                                    <form action="{{ route('admin.inventory-items.destroy', $item->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-Inventory', {
        searchPlaceholder: 'Search inventory...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ inventory items'
    });
});
</script>
@endsection