@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection
<x-header_logo></x-header_logo>

@section('content')
<div class="login__container">
    <h1 class="login__title">管理者ログイン</h1>
    <form action="{{ route('admin.login') }}" method="POST" class="login__form">
        @csrf
        <div class="form__group">
            <label for="email" class="form__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form__input" value="{{ old('email') }}">
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form__group form__group-pass">
            <label for="password" class="form__label">パスワード</label>
            <input type="password" id="password" name="password" class="form__input">
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
            @if ($errors->has('login_failed'))
            <div class="error">{{ $errors->first('login_failed') }}</div>
            @endif
        </div>
        <button type="submit" class="form__btn-submit">管理者ログインする</button>
    </form>
</div>
@endsection