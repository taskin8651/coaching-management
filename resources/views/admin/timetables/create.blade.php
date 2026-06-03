@extends('layouts.admin')
@section('content') @include('admin.timetables.form',['action'=>route('admin.timetables.store'),'method'=>'POST','timetable'=>null]) @endsection
