@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection
<x-staff_header></x-staff_header>

@section('content')
<div class="attendance">
    <div class="attendance__container">
        <div class="attendance__status">
            <span class="attendance__status-text">
                @if($attendance)
                @if($attendance->status === 'working')
                出勤中
                @elseif($attendance->status === 'break')
                休憩中
                @elseif($attendance->status === 'off')
                勤務外
                @elseif($attendance->status === 'checkedOut')
                退勤済
                @else
                {{ ucfirst($attendance->status) }}
                @endif
                @else
                勤務外
                @endif
            </span>
        </div>
        <div class="attendance__date">
            <p>{{ now()->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
        </div>
        <div class="attendance__time">
            <p>{{ now()->format('H:i') }}</p>
        </div>

        @if($attendance)
        @if($attendance->status === 'working')
        <div class="attendance__button-group">
            <form action="{{ route('attendance.checkOut') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button">退勤</button>
            </form>
            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button attendance__button--break">休憩入</button>
            </form>
        </div>
        @endif
        @else
        <form action="{{ route('attendance.checkIn') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button">出勤</button>
        </form>
        @endif
    </div>
</div>
@endsection