@extends('layouts.admin')
@section('content') @include('admin.inventoryItems.form',['action'=>route('admin.inventory-items.update',$inventoryItem->id),'method'=>'PUT']) @endsection
