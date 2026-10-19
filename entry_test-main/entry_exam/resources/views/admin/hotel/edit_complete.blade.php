<!-- base view -->
@extends('common.admin.base')

<!-- main contents -->
@section('main_contents')
    <div class="page-wrapper">
        <section class="result-card">
            <svg class="result-card-icon" viewBox="0 0 48 48" aria-hidden="true">
                <circle cx="24" cy="24" r="22" fill="currentColor" opacity="0.12"/>
                <path d="M15 24.5l6 6 12-13" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h2 class="title">更新が完了しました</h2>
            <p class="page-lead">ホテル情報を以下の内容で保存しました。</p>

            <div class="hotel-summary">
                @if ($hotel->file_path)
                    <img class="hotel-summary-thumb" src="/assets/img/{{ $hotel->file_path }}" alt="{{ $hotel->hotel_name }}">
                @endif
                <dl class="hotel-summary-body">
                    <dt>ホテル名</dt>
                    <dd class="hotel-summary-name">{{ $hotel->hotel_name }}</dd>
                    <dt>都道府県</dt>
                    <dd>{{ $hotel->prefecture->prefecture_name }}</dd>
                    <dt>更新日時</dt>
                    <dd class="num">{{ $hotel->updated_at?->format('Y-m-d H:i') }}</dd>
                </dl>
            </div>

            <div class="result-card-actions">
                <a class="btn" href="{{ route('hotelDetail', ['hotel_id' => $hotel->hotel_id]) }}" target="_blank" rel="noopener">ユーザー画面で確認</a>
                <a class="btn btn-primary" href="{{ route('adminHotelSearchPage') }}">ホテル一覧へ戻る</a>
            </div>
        </section>
    </div>
@endsection
