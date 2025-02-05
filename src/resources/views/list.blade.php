@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

<x-staff_header></x-staff_header>

@section('content')
<div class="attendance__list">
    <h1>勤怠一覧</h1>
    <div class="navigation">
        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::createFromFormat('Y/m', $currentMonth)->subMonth()->format('Y-m')]) }}" class="prev__month">
            <img src="{{ asset('img/arrow-left-solid.svg') }}" alt="前月" class="icon"> 前月
        </a>
        <span class="current__month">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="icon"> {{ $currentMonth }}
        </span>
        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::createFromFormat('Y/m', $currentMonth)->addMonth()->format('Y-m')]) }}" class="next__month">
            翌月 <img src="{{ asset('img/arrow-right-solid.svg') }}" alt="翌月" class="icon">
        </a>
    </div>


    <table class="attendance__table">
        <thead>
            <tr>
                <th class="table__ttl">日付</th>
                <th class="table__ttl">出勤</th>
                <th class="table__ttl">退勤</th>
                <th class="table__ttl">休憩</th>
                <th class="table__ttl">合計</th>
                <th class="table__ttl table__ttl-detail">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td class="table__inner">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}
                    (<span class="weekday">{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('ddd') }}</span>)
                </td>
                <td class="table__inner">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                <td class="table__inner">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                <td class="table__inner">{{ $attendance->break_time }}</td>
                <td class="table__inner">{{ $attendance->total_work_time }}</td>
                <td class="table__inner">
                    <a class="table__inner-link" href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection