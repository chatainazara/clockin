@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <h1 class="ttl">{{ $date->format('Y年n月j日') }}の勤怠</h1>
        <div class="attendance__header">
            {{-- 前日ボタン --}}
            <a class="pagenate" href="/admin/attendance/list?date={{ $date->copy()->subDay()->toDateString() }}" >&larr; 前日</a>
            {{-- カレンダー入力 --}}
            <form class="pagenate__date" method="GET" action="/admin/attendance/list" style="display:inline;">
                <img class="calender-icon" src="{{asset('img/icon1.png')}}">
                <input
                    class="calender"
                    type="date"
                    name="date"
                    value="{{ $date->toDateString() }}"
                    onchange="this.form.submit()"
                    class="date-picker">
            </form>
            {{-- 翌日ボタン --}}
            <a class="pagenate" href="/admin/attendance/list?date={{ $date->copy()->addDay()->toDateString() }}">翌日 &rarr;</a>
        </div>
        <table class="attendance__table">
            <thead>
                <tr>
                    <th class="attendance__table-head">名前</th>
                    <th class="attendance__table-head">出勤</th>
                    <th class="attendance__table-head">退勤</th>
                    <th class="attendance__table-head">休憩</th>
                    <th class="attendance__table-head">合計</th>
                    <th class="attendance__table-head">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($works as $work)
                @if($work->user->role !== 'admin')
                <tr>
                    <td class="attendance__table-data">{{ $work->user->name }}</td>
                    <td class="attendance__table-data">{{ $work->start_display }}</td>
                    <td class="attendance__table-data">{{ $work->end_display }}</td>
                    <td class="attendance__table-data">{{ $work->rest_display }}</td>
                    <td class="attendance__table-data">{{ $work->work_display }}</td>
                    <td class="attendance__table-data">
                        <a class="attendance__table-data--link" href="/admin/attendance/{{$work->id}}">
                            @if($work->id)
                            詳細
                            @endif
                        </a>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
