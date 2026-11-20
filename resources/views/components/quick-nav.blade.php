@php
    $quickNavItems = [];

    if (hasPermission('buat', 'penjualan')) {
        $quickNavItems[] = [
            'label' => 'Import Penjualan',
            'url' => route('penjualan.create.multiple'),
            'icon' => 'icon-upload-cloud',
        ];
    }

    if (hasPermission('buat', 'pembelian')) {
        $quickNavItems[] = [
            'label' => 'Buat Pembelian',
            'url' => route('pembelian.create'),
            'icon' => 'icon-plus-square',
        ];
    }

    if (hasPermission('lihat', 'laporan_penjualan')) {
        $quickNavItems[] = [
            'label' => 'Laporan Penjualan',
            'url' => route('laporan.penjualan'),
            'icon' => 'icon-file-text',
        ];
    }

    if (hasPermission('lihat', 'laporan_retur')) {
        $quickNavItems[] = [
            'label' => 'Penjualan Retur',
            'url' => route('laporan.retur'),
            'icon' => 'icon-corner-up-left',
        ];
    }

    if (hasPermission('lihat', 'barang')) {
        $quickNavItems[] = [
            'label' => 'Barang & Stok',
            'url' => route('produk.index'),
            'icon' => 'icon-package',
        ];
    }

    if (hasPermission('tambah', 'hitung_stok')) {
        $quickNavItems[] = [
            'label' => 'Stok Opname',
            'url' => route('hitung-stok.create'),
            'icon' => 'icon-clipboard',
        ];
    }

    if (hasPermission('lihat', 'backup_database')) {
        $quickNavItems[] = [
            'label' => 'Backup Database',
            'url' => route('backup.index'),
            'icon' => 'icon-server',
        ];
    }
@endphp

