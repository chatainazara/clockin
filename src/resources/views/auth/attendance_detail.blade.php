@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <h1 class="ttl">勤怠一覧</h1>
        <form class="attendance__form" method="POST" action="/attendance/detail/{{$work->id}}">
            @csrf
            <input type="hidden" name="work_id" value="{{ $work->id }}">

            <div class="content__row">
                <div class="content__label">名前</div>
                <div class="content__value">{{ $work->user->name }}</div>
            </div>

            <div class="content__row">
                <div class="content__label">日付</div>
                <div class="content__value">{{ \Carbon\Carbon::parse($work->work_date)->format('Y年n月j日') }}</div>
            </div>

            {{-- 出退勤修正申請 --}}
            <div class="content__row">
                <div class="content__label">出勤・退勤</div>
                <div class="content__value">
                    <input class="input-time" type="time" name="work_application[clock_in_at]"
                        value="{{ \Carbon\Carbon::parse($work->clock_in_at)->format('H:i') }}">
                    〜
                    <input class="input-time" type="time" name="work_application[clock_out_at]"
                        value="{{ \Carbon\Carbon::parse($work->clock_out_at)->format('H:i') }}">
                </div>
            </div>
            @error('work_application.clock_in_at')
                <div class="error">{{ $message }}</div>
            @enderror
            @error('work_application.clock_out_at')
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- 既存休憩の修正申請 --}}
            @foreach($work->rests as $index => $rest)
            <div class="content__row">
                <div class="content__label">休憩{{ $index + 1 }}</div>
                <div class="content__value">
                    <input type="hidden" name="rest_applications[{{ $index }}][rest_id]" value="{{ $rest->id }}">
                    <input class="input-time" type="time" name="rest_applications[{{ $index }}][rest_start_at]"
                        value="{{ \Carbon\Carbon::parse($rest->rest_start_at)->format('H:i') }}">
                    〜
                    <input class="input-time" type="time" name="rest_applications[{{ $index }}][rest_end_at]"
                        value="{{ \Carbon\Carbon::parse($rest->rest_end_at)->format('H:i') }}">
                </div>
            </div>
            @error("rest_applications.$index.rest_start_at")
                <div class="error">{{ $message }}</div>
            @enderror
            @error("rest_applications.$index.rest_end_at")
                <div class="error">{{ $message }}</div>
            @enderror
            @endforeach

            {{-- 新規追加用休憩 --}}
            <div class="content__row">
                <div class="content__label">休憩{{ $work->rests->count() + 1 }}</div>
                <div class="content__value">
                    <input type="hidden" name="rest_applications[{{ $work->rests->count() }}][rest_id]" value="">
                    <input class="input-time" type="time" name="rest_applications[{{ $work->rests->count() }}][rest_start_at]" value="">
                    〜
                    <input class="input-time" type="time" name="rest_applications[{{ $work->rests->count() }}][rest_end_at]" value="">
                </div>
            </div>
            @error("rest_applications." . $work->rests->count() . ".rest_start_at")
                <div class="error">{{ $message }}</div>
            @enderror
            @error("rest_applications." . $work->rests->count() . ".rest_end_at")
                <div class="error">{{ $message }}</div>
            @enderror

            {{-- 備考 --}}
            <div class="content__row">
                <div class="content__label">備考</div>
                <div class="content__value">
                    <textarea name="work_application[reason]" class="textarea"></textarea>
                </div>
            </div>
            @error('work_application.reason')
                <div class="error">{{ $message }}</div>
            @enderror
            <div class="actions">
                <button type="submit" class="button">修正申請</button>
            </div>
        </form>
    </div>
</div>
@endsection
