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
                @elseif($attendance->status === 'finished')
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
            <p id="currentDate">{{ now()->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
        </div>
        <div class="attendance__time">
            <p id="currentTime">{{ now()->format('H:i') }}</p>
        </div>

        @if($attendance)
        <div class="attendance__button-group">
            @if($attendance->status === 'working')
            <form action="{{ route('attendance.checkOut') }}" method="POST" id="checkOutForm">
                @csrf
                <button type="submit" class="attendance__button">退勤</button>
            </form>
            <form action="{{ route('attendance.breakStart') }}" method="POST" id="breakStartForm">
                @csrf
                <button type="submit" class="attendance__button attendance__button--break">休憩入</button>
            </form>
            @elseif($attendance->status === 'break')
            <form action="{{ route('attendance.breakEnd') }}" method="POST" id="breakEndForm">
                @csrf
                <button type="submit" class="attendance__button attendance__button--break-end">休憩戻</button>
            </form>
            @elseif($attendance->status === 'finished')
            <div class="attendance__message attendance__message--hidden">
                <p>お疲れ様でした。</p>
            </div>
            @endif
        </div>
        @else
        <form action="{{ route('attendance.checkIn') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button">出勤</button>
        </form>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        updateTime();
        setInterval(updateTime, 60000);

        function updateTime() {
            const now = new Date();
            const currentTime = now.toLocaleTimeString('ja-JP', {
                hour: '2-digit',
                minute: '2-digit'
            });
            const currentDate = now.toLocaleDateString('ja-JP', {
                weekday: 'short',
                year: 'numeric',
                month: 'numeric',
                day: 'numeric'
            });

            document.getElementById('currentTime').textContent = currentTime;
            document.getElementById('currentDate').textContent = currentDate;
        }
    });
</script>
@endsection