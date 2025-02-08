@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<x-admin_header></x-admin_header>

@section('content')
<div class="attendance__detail">
    <h1 class="detail__ttl">勤怠詳細</h1>
    <div class="detail__form-container">
        <form class="detail__form" method="POST" action="{{ route('admin.attendance.update', ['userId' => $attendance->user_id, 'date' => $attendance->date]) }}">
            @csrf
            @method('PUT')
            <table class="table__content">
                <tr class="table__item">
                    <th class="table__ttl">名前</th>
                    <td class="table__inner">{{ $attendance->user->name }}</td>
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
                            <input type="text" name="check_in" class="time" value="{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}">
                            <span>~</span>
                            <input type="text" name="check_out" class="time" value="{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}">
                        </div>
                        @error('check_in')
                        <span class="error__message">{{ $message }}</span>
                        @enderror
                        @error('check_out')
                        <span class="error__message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                @foreach ($attendance->breakTimes as $index => $breakTime)
                <tr class="table__item">
                    <th class="table__ttl">休憩{{ $index + 1 }}</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" name="break_start[]" class="time" value="{{ old("break_start.$index", \Carbon\Carbon::parse($breakTime->break_start)->format('H:i')) }}">
                            <span>~</span>
                            <input type="text" name="break_end[]" class="time" value="{{ old("break_end.$index", \Carbon\Carbon::parse($breakTime->break_end)->format('H:i')) }}">
                        </div>
                        @php
                        $breakError = $errors->has("break_start.$index") || $errors->has("break_end.$index");
                        @endphp
                        @if ($breakError)
                        <span class="error__message">休憩時間が勤務時間外です。</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr class="table__item">
                    <th class="table__ttl">休憩{{ $attendance->breakTimes->count() + 1 }}</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" name="break_start[]" class="time">
                            <span>~</span>
                            <input type="text" name="break_end[]" class="time">
                        </div>
                        @php
                        $breakError = $errors->has("break_start.$index") || $errors->has("break_end.$index");
                        @endphp
                        @if ($breakError)
                        <span class="error__message">休憩時間が勤務時間外です。</span>
                        @endif
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">備考</th>
                    <td class="table__inner">
                        <textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                        @error('remarks')
                        <span class="error__message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>

            </table>
            <button type="submit" class="edit__button">修正</button>
        </form>
    </div>
</div>

@endsection