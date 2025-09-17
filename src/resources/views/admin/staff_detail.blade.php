@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_detail.css') }}">
@endsection

@section('content')
    <h1>{{ $user->name }}さんの勤怠</h1>
    <table class="table">
        <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        </thead>
        <tbody>
        @foreach($attendances as $attendance)
        <tr>
            <td>{{ $attendance->date }}</td>
            <td>{{ $attendance->clock_in }}</td>
            <td>{{ $attendance->clock_out }}</td>
            <td>{{ $attendance->break_time }}</td>
            <td>{{ $attendance->total_time }}</td>
            <td><a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div class="center">
        <form action="{{ route('attendance.export') }}" method="POST">
        @csrf
        <button type="submit">CSV出力</button>
        </form>
    </div>
@endsection
