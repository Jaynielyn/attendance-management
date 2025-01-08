@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection
<x-header_logo></x-header_logo>

@section('content')
<div class="register__container">
    <h1 class="register__title">会員登録</h1>
    <form action="{{ route('register') }}" method="POST" class="register__form" novalidate>
        @csrf
        <div class="form__group">
            <label for="name" class="form__label">名前</label>
            <input type="text" id="name" name="name" class="form__input" required>
            @error('name')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form__group">
            <label for="email" class="form__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form__input" required>
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form__group">
            <label for="password" class="form__label">パスワード</label>
            <input type="password" id="password" name="password" class="form__input" required>
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form__group">
            <label for="password_confirmation" class="form__label">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form__input" required>
        </div>
        <button type="submit" class="form__btn-submit">登録する</button>
    </form>
    <a href="{{ route('login') }}" class="register__link-login">ログインはこちら</a>
</div>
@endsection