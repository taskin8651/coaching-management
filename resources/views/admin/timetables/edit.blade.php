@extends('layouts.admin')
@section('content') @include('admin.timetables.form',['action'=>route('admin.timetables.update',$timetable->id),'method'=>'PUT']) @endsection
