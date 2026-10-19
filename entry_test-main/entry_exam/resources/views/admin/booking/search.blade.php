<!-- base view -->
@extends('common.admin.base')

<!-- CSS per page -->
@section('custom_css')
    @vite('resources/scss/admin/search.scss')
    @vite('resources/scss/admin/result.scss')
@endsection

<!-- main contents -->
@section('main_contents')
    <div class="page-wrapper search-page-wrapper">
        <h2 class="title">予約情報検索</h2>
        <p class="page-lead">顧客名・連絡先は一部だけでも検索できます。条件を空にして検索すると、すべての予約を表示します。</p>
        <div class="search-form">
            <form action="{{ route('adminBookingSearchPage') }}" method="get">
                <input type="hidden" name="search" value="1">
                <div class="search-fields">
                    <label class="field">
                        顧客名
                        <input type="text" name="customer_name" value="{{ request('customer_name') }}" maxlength="255" placeholder="例：山田">
                    </label>
                    <label class="field">
                        顧客連絡先
                        <input type="text" name="customer_contact" value="{{ request('customer_contact') }}" maxlength="255" placeholder="電話番号またはメール">
                    </label>
                    <label class="field">
                        チェックイン日
                        <input type="date" name="checkin_date" value="{{ request('checkin_date') }}">
                    </label>
                    <label class="field">
                        チェックアウト日
                        <input type="date" name="checkout_date" value="{{ request('checkout_date') }}">
                    </label>
                    <button type="submit" class="btn btn-primary">検索</button>
                </div>
            </form>
            @foreach ($errors->all() as $message)
                <p class="error-message">{{ $message }}</p>
            @endforeach
        </div>

        @if ($bookingList !== null)
            <section class="search-result">
                <div class="result-head">
                    <h3 class="search-result-title">検索結果</h3>
                    @if ($bookingList->isNotEmpty())
                        <p class="result-count"><strong>{{ $bookingList->total() }}</strong>件中 {{ $bookingList->firstItem() }}〜{{ $bookingList->lastItem() }}件を表示</p>
                    @endif
                </div>
                @if ($bookingList->isNotEmpty())
                    <div class="table-scroll">
                        <table class="shopsearchlist_table is-compact">
                            <thead>
                                <tr>
                                    <th>顧客名</th>
                                    <th>顧客連絡先</th>
                                    <th>ホテル名</th>
                                    <th>チェックイン日時</th>
                                    <th>チェックアウト日時</th>
                                    <th>予約日時</th>
                                    <th>情報更新日時</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookingList as $booking)
                                    <tr>
                                        <td><strong>{{ $booking->customer_name }}</strong></td>
                                        <td>{{ $booking->customer_contact }}</td>
                                        <td>{{ $booking->hotel?->hotel_name }}</td>
                                        <td class="num">{{ $booking->checkin_time?->format('Y-m-d H:i') }}</td>
                                        <td class="num">{{ $booking->checkout_time?->format('Y-m-d H:i') }}</td>
                                        <td class="num">{{ $booking->created_at?->format('Y-m-d H:i') }}</td>
                                        <td class="num">{{ $booking->updated_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $bookingList->links('pagination::default') }}
                @else
                    <p class="result-empty">条件に一致する予約はありません。条件を減らして検索してください。</p>
                @endif
            </section>
        @endif
    </div>
@endsection
