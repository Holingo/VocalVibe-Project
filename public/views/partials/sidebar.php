<aside class="apple-sidebar">
    <div class="sidebar-top">
        <a href="/dashboard?page=book-now" class="nav-item <?= $page === 'book-now' ? 'active' : '' ?>" title="Strona Główna">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="/dashboard?page=my-bookings" class="nav-item <?= $page === 'my-bookings' ? 'active' : '' ?>" title="Moje Rezerwacje">
            <i class="fa-solid fa-border-all"></i>
        </a>

        <a href="/dashboard?page=menu" class="nav-item <?= $page === 'menu' ? 'active' : '' ?>" title="Menu Barowe">
            <i class="fa-solid fa-mug-hot"></i>
        </a>

        <a href="/dashboard?page=loyalty" class="nav-item <?= $page === 'loyalty' ? 'active' : '' ?>" title="Moje Konto">
            <i class="fa-regular fa-user"></i>
        </a>
    </div>

    <div class="sidebar-bottom">
        <a href="/logout" class="nav-item logout" title="Wyloguj się">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</aside>