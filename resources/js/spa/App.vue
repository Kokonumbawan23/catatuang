<template>
    <div class="pb-16 sm:pb-0">
        <nav v-if="authStore.isAuthenticated" class="bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <router-link to="/" class="text-xl font-bold text-indigo-600 dark:text-indigo-400 tracking-tight">CatatUang</router-link>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <router-link
                                to="/"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="isActive('/') ? 'border-indigo-400 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-500'"
                            >
                                Dashboard
                            </router-link>
                            <router-link
                                to="/transactions"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="isActive('/transactions') ? 'border-indigo-400 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-500'"
                            >
                                Transaksi
                            </router-link>
                            <router-link
                                to="/wallets"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="isActive('/wallets') ? 'border-indigo-400 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-500'"
                            >
                                Dompet
                            </router-link>
                            <router-link
                                to="/recurring"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="isActive('/recurring') ? 'border-indigo-400 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-500'"
                            >
                                Berulang
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="toggleDark"
                            class="p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        >
                            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>

                        <div class="relative" x-data="{ open: false }">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-slate-700 hover:text-gray-700 dark:hover:text-white focus:outline-none transition ease-in-out duration-150"
                            >
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ authStore.user?.name?.charAt(0).toUpperCase() }}</span>
                                </div>
                                <div class="hidden sm:block">{{ authStore.user?.name }}</div>
                                <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-if="userMenuOpen"
                                    class="absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
                                >
                                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-slate-800">
                                        <router-link
                                            to="/profile"
                                            @click="userMenuOpen = false"
                                            class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-slate-700 transition"
                                        >
                                            Profile
                                        </router-link>
                                        <button
                                            @click="handleLogout"
                                            class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-slate-700 transition"
                                        >
                                            Keluar
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                </div>
            </div>
        </nav>

        <main class="min-h-screen bg-gray-100 dark:bg-slate-900 mb-16 sm:mb-0">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>

        <div
            v-if="showInstallPrompt && authStore.isAuthenticated"
            class="fixed bottom-20 right-4 z-50 sm:hidden"
        >
            <button
                @click="installPWA"
                class="flex items-center gap-2 px-4 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-medium">Install App</span>
            </button>
        </div>

        <nav v-if="authStore.isAuthenticated" class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)]">
            <div class="flex justify-around items-center h-16">
                <router-link
                    to="/"
                    class="flex flex-col items-center justify-center flex-1 h-full"
                    :class="isActive('/') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isActive('/') ? '2.2' : '1.8'" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                    </svg>
                    <span class="text-[10px] mt-1 font-medium">Dashboard</span>
                </router-link>
                <router-link
                    to="/transactions"
                    class="flex flex-col items-center justify-center flex-1 h-full"
                    :class="isActive('/transactions') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isActive('/transactions') ? '2.2' : '1.8'" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-[10px] mt-1 font-medium">Transaksi</span>
                </router-link>
                <router-link
                    to="/recurring"
                    class="flex flex-col items-center justify-center flex-1 h-full"
                    :class="isActive('/recurring') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="isActive('/recurring') ? '2.2' : '1.8'" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="text-[10px] mt-1 font-medium">Berulang</span>
                </router-link>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from './stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isDark = ref(false);
const userMenuOpen = ref(false);
const showInstallPrompt = ref(false);

const isActive = (path) => {
    if (path === '/') return route.path === '/';
    return route.path.startsWith(path);
};

const toggleDark = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const handleLogout = async () => {
    userMenuOpen.value = false;
    await authStore.logout();
    router.push({ name: 'login' });
};

const installPWA = async () => {
    if (!window.deferredPrompt) return;
    window.deferredPrompt.prompt();
    const { outcome } = await window.deferredPrompt.userChoice;
    if (outcome === 'accepted') {
        showInstallPrompt.value = false;
    }
    window.deferredPrompt = null;
};

const handleInstallAvailable = () => {
    showInstallPrompt.value = true;
};

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    }
    window.addEventListener('pwa-install-available', handleInstallAvailable);
    if (window.deferredPrompt) {
        showInstallPrompt.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('pwa-install-available', handleInstallAvailable);
});
</script>
