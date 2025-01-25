@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request.css') }}">
@endsection

<x-admin_header></x-admin_header>

@section('content')
<div class="staff__list">
    <h1>申請一覧</h1>
    <div class="staff__tab">
        <!-- タブ切り替え -->
        <a class="tab__inner {{ $status === '承認待ち' ? 'active' : '' }}"
            href="{{ route('admin.requests.index', ['status' => '承認待ち']) }}">承認待ち</a>
        <a class="tab__inner {{ $status === '承認済み' ? 'active' : '' }}"
            href="{{ route('admin.requests.index', ['status' => '承認済み']) }}">承認済み</a>
    </div>
    <table class="staff__table">
        <thead>
            <tr>
                <th class="table__ttl">状態</th>
                <th class="table__ttl">名前</th>
                <th class="table__ttl">対象日時</th>
                <th class="table__ttl">申請理由</th>
                <th class="table__ttl">申請日時</th>
                <th class="table__ttl">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($editRequests as $request)
            <tr>
                <td class="table__inner">{{ $request->status }}</td>
                <td class="table__inner">{{ $request->user->name }}</td>
                <td class="table__inner">{{ $request->attendance->date }}</td>
                <td class="table__inner">{{ $request->reason }}</td>
                <td class="table__inner">{{ $request->requested_at }}</td>
                <td class="table__inner">
                    @if ($status === '承認待ち')
                    <form method="POST" action="{{ route('admin.requests.approve', $request->id) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="approve__button">承認</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection