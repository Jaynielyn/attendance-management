@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

<x-staff_header></x-staff_header>

@section('content')
<div class="staff__list">
    <h1>申請一覧</h1>
    <div class="staff__tab">
        <a class="tab__inner" href="">承認待ち</a>
        <a class="tab__inner" href="">承認済み</a>
    </div>
    <table class="staff__table">
        <thead>
            <tr>
                <th class="table__ttl">状態</th>
                <th class="table__ttl">名前</th>
                <th class="table__ttl">対象日時</th>
                <th class="table__ttl">申請理由</th>
                <th class="table__ttl">申請日時</th>
                <th class="table__ttl">詳細</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table__inner"></td>
                <td class="table__inner"></td>
                <td class="table__inner"></td>
                <td class="table__inner"></td>
                <td class="table__inner"></td>
                <td class="table__inner">
                    <a class="table__inner-link" href="">詳細</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection