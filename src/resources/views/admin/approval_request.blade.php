@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<x-admin_header></x-admin_header>

@section('content')
<div class="attendance__detail">
    <h1 class="detail__ttl">修正申請詳細</h1>
    <div class="detail__form-container">
        <form class="detail__form" method="POST" action="{{ route('admin.approve_request', $editRequest->id) }}">
            @csrf
            <table class="table__content">
                <tr class="table__item">
                    <th class="table__ttl">名前</th>
                    <td class="table__inner">{{ $editRequest->user->name }}</td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">日付</th>
                    <td class="table__inner">
                        <div class="date__row">
                            <input type="text" name="year" class="year" value="{{ \Carbon\Carbon::parse($editRequest->new_date)->format('Y年') }}" readonly>
                            <input type="text" name="month_day" class="day" value="{{ \Carbon\Carbon::parse($editRequest->new_date)->format('m月d日') }}" readonly>
                        </div>
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">出勤・退勤</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" name="check_in" class="time" value="{{ \Carbon\Carbon::parse($editRequest->new_check_in)->format('H:i') }}" readonly>
                            <span>~</span>
                            <input type="text" name="check_out" class="time" value="{{ \Carbon\Carbon::parse($editRequest->new_check_out)->format('H:i') }}" readonly>
                        </div>
                    </td>
                </tr>
                @foreach ($editRequest->editBreakTimes as $index => $breakTime)
                <tr class="table__item">
                    @if ($index === 0)
                    <th class="table__ttl">休憩</th>
                    @else
                    <th class="table__ttl">休憩{{ $index + 1 }}</th>
                    @endif
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" class="time" value="{{ \Carbon\Carbon::parse($breakTime->new_break_start)->format('H:i') }}" readonly>
                            <span>~</span>
                            <input type="text" class="time" value="{{ \Carbon\Carbon::parse($breakTime->new_break_end)->format('H:i') }}" readonly>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="table__item">
                    <th class="table__ttl">備考</th>
                    <td class="table__inner">
                        <textarea name="remarks" readonly>{{ $editRequest->reason }}</textarea>
                    </td>
                </tr>
            </table>
            @if ($editRequest->approval_status === '承認済み')
            <button class="edit__button btn__success" disabled>承認済み</button>
            @else
            <form method="POST" action="{{ route('admin.approve_request', $editRequest->id) }}">
                @csrf
                <button type="submit" class="edit__button btn-primary">承認</button>
            </form>
            @endif
        </form>
    </div>
</div>
@endsection