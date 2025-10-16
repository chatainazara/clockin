@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/approve.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <h1 class="ttl">勤怠詳細</h1>
        <div class="attendance__form">
            {{-- 名前 --}}
            <div class="content__row">
                <div class="content__label">名前</div>
                <div class="content__value">{{ $workApp->work->user->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="content__row">
                <div class="content__label">日付</div>
                <div class="content__value">
                    {{ optional(\Carbon\Carbon::parse($workApp->work->work_date))->format('Y年') }}
                </div>
                <div class="content__value">
                </div>
                <div class="content__value">
                    {{ optional(\Carbon\Carbon::parse($workApp->work->work_date))->format('n月j日') }}
                </div>
            </div>

            {{-- 出退勤 --}}
            <div class="content__row">
                <div class="content__label">出勤・退勤</div>
                <div class="content__value clockInOut">
                {{ $workApp->work->clock_in_at ? optional(\Carbon\Carbon::parse($workApp->work->clock_in_at))->format('H:i') : '未入力' }}
                </div>
                <div class="content__value clockInOut">
                〜
                </div>
                <div class="content__value clockInOut">
                {{ $workApp->work->clock_out_at ? optional(\Carbon\Carbon::parse($workApp->work->clock_out_at))->format('H:i') : '未入力' }}
                </div>
            </div>

            {{-- 休憩一覧 --}}
            <div id="rests">
                @php $restIndex = 1; @endphp
                @foreach($workApp->rest_applications as $restApp)
                    @php
                        $start = $restApp->rest_start_at ? optional(\Carbon\Carbon::parse($restApp->rest_start_at))->format('H:i') : '未入力';
                        $end   = $restApp->rest_end_at ? optional(\Carbon\Carbon::parse($restApp->rest_end_at))->format('H:i') : '未入力';
                        $rowId = $restApp->rest_id ?? 'new-' . $restIndex;
                    @endphp
                    <div class="content__row" id="rest-{{ $rowId }}">
                        <div class="content__label">休憩{{ $restIndex }}</div>
                        <div class="content__value">
                            {{ $start }}
                        </div>
                        <div class="content__value">
                            〜
                        </div>
                        <div class="content__value">
                            {{ $end }}
                        </div>

                    </div>
                    @php $restIndex++; @endphp
                @endforeach
            </div>

            {{-- 備考 --}}
            <div class="content__row">
                <div class="content__label">備考</div>
                <div class="content__value" id="reason">
                    {{ $workApp->reason ?? '（未入力）' }}
                </div>
            </div>
        </div>
            {{-- 承認ボタン --}}
            <div class="actions">
                @if($workApp->approve_at)
                    <button class="button2" disabled>承認済み</button>
                @else
                    <button id="approveBtn" class="button" data-route="/work_applications/{{$workApp->id}}/approve">
                        承認
                    </button>
                @endif
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('approveBtn');
    if(!btn) return; // 承認済みの場合はボタンが存在しないので処理しない

    btn.addEventListener('click', function() {
        const url = btn.dataset.route;
        if (!url) return;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'ok'){

                // ボタン無効化
                btn.disabled = true;
                btn.innerText = '承認済み';

                // クラス切り替え
                btn.classList.remove('button');
                btn.classList.add('button2');

                // 出退勤反映
                const clockEls = document.querySelectorAll('.clockInOut');
                clockEls[0].innerText = data.clock_in_at || '未入力';
                clockEls[1].innerText = '〜';
                clockEls[2].innerText = data.clock_out_at || '未入力';


                // 休憩反映
                const restsEl = document.getElementById('rests');
                restsEl.innerHTML = ''; // いったんクリア
                data.rests.forEach((rest, index) => {
                    const div = document.createElement('div');
                    div.className = 'content__row';
                    div.id = `rest-${rest.id}`;
                    div.innerHTML = `<div class="content__label">休憩${index + 1}</div>
                                    <div class="content__value">
                                    ${rest.rest_start_at || '未入力'}
                                    </div>
                                    <div class="content__value">
                                    〜
                                    </div>
                                    <div class="content__value">
                                    ${rest.rest_end_at || '未入力'}
                                    </div>`;
                    restsEl.appendChild(div);
                });
            } else if(data.status === 'already_approved'){
                btn.disabled = true;
                btn.innerText = '承認済み';
                alert(data.message);
            } else if(data.status === 'error'){
                alert('承認中にエラーが発生しました:\n' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('承認中にエラーが発生しました');
        });
    });
});
</script>
@endsection
