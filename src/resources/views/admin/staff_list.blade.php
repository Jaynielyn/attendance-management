@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff.css') }}">
@endsection

<x-admin_header></x-admin_header>

@section('content')
<div class="staff__list">
    <h1>スタッフ一覧</h1>
    <table class="staff__table">
        <thead>
            <tr class="staff__table-item">
                <th class="table__ttl item__name-ttl">名前</th>
                <th class="table__ttl">メールアドレス</th>
                <th class="table__ttl item__detail-ttl">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
            <tr class="staff__table-item">
                <td class="table__inner item__name-inner">{{ $staff->name }}</td>
                <td class="table__inner">{{ $staff->email }}</td>
                <td class="table__inner item__detail-inner">
                    <a href="{{ route('admin.staff_attendance', ['id' => $staff->id]) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="table__inner">スタッフが見つかりません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection