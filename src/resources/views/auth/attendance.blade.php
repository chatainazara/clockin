@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance.css') }}">
@endsection

@section('content')
<script>
    function updateClock() {
        const now = new Date();
        const y = now.getFullYear();
        const m = now.getMonth() + 1;
        const d = now.getDate();
        const h = ("0" + now.getHours()).slice(-2);
        const i = ("0" + now.getMinutes()).slice(-2);
        const s = ("0" + now.getSeconds()).slice(-2);
        const weekdays = ["日", "月", "火", "水", "木", "金", "土"];
        const w = weekdays[now.getDay()];

        document.getElementById("date").textContent = `${y}年${m}月${d}日（${w}）`;
        document.getElementById("time").textContent = `${h}:${i}`;
    }
    setInterval(updateClock, 1000); // 1秒ごとに更新
    window.onload = updateClock;
</script>

<div class="content">
    <div class="content__inner">
        @if (!$todayWork)
        <span class="content__badge">勤務外</span>
        @elseif ($todayWork->clock_out_at)
        <span class="content__badge">退勤済</span>
        @elseif ($todayWork->rests->contains(fn($rest) => $rest->rest_start_at && !$rest->rest_end_at))
        <span class="content__badge">休憩中</span>
        @else
        <span class="content__badge">出勤中</span>
        @endif
        <div class="content__date" id="date"></div>
        <div class="content__time" id="time"></div>
        <form class="content__btn" method="POST" action="attendance/action">
            @csrf

            @if (!$todayWork)
                <button class="btn" type="submit" name="action" value="clockIn">出勤</button>

            @elseif ($todayWork->clock_out_at)
                <div><p>お疲れ様でした。</p></div>

            @elseif ($todayWork->rests->contains(fn($rest) => $rest->rest_start_at && !$rest->rest_end_at))
                <button class="btn2" type="submit" name="action" value="restEnd">休憩戻</button>

            @else
                <button class="btn" type="submit" name="action" value="clockOut">退勤</button>
                <button class="btn2" type="submit" name="action" value="restStart">休憩入</button>
            @endif
        </form>
    </div>
</div>
@endsection