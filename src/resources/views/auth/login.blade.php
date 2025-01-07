@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection
<x-header_logo></x-header_logo>

@section('content')
<div class="login__container">
    <h1 class="login__title">ログイン</h1>
    <form action="/login" method="POST" class="login__form">
        @csrf
        <div class="form__group">
            <label for="email" class="form__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form__input" required>
        </div>
        <div class="form__group">
            <label for="password" class="form__label">パスワード</label>
            <input type="password" id="password" name="password" class="form__input" required>
        </div>
        <button type="submit" class="form__btn-submit">登録する</button>
    </form>
    <a href="{{ route('register') }}" class="login__link-register">会員登録はこちら</a>
</div>
@endsection