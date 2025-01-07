@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection
<x-staff_header></x-staff_header>

@section('content')
<div class="attendance">
    <div class="attendance__container">
        <div class="attendance__status">
            <span class="attendance__status-text">勤務外</span>
        </div>
        <div class="attendance__date">
            <p>2023年6月1日(木)</p>
        </div>
        <div class="attendance__time">
            <p>08:00</p>
        </div>
        <button class="attendance__button">出勤</button>
    </div>
</div>
@endsection