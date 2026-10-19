<!-- base view -->
@extends('common.admin.base')

<!-- CSS per page -->
@section('custom_css')
    @vite('resources/scss/admin/search.scss')
    @vite('resources/scss/admin/result.scss')
@endsection

<!-- main containts -->
@section('main_contents')
    <div class="page-wrapper search-page-wrapper">
        <div class="page-head">
            <div>
                <h2 class="title">検索画面</h2>
                <p class="page-lead">ホテル名の一部、または都道府県で検索できます。</p>
            </div>
            <a class="btn btn-primary" href="{{ route('adminHotelCreatePage') }}">
                <svg class="btn-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 3v10M3 8h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                ホテル追加
            </a>
        </div>
        <div class="search-form search-hotel-name">
            <form action="{{ route('adminHotelSearchResult') }}" method="post">
                @csrf
                <div class="search-fields">
                    <label class="field">
                        ホテル名
                        <input type="text" name="hotel_name" value="{{ old('hotel_name', request('hotel_name')) }}" placeholder="ホテル名" maxlength="255">
                    </label>
                    <label class="field">
                        都道府県
                        <select name="prefecture_id">
                            <option value="">すべての都道府県</option>
                            @foreach ($prefectures as $prefecture)
                                <option value="{{ $prefecture->prefecture_id }}" @selected((string) old('prefecture_id', request('prefecture_id')) === (string) $prefecture->prefecture_id)>
                                    {{ $prefecture->prefecture_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary">検索</button>
                </div>
            </form>
            @foreach ($errors->all() as $message)
                <p class="error-message">{{ $message }}</p>
            @endforeach
        </div>
        @yield('search_results')
    </div>
@endsection
