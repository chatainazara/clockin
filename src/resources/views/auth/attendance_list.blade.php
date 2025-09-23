@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <h1 class="ttl">勤怠一覧</h1>
        <div class="attendance__header">
            <a class="pagenate" href="/attendance/list?month={{ $yearMonth->copy()->subMonth()->format('Y-m') }}">&larr; 前月</a>
            <span class="pagenate__month"><img class="calender-icon" src="{{asset('img/icon1.png')}}"> {{ $yearMonth->format('Y/m') }}</span>
            <a class="pagenate" href="/attendance/list?month={{ $yearMonth->copy()->addMonth()->format('Y-m') }}">翌月 &rarr;</a>
        </div>
        <table class="attendance__table">
            <thead >
                <tr>
                    <th class="attendance__table-head">日付</th>
                    <th class="attendance__table-head">出勤</th>
                    <th class="attendance__table-head">退勤</th>
                    <th class="attendance__table-head">休憩</th>
                    <th class="attendance__table-head">合計</th>
                    <th class="attendance__table-head">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                <tr>
                    <td class="attendance__table-data">{{ $day['date'] }}({{ $day['weekday'] }})</td>
                    <td class="attendance__table-data">{{ $day['clock_in'] }}</td>
                    <td class="attendance__table-data">{{ $day['clock_out'] }}</td>
                    <td class="attendance__table-data">{{ $day['rest'] }}</td>
                    <td class="attendance__table-data">{{ $day['total'] }}</td>
                    <td class="attendance__table-data">
                        <a class="attendance__table-data--link" href="/attendance/detail/{{$day['work_id']}}">
                            @if($day['work_id'])
                            詳細
                            @endif
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
