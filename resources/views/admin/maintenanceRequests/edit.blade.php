@extends('layouts.admin')
@section('content') @include('admin.maintenanceRequests.form',['action'=>route('admin.maintenance-requests.update',$maintenanceRequest->id),'method'=>'PUT']) @endsection
