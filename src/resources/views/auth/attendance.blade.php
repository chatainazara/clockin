@extends('layouts.app')

@section('title', '出勤打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance.css') }}">
@endsection

@section('content')
<div class="center">
    <p>
    <span class="badge">勤務外</span>
    </p>
    <h2>{{ $date }}</h2>
    <h1 class="clock">{{ $time }}</h1>

    <form action="{{ route('attendance.clock_in') }}" method="POST">
    @csrf
    <button type="submit">出勤</button>
    </form>
</div>
@endsection