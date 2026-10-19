<nav class="admin-menu js-admin-menu" aria-label="管理メニュー">
    <div class="menu-block">
        <div class="menu-head">
            <h3 class="title"><a href="{{ route('adminTop') }}">サイト管理<small>THK VN HANOI TRAVEL</small></a></h3>
            {{-- shown only on tablet / mobile --}}
            <button type="button" class="menu-toggle js-menu-toggle" aria-expanded="false" aria-controls="admin-menu-list">
                <span class="menu-toggle-bars" aria-hidden="true"></span>
                <span class="visually-hidden">メニューを開く</span>
            </button>
        </div>
        <ul id="admin-menu-list" class="menu-list">
            <li>
                <a class="link {{ request()->routeIs('adminHotelSearch*', 'adminHotelEdit*') ? 'is-active' : '' }}" href="{{ route('adminHotelSearchPage') }}">ホテル管理</a>
            </li>
            <li>
                <a class="link {{ request()->routeIs('adminHotelCreate*') ? 'is-active' : '' }}" href="{{ route('adminHotelCreatePage') }}">ホテル追加</a>
            </li>
            <li>
                <a class="link {{ request()->routeIs('adminBooking*') ? 'is-active' : '' }}" href="{{ route('adminBookingSearchPage') }}">予約情報検索</a>
            </li>
        </ul>
    </div>
</nav>

<script>
    (function () {
        var menu = document.querySelector('.js-admin-menu');
        var toggle = menu.querySelector('.js-menu-toggle');
        var label = toggle.querySelector('.visually-hidden');

        function setOpen(open) {
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            label.textContent = open ? 'メニューを閉じる' : 'メニューを開く';
        }

        toggle.addEventListener('click', function () {
            setOpen(!menu.classList.contains('is-open'));
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menu.classList.contains('is-open')) {
                setOpen(false);
                toggle.focus();
            }
        });

        // close when tapping outside the menu
        document.addEventListener('click', function (event) {
            if (menu.classList.contains('is-open') && !menu.contains(event.target)) {
                setOpen(false);
            }
        });
    })();
</script>
