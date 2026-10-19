@extends('admin.hotel.search')

@section('search_results')
    <section class="search-result">
        <div class="result-head">
            <h3 class="search-result-title">{{ !empty($isSearched) ? '検索結果' : 'ホテル一覧' }}</h3>
            @if ($hotelList->isNotEmpty())
                <p class="result-count"><strong>{{ $hotelList->total() }}</strong>件中 {{ $hotelList->firstItem() }}〜{{ $hotelList->lastItem() }}件を表示</p>
            @endif
        </div>
        @if ($hotelList->isNotEmpty())
            <div class="table-scroll">
                <table class="shopsearchlist_table">
                    <thead>
                        <tr>
                            <th id="hotel_name">ホテル名</th>
                            <th id="pref">都道府県</th>
                            <th id="created_at">登録日</th>
                            <th id="updated_at">更新日</th>
                            <th class="actions"><span class="visually-hidden">操作</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelList as $hotel)
                            <tr>
                                <td>
                                    <a class="hotel-link" href="{{ route('hotelDetail', ['hotel_id' => $hotel['hotel_id']]) }}" target="_blank" rel="noopener">{{ $hotel['hotel_name'] }}</a>
                                </td>
                                <td>{{ $hotel['prefecture']['prefecture_name'] }}</td>
                                <td class="num">{{ $hotel['created_at']?->format('Y-m-d H:i') }}</td>
                                <td class="num">{{ $hotel['updated_at']?->format('Y-m-d H:i') }}</td>
                                <td class="actions">
                                    <a class="btn btn-small" href="{{ route('adminHotelEditPage', ['hotel_id' => $hotel['hotel_id']]) }}">編集</a>
                                    <form action="{{ route('adminHotelDeleteProcess') }}" method="post"
                                          data-confirm-delete
                                          data-confirm-text="「{{ $hotel['hotel_name'] }}」を削除します。削除したデータは元に戻せません。">
                                        @csrf
                                        <input type="hidden" name="hotel_id" value="{{ $hotel['hotel_id'] }}">
                                        <button type="submit" class="btn btn-small btn-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $hotelList->links('pagination::default') }}
        @elseif (!empty($isSearched))
            <p class="result-empty">条件に一致するホテルはありません。ホテル名を短くするか、都道府県を変えて検索してください。</p>
        @else
            <p class="result-empty">登録されているホテルはありません。「ホテル追加」から登録してください。</p>
        @endif
    </section>
@endsection
