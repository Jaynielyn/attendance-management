@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<x-staff_header></x-staff_header>

@section('content')
<div class="attendance__detail">
    <h1 class="detail__ttl">勤怠詳細</h1>
    <div class="detail__form-container">
        <form class="detail__form" method="POST" action="">
            @csrf
            <table class="table__content">
                <tr class="table__item">
                    <th class="table__ttl">名前</th>
                    <td class="table__inner"></td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">日付</th>
                    <td class="table__inner">
                        <div class="date__row">
                            <input type="text" name="year" class="year">
                            <input type="text" name="month_day" class="day">
                        </div>
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">出勤・退勤</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" name="check_in" class="time" placeholder="HH:mm">
                            <span>~</span>
                            <input type="text" name="check_out" class="time" placeholder="HH:mm">
                        </div>
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">休憩</th>
                    <td class="table__inner">
                        <div class="time__row">
                            <input type="text" class="time" placeholder="HH:mm">
                            <span>~</span>
                            <input type="text" class="time" placeholder="HH:mm">
                        </div>
                    </td>
                </tr>
                <tr class="table__item">
                    <th class="table__ttl">理由</th>
                    <td class="table__inner">
                        <textarea name="remarks"></textarea>
                    </td>
                </tr>
            </table>
            <button type="submit" class="edit__button">承認</button>
        </form>
    </div>
</div>
@endsection