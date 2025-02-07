<div class="header">
    <div class="header__container">
        <div class="header__logo">
            <a href="/admin/attendance/list"><img class="header__logo-img" src="{{ asset('img/logo.svg') }}" alt="Sample Logo"></a>
        </div>

        <div class="header__menu-container">
            <nav class="header__nav">
                <ul class="header__menu">
                    <li class="header__menu-item"><a href="/admin/attendance/list">勤怠一覧</a></li>
                    <li class="header__menu-item"><a href="/admin/staff/list">スタッフ一覧</a></li>
                    <li class="header__menu-item"><a href="/admin/requests">申請一覧</a></li>
                </ul>
            </nav>

            <div class="logout">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="menu__logout" type="submit">ログアウト</button>
                </form>
            </div>
        </div>
    </div>
</div>