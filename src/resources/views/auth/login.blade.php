@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection
<x-header_logo></x-header_logo>

@section('content')
<div class="login__container">
    <h1 class="login__title">ログイン</h1>
    <form action="{{ route('login') }}" method="POST" class="login__form" novalidate>
        @csrf
        <div class="form__group">
            <label for="email" class="form__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form__input" required>
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form__group form__group-pass">
            <label for="password" class="form__label">パスワード</label>
            <input type="password" id="password" name="password" class="form__input" required>
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="form__btn-submit">ログインする</button>
    </form>
    <a href="{{ route('register') }}" class="login__link-register">会員登録はこちら</a>
</div>
@endsection