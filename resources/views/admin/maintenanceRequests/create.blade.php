@extends('layouts.admin')
@section('content') @include('admin.maintenanceRequests.form',['action'=>route('admin.maintenance-requests.store'),'method'=>'POST','maintenanceRequest'=>null]) @endsection
