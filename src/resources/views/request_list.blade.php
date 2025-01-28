@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request.css') }}">
@endsection

<x-staff_header></x-staff_header>

@section('content')
<div class="staff__list">
    <h1>申請一覧</h1>
    <div class="staff__tab">
        <!-- タブ切り替え -->
        <a href="{{ route('user.requests', ['status' => '承認待ち']) }}"
            class="tab__inner {{ request('status', '承認待ち') === '承認待ち' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('user.requests', ['status' => '承認済み']) }}"
            class="tab__inner {{ request('status') === '承認済み' ? 'active' : '' }}">承認済み</a>
    </div>
    <table class="staff__table">
        <thead>
            <tr>
                <th class="table__ttl">状態</th>
                <th class="table__ttl">対象日時</th>
                <th class="table__ttl">申請理由</th>
                <th class="table__ttl">申請日時</th>
                <th class="table__ttl">詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($editRequests as $request)
            <tr>
                <td class="table__inner">{{ $request->approval_status }}</td>
                <td class="table__inner">{{ \Carbon\Carbon::parse($request->new_date)->format('Y/m/d') }}</td>
                <td class="table__inner">{{ $request->reason }}</td>
                <td class="table__inner">{{ \Carbon\Carbon::parse($request->requested_at)->format('Y/m/d') }}</td>
                <td class="table__inner"><a href="{{ route('user.request.detail', $request->id) }}">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td class="table__inner" colspan="5">データがありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection