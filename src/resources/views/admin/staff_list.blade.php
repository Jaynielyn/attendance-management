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
            <tr>
                <th class="table__ttl">名前</th>
                <th class="table__ttl">メールアドレス</th>
                <th class="table__ttl">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
            <tr>
                <td class="table__inner">{{ $staff->name }}</td>
                <td class="table__inner">{{ $staff->email }}</td>
                <td class="table__inner">
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