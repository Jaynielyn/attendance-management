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
                @foreach ($attendance->breakTimes as $index => $break)
                <tr class="table__item">
                    @if ($index === 0)
                    <th class="table__ttl">休憩</th>
                    @else
                    <th class="table__ttl">休憩{{ $index + 1 }}</th>
                    @endif
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="hidden" name="break_times[{{ $index }}][id]" value="{{ $break->id }}">
                            <input type="text" class="time" name="break_times[{{ $index }}][start]"
                                placeholder="HH:mm" value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}">
                            <span>~</span>
                            <input type="text" class="time" name="break_times[{{ $index }}][end]"
                                placeholder="HH:mm" value="{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="table__item">
                    <th class="table__ttl">備考</th>
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