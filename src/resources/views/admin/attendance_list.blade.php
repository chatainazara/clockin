
@extends('layouts.app')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endpush

@section('content')
<h1 class="title">2023年6月1日の勤怠</h1>

<div class="date-nav">
    <button class="btn">&larr; 前日</button>
    <span class="date">📅 2023/06/01</span>
    <button class="btn">翌日 &rarr;</button>
</div>

<div class="table-container">
    <table class="table">
    <thead>
        <tr>
        <th>名前</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        <tr>
        <td>山田 太郎</td>
        <td>09:00</td>
        <td>18:00</td>
        <td>1:00</td>
        <td>8:00</td>
        <td><a href="{{ route('attendance.show', 1) }}">詳細</a></td>
        </tr>
        <tr>
        <td>西 玲奈</td>
        <td>09:00</td>
        <td>18:00</td>
        <td>1:00</td>
        <td>8:00</td>
        <td><a href="{{ route('attendance.show', 2) }}">詳細</a></td>
        </tr>
    </tbody>
    </table>
</div>
@endsection