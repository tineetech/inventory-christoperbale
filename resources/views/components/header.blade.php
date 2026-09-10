<nav class="layout-navbar navbar navbar-expand-lg align-items-lg-center bg-dark container-p-x" id="layout-navbar">

    <!-- Brand demo (see assets/css/demo/demo.css) -->
    <a href="" class="navbar-brand app-brand demo d-lg-none py-0 mr-4">
        <span class="">
            <img src="{{ asset('assets/img/logo-inv.png') }}" style="width:40px;" alt="Brand Logo" class="img-fluid">
        </span>
        <span class="app-brand-text demo font-weight-normal ml-2">POS Inventory</span>
    </a>

    <!-- Sidenav toggle (see assets/css/demo/demo.css) -->
    <div class="layout-sidenav-toggle navbar-nav d-lg-none align-items-lg-center mr-auto">
        <a class="nav-item nav-link px-0 mr-lg-4" href="javascript:">
            <i class="ion ion-md-menu text-large align-middle"></i>
        </a>
    </div>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#layout-navbar-collapse">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse collapse" id="layout-navbar-collapse">
        <!-- Divider -->
        <hr class="d-lg-none w-100 my-2">

        <div class="navbar-nav align-items-lg-center">
            <!-- Search -->
            {{-- <label class="nav-item navbar-text navbar-search-box p-0 active">
                <i class="feather icon-search navbar-icon align-middle"></i>
                <span class="navbar-search-input pl-2">
                    <input type="text" class="form-control navbar-text mx-2" placeholder="Search...">
                </span>
            </label> --}}
        </div>

        <div class="navbar-nav align-items-lg-center ml-auto">
            <!-- Notifikasi -->
            <div class="demo-navbar-notifications nav-item dropdown mr-lg-3" id="notif-wrapper">
                <a class="nav-link hide-arrow" href="javascript:" id="notif-bell-link">
                    <i class="feather icon-bell navbar-icon align-middle"></i>
                    <span class="badge badge-danger badge-dot indicator" id="notif-indicator" style="display:none;"></span>
                    <span class="badge badge-danger notif-count" id="notif-count" style="display:none;">0</span>
                    <span class="d-lg-none align-middle">&nbsp; Notifikasi</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right notif-dropdown-menu" id="notif-dropdown">
                    <div class="bg-warning text-center text-white font-weight-bold p-3 d-flex justify-content-between align-items-center">
                        <span>Notifikasi</span>
                        <a href="javascript:" id="notif-mark-all" class="text-white small" style="opacity:.9;">Tandai semua dibaca</a>
                    </div>
                    <div class="notif-list list-group list-group-flush" id="notif-list">
                        <div class="notif-empty text-center text-light p-4">Tidak ada notifikasi.</div>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="nav-item d-none d-lg-block text-big font-weight-light line-height-1 opacity-25 mr-3 ml-1">|</div>
            <div class="demo-navbar-user nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <span class="d-inline-flex flex-lg-row-reverse align-items-center align-middle">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="d-block ui-w-30 rounded-circle">
                        <span class="px-1 mr-lg-2 ml-2 ml-lg-0">
                            {{ ucfirst(Auth::guard('pengguna')->user()->nama) }}
                        </span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="feather icon-user text-muted"></i> &nbsp; My profile</a>
                    <!-- <a href="javascript:" class="dropdown-item">
                        <i class="feather icon-mail text-muted"></i> &nbsp; Messages</a> -->
                    {{-- <a href="javascript:" class="dropdown-item">
                        <i class="feather icon-settings text-muted"></i> &nbsp; Account settings</a> --}}
                    <div class="dropdown-divider"></div>
                    <a href="/logout" class="dropdown-item">
                        <i class="feather icon-power text-danger"></i> &nbsp; Log Out</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    #layout-navbar {
        z-index: 1080;
    }

    #notif-wrapper {
        position: relative;
        z-index: 1085;
    }

    #notif-wrapper .nav-link {
        position: relative;
    }

    #notif-count {
        position: absolute;
        top: 0;
        left: 20px;
        font-size: 10px;
        padding: 2px 5px;
        border-radius: 10px;
        line-height: 1.2;
        min-width: 16px;
        text-align: center;
        z-index: 5;
    }

    #notif-indicator {
        position: absolute;
        top: 4px;
        left: 26px;
    }

    .notif-dropdown-menu {
        width: 360px;
        max-width: 92vw;
        padding: 0;
        max-height: 75vh;
        display: none;
        overflow: hidden;
        border: none;
        border-radius: 12px;
        box-shadow: 0 12px 36px rgba(20, 24, 60, 0.18), 0 2px 8px rgba(20, 24, 60, 0.08);
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        left: auto;
        z-index: 1090; /* di atas navbar & komponen lain agar tidak tertimpa */
        animation: notifFadeIn .15s ease-out;
    }

    @keyframes notifFadeIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notif-dropdown-menu.show {
        display: block;
    }

    /* Anak panah kecil penunjuk arah dropdown */
    .notif-dropdown-menu::before {
        content: '';
        position: absolute;
        top: -6px;
        right: 18px;
        width: 12px;
        height: 12px;
        background: #1454a3;
        transform: rotate(45deg);
        border-radius: 2px;
        z-index: -1;
    }

    .notif-dropdown-menu .bg-primary {
        background: linear-gradient(135deg, #1e70cd, #1454a3) !important;
        padding: 14px 16px !important;
        font-size: 14px;
        letter-spacing: .2px;
        position: relative;
        z-index: 1;
    }

    .notif-dropdown-menu .bg-primary a {
        text-decoration: none;
        font-weight: 500;
        font-size: 12px;
        color: #fff;
        transition: opacity .15s ease;
    }

    .notif-dropdown-menu .bg-primary a:hover {
        opacity: 1 !important;
        text-decoration: underline;
    }

    .notif-list {
        max-height: 56vh;
        overflow-y: auto;
        background: #fff;
    }

    .notif-list::-webkit-scrollbar {
        width: 6px;
    }

    .notif-list::-webkit-scrollbar-thumb {
        background: #d7dae8;
        border-radius: 10px;
    }

    .notif-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .notif-empty {
        font-size: 13px;
        color: #a9adc2;
    }

    .notif-item {
        position: relative;
        border-left: 3px solid transparent;
        border-bottom: 1px solid #f0f1f6;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        padding: 12px 14px;
        transition: background .15s ease;
        text-decoration: none;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background: #f6f8fd;
        text-decoration: none;
    }

    .notif-item.unread {
        background: #eef4ff;
        border-left-color: #1e70cd;
    }

    .notif-item.unread:hover {
        background: #e5eeff;
    }

    .notif-item .notif-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        margin-right: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    .notif-item .notif-title {
        font-weight: 600;
        font-size: 13px;
        color: #23263b;
    }

    .notif-item .notif-body {
        font-size: 12.5px;
        color: #7a7e99;
        margin-top: 2px;
        line-height: 1.5;
        word-break: break-word;
    }

    .notif-item .notif-time {
        font-size: 11px;
        color: #b0b3c4;
        margin-top: 5px;
    }

    .notif-item .notif-dot {
        width: 8px;
        height: 8px;
        min-width: 8px;
        border-radius: 50%;
        background: #1e70cd;
        box-shadow: 0 0 0 3px rgba(30, 112, 205, .15);
        margin-top: 6px;
        margin-left: 8px;
        align-self: flex-start;
    }
</style>

<script>
    (function () {
        var notifUrl = "{{ route('notifikasi.data') }}";
        var readUrl = "{{ route('notifikasi.read') }}";
        var notifIndexUrl = "{{ route('notifikasi.index') }}";
        var csrf = "{{ csrf_token() }}";
        var pollMs = 5000;
        var notifItemIcons = {
            'penjualan_web': { icon: 'icon-shopping-cart', color: '#1e70cd' },
            'pembayaran': { icon: 'icon-credit-card', color: '#28a745' },
            'default': { icon: 'icon-bell', color: '#6f42c1' }
        };

        function getIconCfg(tipe) {
            return notifItemIcons[tipe] || notifItemIcons['default'];
        }

        function renderList(items) {
            var listEl = document.getElementById('notif-list');
            if (!listEl) return;

            if (!items || !items.length) {
                listEl.innerHTML = '<div class="notif-empty text-center text-light p-4">Tidak ada notifikasi.</div>';
                return;
            }

            var html = '';
            items.forEach(function (n) {
                var cfg = getIconCfg(n.tipe);
                var cls = n.is_read ? '' : ' unread';
                var link = n.link ? 'href="' + n.link + '"' : 'href="javascript:"';
                html += '<a ' + link + ' class="notif-item list-group-item-action list-group-item' + cls + '" data-id="' + n.id + '">' +
                    '<div class="notif-icon" style="background:' + cfg.color + ';">' +
                    '<i class="feather ' + cfg.icon + '" style="width:16px;height:16px;"></i></div>' +
                    '<div class="media-body" style="min-width:0;">' +
                    '<div class="notif-title">' + escapeHtml(n.judul) + '</div>' +
                    '<div class="notif-body">' + escapeHtml(n.isi) + '</div>' +
                    '<div class="notif-time">' + (n.created_diff || '') + '</div></div>' +
                    (n.is_read ? '' : '<span class="notif-dot"></span>') +
                    '</a>';
            });
            listEl.innerHTML = html;
        }

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateBadge(count) {
            var countEl = document.getElementById('notif-count');
            var indEl = document.getElementById('notif-indicator');
            if (!countEl) return;

            if (count > 0) {
                countEl.style.display = 'inline-block';
                countEl.textContent = count > 99 ? '99+' : count;
                if (indEl) indEl.style.display = 'inline-block';
                document.title = '(' + count + ') POS Inventory';
            } else {
                countEl.style.display = 'none';
                if (indEl) indEl.style.display = 'none';
                document.title = 'POS Inventory';
            }
        }

        function loadNotifikasi() {
            fetch(notifUrl, {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res && res.success) {
                        updateBadge(res.unread_count || 0);
                        renderList(res.data || []);
                    }
                })
                .catch(function () { /* silent */ });
        }

        function markAllRead() {
            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({})
            })
                .then(function (r) { return r.json(); })
                .then(function () { loadNotifikasi(); });
        }

        function markOneRead(id) {
            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ id: id })
            })
                .then(function (r) { return r.json(); })
                .then(function () {
                    loadNotifikasi();
                    window.location.href = notifIndexUrl;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var listEl = document.getElementById('notif-list');
            var bellLink = document.getElementById('notif-bell-link');
            var dropdown = document.getElementById('notif-dropdown');

            bellLink.addEventListener('click', function (e) {
                e.preventDefault();
                var isOpen = dropdown.classList.contains('show');
                closeNotifDropdown();
                if (!isOpen) {
                    dropdown.classList.add('show');
                }
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#notif-wrapper')) {
                    closeNotifDropdown();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeNotifDropdown();
                }
            });

            function closeNotifDropdown() {
                var dd = document.getElementById('notif-dropdown');
                if (dd) dd.classList.remove('show');
            }

            document.getElementById('notif-mark-all').addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                markAllRead();
            });

            listEl.addEventListener('click', function (e) {
                var item = e.target.closest('.notif-item');
                if (!item) return;

                e.preventDefault();

                var id = item.getAttribute('data-id');
                if (item.classList.contains('unread')) {
                    markOneRead(id);
                } else {
                    window.location.href = notifIndexUrl;
                }
            });

            // Fetch data notifikasi sekali saja saat halaman dibuka
            loadNotifikasi();

            // Feather icon refresh setelah render
            if (window.feather) {
                setInterval(function () {
                    if (document.querySelector('.notif-dropdown-menu.show')) {
                        feather.replace();
                    }
                }, 300);
            }
        });
    })();
</script>