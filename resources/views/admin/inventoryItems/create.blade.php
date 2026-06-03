@extends('layouts.admin')
@section('content') @include('admin.inventoryItems.form',['action'=>route('admin.inventory-items.store'),'method'=>'POST','inventoryItem'=>null]) @endsection
