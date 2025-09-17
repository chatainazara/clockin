@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_detail.css') }}">
@endpush

@section('content')
<h1 class="title">勤怠詳細</h1>

<form>
<table class="form-table">
    <tr>
    <th>名前</th>
    <td>西 玲奈</td>
    </tr>
    <tr>
    <th>日付</th>
    <td>2023年6月1日</td>
    </tr>
    <tr>
    <th>出勤・退勤</th>
    <td>
        <input type="text" value="09:00"> 〜 <input type="text" value="20:00">
    </td>
    </tr>
    <tr>
    <th>休憩</th>
    <td>
        <input type="text" value="12:00"> 〜 <input type="text" value="13:00">
    </td>
    </tr>
    <tr>
    <th>休憩2</th>
    <td>
        <input type="text"> 〜 <input type="text">
    </td>
    </tr>
    <tr>
    <th>備考</th>
    <td>
        <textarea rows="3"></textarea>
    </td>
    </tr>
</table>

<button type="submit" class="submit-btn">修正</button>
</form>
@endsection