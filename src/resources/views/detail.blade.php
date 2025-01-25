@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<x-staff_header></x-staff_header>

@section('content')
<div class="attendance__detail">
    <h1 class="detail__ttl">勤怠詳細</h1>
    <div class="detail__form-container">
        <form class="detail__form" method="POST" action="{{ route('attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')
            <table class="table__content">
                <tr class="table__item">
                    <th class="table__ttl">名前</th>
                    <td class="table__inner">{{ Auth::user()->name }}</td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">日付</th>
                    <td class="table__inner">
                        <div class="date__row">
                            <input type="text" name="year" class="year" value="{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}">
                            <input type="text" name="month_day" class="day" value="{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}">
                        </div>
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">出勤・退勤</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" name="check_in" class="time" placeholder="HH:mm" value="{{ $attendance->check_in_time }}">
                            <span>~</span>
                            <input type="text" name="check_out" class="time" placeholder="HH:mm" value="{{ $attendance->check_out_time }}">
                        </div>
                    </td>
                </tr>
                @foreach ($breakTimes as $index => $break)
                <tr class="table__item">
                    <th class="table__ttl">休憩</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" class="time" name="break_times[{{ $index }}][start]" placeholder="HH:mm" value="{{ $break['start'] }}">
                            <span>~</span>
                            <input type="text" class="time" name="break_times[{{ $index }}][end]" placeholder="HH:mm" value="{{ $break['end'] }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="table__item">
                    <th class="table__ttl">理由</th>
                    <td class="table__inner">
                        <textarea name="remarks"></textarea>
                    </td>
                </tr>
            </table>
            @if (!$editRequest)
            <button type="submit" class="edit__button">修正</button>
            @else
            <p class="approval__status">*承認待ちのため修正はできません。</p>
            @endif
        </form>
    </div>
</div>
@endsection