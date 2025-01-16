@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection
<x-admin_header></x-admin_header>

@section('content')
<h1>管理者ダッシュボード</h1>
<p>ようこそ、管理者さん！</p>
@endsection