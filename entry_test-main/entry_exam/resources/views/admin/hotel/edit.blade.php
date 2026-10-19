<!-- base view -->
@extends('common.admin.base')

<!-- main contents -->
@section('main_contents')
    <div class="page-wrapper form-page">
        <h2 class="title">ホテル編集</h2>
        <p class="page-lead">内容を変更して「確認画面へ」を押してください。</p>
        <div class="form-panel">
            <form action="{{ route('adminHotelEditProcess') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_id }}">
                <input type="hidden" name="step" value="confirm">
                @include('admin.hotel.form_fields', [
                    'hotelName' => $hotelName,
                    'prefectureId' => $prefectureId,
                    'imageRequired' => false,
                    'currentImage' => $hotel->file_path,
                    'keptImage' => $keptImage,
                ])
                <div class="form-actions">
                    <a class="btn" href="{{ route('adminHotelSearchPage') }}">キャンセル</a>
                    <button type="submit" class="btn btn-primary">確認画面へ</button>
                </div>
            </form>
        </div>
    </div>
@endsection
