<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 sm:hidden z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)]">
    <div class="flex justify-around items-center h-16">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }} transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('dashboard') ? '2.2' : '1.8' }}" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('transactions.index') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('transactions.*') && !request()->routeIs('transactions.create') ? 'text-indigo-600' : 'text-slate-400' }} transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('transactions.*') && !request()->routeIs('transactions.create') ? '2.2' : '1.8' }}" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Transaksi</span>
        </a>
        <a href="{{ route('transactions.create') }}"
           class="flex flex-col items-center justify-center w-full h-full -mt-5">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="text-[10px] mt-1 font-medium text-slate-400">Tambah</span>
        </a>
        <a href="{{ route('recurring-transactions.index') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('recurring-transactions.*') ? 'text-indigo-600' : 'text-slate-400' }} transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('recurring-transactions.*') ? '2.2' : '1.8' }}" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Berulang</span>
        </a>
        <a href="{{ route('profile.edit') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-400' }} transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('profile.*') ? '2.2' : '1.8' }}" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Profil</span>
        </a>
    </div>
</nav>
