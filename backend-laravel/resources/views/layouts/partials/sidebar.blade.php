<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('transactions.index', [], false) }}" class="brand-link">
            <span class="brand-image rounded-circle d-inline-flex align-items-center justify-content-center opacity-75 shadow">
                <i class="bi bi-wallet2 text-white"></i>
            </span>
            <span class="brand-text fw-light">Simple Transactions</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2 h-100" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column h-100" data-lte-toggle="treeview" data-accordion="false">
                <x-admin.nav-item
                    route="transactions.index"
                    icon="bi bi-receipt"
                    label="Transactions"
                />
                <x-admin.nav-item
                    route="reports.index"
                    icon="bi bi-file-earmark-spreadsheet"
                    label="Reports"
                />
                <li class="nav-item has-treeview @if (request()->routeIs('chart-of-accounts.*', 'chart-of-account-categories.*')) menu-open @endif">
                    <a href="#" @class([
                        'nav-link',
                        'active' => request()->routeIs('chart-of-accounts.*', 'chart-of-account-categories.*'),
                    ])>
                        <i class="nav-icon bi bi-database"></i>
                        <p>
                            Master Data
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <x-admin.nav-item
                            route="chart-of-account-categories.index"
                            icon="bi bi-tags"
                            label="Chart of Account Categories"
                        />
                        <x-admin.nav-item
                            route="chart-of-accounts.index"
                            icon="bi bi-journal-text"
                            label="Chart of Accounts"
                        />
                    </ul>
                </li>
                <li class="nav-item has-treeview menu-open mt-auto">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-link-45deg"></i>
                        <p>
                            Other Links
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a
                                href="https://github.com/dragonregure/simple-transaction-webapp"
                                class="nav-link"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="nav-icon bi bi-github"></i>
                                <p>Repository</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                href="https://getlifely.vercel.app/"
                                class="nav-link"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <span class="nav-icon sidebar-lifely-icon" aria-hidden="true">
                                    <img src="/images/lifely-icon.png" alt="">
                                </span>
                                <p>Get Lifely</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                href="https://github.com/dragonregure/lifely"
                                class="nav-link"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="nav-icon bi bi-github"></i>
                                <p>Lifely Repository</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>
