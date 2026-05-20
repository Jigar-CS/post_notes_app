<header class="site-header">
    <div class="container">
        <a class="brand" href="{{ url('/') }}">{{ config('app.name', 'Mini Blog Notes') }}</a>
        <nav class="nav">
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('/posts') }}">Posts</a>
            <a href="{{ url('/notes') }}">Notes</a>
        </nav>

        <div id="guestLinks" style="display:flex;gap:8px;margin-left:12px;">
            <a href="{{ url('/login') }}">Log in</a>
            <a href="{{ url('/register') }}">Register</a>
        </div>

        <div id="userLinks" style="display:none;gap:8px;margin-left:12px;align-items:center;">
            <span id="usernameDisplay" style="font-weight:600"></span>
            <a href="#" id="logoutBtn">Logout</a>
        </div>

        <button id="menuToggle" class="menu-toggle">☰</button>
    </div>
</header>
