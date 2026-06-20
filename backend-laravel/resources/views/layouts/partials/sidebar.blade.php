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
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
                <x-admin.nav-item
                    route="transactions.index"
                    icon="bi bi-receipt"
                    label="Transactions"
                />
                <x-admin.nav-item
                    route="chart-of-accounts.index"
                    icon="bi bi-journal-text"
                    label="Master Chart of Accounts"
                />
                <x-admin.nav-item
                    route="chart-of-account-categories.index"
                    icon="bi bi-tags"
                    label="Master Chart of Account Categories"
                />
            </ul>
        </nav>
    </div>
</aside>