@if (count($quickNavItems))
    <style>
        .quick-nav {
            --qn-orange: #ff7a00;
            --qn-orange-hover: #e66a00;
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            font-family: 'Roboto', sans-serif;
        }

        .quick-nav-panel {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            visibility: hidden;
            opacity: 0;
            transform: translateY(16px) scale(0.92);
            transform-origin: bottom right;
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s;
        }

        .quick-nav.is-open .quick-nav-panel {
            visibility: visible;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
            transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.34, 1.35, 0.64, 1), visibility 0.45s;
        }

        .quick-nav-item {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            height: 44px;
            padding: 0 0 0 14px;
            color: #374151;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            text-decoration: none;
            overflow: hidden;
            box-sizing: border-box;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease, color 0.15s ease;
        }

        .quick-nav-item:hover,
        .quick-nav-item:focus-visible,
        .quick-nav-item.is-active {
            color: #ffffff;
            background: var(--qn-orange);
            border-color: var(--qn-orange);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transform: translateX(-2px);
            text-decoration: none;
            outline: none;
        }

        .quick-nav-item:hover .quick-nav-label,
        .quick-nav-item:focus-visible .quick-nav-label,
        .quick-nav-item.is-active .quick-nav-label {
            color: #ffffff;
        }

        .quick-nav-item:hover .quick-nav-icon,
        .quick-nav-item:focus-visible .quick-nav-icon,
        .quick-nav-item.is-active .quick-nav-icon {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        .quick-nav-label {
            display: inline-block;
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
            color: inherit;
            padding-right: 12px;
            white-space: nowrap;
            transition: color 0.15s ease;
        }

        .quick-nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            color: #ffffff;
            background: var(--qn-orange);
            border-radius: 50%;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .quick-nav-icon i,
        .quick-nav-icon svg {
            font-size: 18px;
            width: 18px;
            height: 18px;
            line-height: 1;
        }

        .quick-nav-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            padding: 0;
            color: #ffffff;
            background: var(--qn-orange);
            border: 0;
            border-radius: 50%;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
            cursor: pointer;
            box-sizing: border-box;
            transition: background-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }

        .quick-nav-toggle:hover {
            background: var(--qn-orange-hover);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.16);
            transform: translateY(-2px);
        }

        .quick-nav-toggle:focus-visible {
            box-shadow: 0 0 0 3px rgba(255, 122, 0, 0.25), 0 4px 12px rgba(0, 0, 0, 0.12);
            outline: none;
        }

        .quick-nav-toggle i,
        .quick-nav-toggle svg {
            font-size: 18px;
            width: 18px;
            height: 18px;
            line-height: 1;
        }

        .quick-nav-icon-close {
            display: none !important;
        }

        .quick-nav.is-open .quick-nav-icon-zap {
            display: none !important;
        }

        .quick-nav.is-open .quick-nav-icon-close {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            animation: qn-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes qn-pop {
            0% {
                transform: scale(0.6) rotate(-45deg);
                opacity: 0;
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        @media (max-width: 575.98px) {
            .quick-nav {
                right: 16px;
                bottom: 16px;
                gap: 8px;
            }

            .quick-nav-item {
                height: 40px;
                padding-left: 12px;
            }

            .quick-nav-label {
                font-size: 12.5px;
                padding-right: 10px;
                max-width: 180px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .quick-nav-icon {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
            }

            .quick-nav-toggle {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
            }

            .quick-nav-icon i,
            .quick-nav-icon svg,
            .quick-nav-toggle i,
            .quick-nav-toggle svg {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .quick-nav-panel,
            .quick-nav-item,
            .quick-nav-toggle,
            .quick-nav-toggle i,
            .quick-nav-toggle svg,
            .quick-nav-icon-close {
                transition: none;
                animation: none;
            }
        }
    </style>

    <div id="quickNav" class="quick-nav">
        <div id="quickNavPanel" class="quick-nav-panel" role="navigation" aria-label="Quick navigation" aria-hidden="true">
            @foreach ($quickNavItems as $item)
                <a href="{{ $item['url'] }}" class="quick-nav-item" title="{{ $item['label'] }}">
                    <span class="quick-nav-label">{{ $item['label'] }}</span>
                    <span class="quick-nav-icon">
                        <i class="feather {{ $item['icon'] }}" data-feather="{{ str_replace('icon-', '', $item['icon']) }}"></i>
                    </span>
                </a>
            @endforeach
        </div>

        <button type="button" id="quickNavToggle" class="quick-nav-toggle" aria-label="Buka quick navigation"
            aria-expanded="false" aria-controls="quickNavPanel">
            <i class="feather icon-zap quick-nav-icon-zap" data-feather="zap"></i>
            <i class="feather icon-x quick-nav-icon-close" data-feather="x"></i>
        </button>
    </div>

    <script>
        (function() {
            const quickNav = document.getElementById('quickNav');
            if (!quickNav) return;

            const toggle = document.getElementById('quickNavToggle');
            const panel = document.getElementById('quickNavPanel');
            if (!toggle || !panel) return;

            const links = Array.from(panel.querySelectorAll('a'));
            let activeIndex = -1;

            const setActive = (index) => {
                if (!links.length) return;

                activeIndex = (index + links.length) % links.length;

                links.forEach(function(link, linkIndex) {
                    link.classList.toggle('is-active', linkIndex === activeIndex);
                });

                links[activeIndex].focus({ preventScroll: true });
            };

            const STORAGE_KEY = 'quick_nav_auto_expand_history';
            let hasSeenAutoExpand = false;

            try {
                hasSeenAutoExpand = !!localStorage.getItem(STORAGE_KEY);
            } catch (e) {
                hasSeenAutoExpand = true;
            }

            let autoCloseTimer = null;
            let isInitialSplash = !hasSeenAutoExpand;

            const setOpen = (open) => {
                quickNav.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Tutup quick navigation' : 'Buka quick navigation');
                panel.setAttribute('aria-hidden', open ? 'false' : 'true');

                if (!open) {
                    activeIndex = -1;
                    links.forEach(function(link) {
                        link.classList.remove('is-active');
                    });
                }
            };

            const cancelAutoClose = () => {
                if (autoCloseTimer) {
                    clearTimeout(autoCloseTimer);
                    autoCloseTimer = null;
                }
            };

            const openQuickNav = () => {
                cancelAutoClose();
                isInitialSplash = false;
                setOpen(true);
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        setActive(0);
                    });
                });
            };

            const toggleQuickNav = () => {
                if (quickNav.classList.contains('is-open')) {
                    const focusWasInside = quickNav.contains(document.activeElement);
                    cancelAutoClose();
                    isInitialSplash = false;
                    setOpen(false);

                    if (focusWasInside) {
                        toggle.focus({ preventScroll: true });
                    }

                    return;
                }

                openQuickNav();
            };

            // Jalankan auto-expand hanya 1x jika belum ada history di localStorage
            if (!hasSeenAutoExpand) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify({
                        shown: true,
                        timestamp: new Date().toISOString()
                    }));
                } catch (e) {}

                setTimeout(function() {
                    setOpen(true);
                    autoCloseTimer = setTimeout(function() {
                        setOpen(false);
                        isInitialSplash = false;
                    }, 2600);
                }, 350);

                // Jika user mengarahkan kursor ke menu saat splash, batalkan auto-close agar menu tetap terbuka
                quickNav.addEventListener('mouseenter', function() {
                    if (isInitialSplash) {
                        cancelAutoClose();
                    }
                });

                // Jika user selesai hover dan splash tadi belum selesai, beri jeda 1 detik lalu tutup
                quickNav.addEventListener('mouseleave', function() {
                    if (isInitialSplash && quickNav.classList.contains('is-open')) {
                        autoCloseTimer = setTimeout(function() {
                            setOpen(false);
                            isInitialSplash = false;
                        }, 1000);
                    }
                });
            }

            toggle.addEventListener('click', function(event) {
                event.stopPropagation();
                cancelAutoClose();
                isInitialSplash = false;
                setOpen(!quickNav.classList.contains('is-open'));
            });

            document.addEventListener('click', function(event) {
                if (!quickNav.contains(event.target)) {
                    cancelAutoClose();
                    isInitialSplash = false;
                    setOpen(false);
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.altKey && !event.ctrlKey && !event.metaKey &&
                    (event.code === 'KeyS' || (event.key || '').toLowerCase() === 's')) {
                    event.preventDefault();
                    toggleQuickNav();
                    return;
                }

                if (event.key === 'Escape') {
                    const focusWasInside = quickNav.contains(document.activeElement);
                    cancelAutoClose();
                    isInitialSplash = false;
                    setOpen(false);

                    if (focusWasInside) {
                        toggle.focus({ preventScroll: true });
                    }

                    return;
                }

                const isArrowDown = event.key === 'ArrowDown' || event.code === 'ArrowDown';
                const isArrowUp = event.key === 'ArrowUp' || event.code === 'ArrowUp';
                const isAltArrow = event.altKey && (isArrowDown || isArrowUp);

                if (!quickNav.classList.contains('is-open')) return;
                if (!isAltArrow && !quickNav.contains(document.activeElement)) return;

                if (isAltArrow) {
                    event.preventDefault();

                    if (activeIndex < 0) {
                        setActive(isArrowDown ? 0 : links.length - 1);
                    } else {
                        setActive(activeIndex + (isArrowDown ? 1 : -1));
                    }

                    return;
                }

                if (event.key === 'Enter') {
                    const activeLink = document.activeElement &&
                        document.activeElement.closest
                        ? document.activeElement.closest('.quick-nav-item')
                        : null;

                    if (activeLink) {
                        event.preventDefault();
                        activeLink.click();
                    }
                }
            });

            links.forEach(function(link) {
                link.addEventListener('click', function() {
                    cancelAutoClose();
                    isInitialSplash = false;
                    setOpen(false);
                });
            });
        })();
    </script>
@endif
