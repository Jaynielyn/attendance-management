@extends('layouts.app')

<x-header_logo></x-header_logo>

@section('content')
<h1>メール認証が必要です</h1>
<p>登録したメールアドレスに確認メールを送信しました。メールのリンクをクリックして認証を完了してください。</p>
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit">認証メールを再送信</button>
</form>
@endsection