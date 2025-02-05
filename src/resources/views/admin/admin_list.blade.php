@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

<x-admin_header></x-admin_header>

@section('content')
<div class="attendance__list">
    <h1>{{ $currentDate->format('Y年n月j日の勤怠') }}</h1>
    <div class="navigation">
        <a href="{{ route('admin.admin_list', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}" class="prev__day">
            <img src="{{ asset('img/arrow-left-solid.svg') }}" alt="前日" class="icon"> 前日
        </a>

        <span class="current__day">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="icon">
            {{ $currentDate->format('Y/m/d') }}
        </span>

        @if (!$currentDate->isToday())
        <a href="{{ route('admin.admin_list', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}" class="next__day">
            翌日 <img src="{{ asset('img/arrow-right-solid.svg') }}" alt="翌日" class="icon">
        </a>
        @endif
    </div>

    <table class="attendance__table">
        <thead>
            <tr>
                <th class="table__ttl">名前</th>
                <th class="table__ttl">出勤</th>
                <th class="table__ttl">退勤</th>
                <th class="table__ttl">休憩</th>
                <th class="table__ttl">合計</th>
                <th class="table__ttl">詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
            <tr>
                <td class="table__inner table__inner-text">{{ $attendance->user->name }}</td>
                <td class="table__inner">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                <td class="table__inner">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                <td class="table__inner">{{ $attendance->break_time }}</td>
                <td class="table__inner">{{ $attendance->total_work_time }}</td>
                <td class="table__inner table__inner-text">
                    <a href="{{ route('admin.attendance.detail', ['userId' => $attendance->user->id, 'date' => $currentDate->toDateString()]) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="table__inner">出勤情報がありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection