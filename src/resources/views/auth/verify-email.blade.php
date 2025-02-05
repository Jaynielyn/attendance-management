@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mail.css') }}">
@endsection

<x-header_logo></x-header_logo>

@section('content')
<div class="mail__page">
    <h1 class="mail__ttl">メール認証が必要です</h1>
    <p class="mail__txt">登録したメールアドレスに確認メールを送信しました。<br>メールのリンクをクリックして認証を完了してください。</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="mail__resend" type="submit">認証メールを再送信</button>
    </form>
</div>
@endsection