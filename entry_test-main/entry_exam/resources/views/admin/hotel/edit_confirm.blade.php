<!-- base view -->
@extends('common.admin.base')

<!-- main contents -->
@section('main_contents')
    <div class="page-wrapper form-page">
        <h2 class="title">編集内容の確認</h2>
        <p class="page-lead">以下の内容で更新します。よろしければ「更新する」を押してください。</p>
        <div class="form-panel">
            <div class="table-scroll">
                <table class="confirm-table">
                    <thead>
                        <tr>
                            <th>項目</th>
                            <th>変更前</th>
                            <th>変更後</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>ホテル名</th>
                            <td>{{ $hotel->hotel_name }}</td>
                            <td class="{{ $hotel->hotel_name !== $draft['hotel_name'] ? 'is-changed' : '' }}">{{ $draft['hotel_name'] }}</td>
                        </tr>
                        <tr>
                            <th>都道府県</th>
                            <td>{{ $hotel->prefecture->prefecture_name }}</td>
                            <td class="{{ $hotel->prefecture_id !== $draft['prefecture_id'] ? 'is-changed' : '' }}">{{ $prefecture->prefecture_name }}</td>
                        </tr>
                        <tr>
                            <th>ホテル画像</th>
                            <td>
                                @if ($hotel->file_path)
                                    <img src="/assets/img/{{ $hotel->file_path }}" alt="変更前の画像">
                                @endif
                            </td>
                            <td class="{{ $draft['tmp_image'] ? 'is-changed' : '' }}">
                                @if ($draft['tmp_image'])
                                    <img src="/assets/img/{{ $draft['tmp_image'] }}" alt="変更後の画像">
                                @else
                                    変更なし
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <form action="{{ route('adminHotelEditProcess') }}" method="post">
                @csrf
                <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_id }}">
                <input type="hidden" name="step" value="save">
                <div class="form-actions">
                    <a class="btn" href="{{ route('adminHotelEditPage', ['hotel_id' => $hotel->hotel_id, 'back' => 1]) }}">入力画面に戻る</a>
                    <button type="submit" class="btn btn-primary">更新する</button>
                </div>
            </form>
        </div>
    </div>
@endsection
