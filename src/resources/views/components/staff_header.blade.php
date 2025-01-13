<div class="header">
    <div class="header__container">
        <div class="header__logo">
            <a href="/"><img class="header__logo-img" src="{{ asset('img/logo.svg') }}" alt="Sample Logo"></a>
        </div>

        <div class="header__menu-container">
            <nav class="header__nav">
                <ul class="header__menu">
                    <li class="header__menu-item"><a href="/">勤怠</a></li>
                    <li class="header__menu-item"><a href="/attendance/list">勤怠一覧</a></li>
                    <li class="header__menu-item"><a href="#">申請</a></li>
                </ul>
            </nav>

            <div class="logout">
                <form method="POST" action="/logout">
                    @csrf
                    <button class="menu__logout" type="submit">ログアウト</button>
                </form>
            </div>
        </div>
    </div>
</div>