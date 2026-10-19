<!-- base view -->
@extends('common.admin.base')

<!-- main contents -->
@section('main_contents')
    <div class="page-wrapper form-page">
        <h2 class="title">ホテル追加</h2>
        <p class="page-lead">登録したホテルは、ユーザー画面の都道府県別ホテル一覧に表示されます。</p>
        <div class="form-panel">
            <form action="{{ route('adminHotelCreateProcess') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf
                @include('admin.hotel.form_fields', [
                    'hotelName' => old('hotel_name', ''),
                    'prefectureId' => old('prefecture_id', ''),
                    'imageRequired' => true,
                    'currentImage' => null,
                    'keptImage' => $keptImage,
                ])
                <div class="form-actions">
                    <a class="btn" href="{{ route('adminHotelSearchPage') }}">キャンセル</a>
                    <button type="submit" class="btn btn-primary">登録する</button>
                </div>
            </form>
        </div>
    </div>
@endsection
