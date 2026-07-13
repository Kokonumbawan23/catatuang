<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-slate-900 px-4">
        <div class="mb-6">
            <a href="/" class="flex items-center gap-2">
                <svg viewBox="0 0 48 48" class="w-12 h-12">
                    <circle cx="24" cy="24" r="22" fill="currentColor" class="text-indigo-600 dark:text-indigo-400"/>
                    <path d="M16 24.5l6 6 10-12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
                <span class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">CatatUang</span>
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white dark:bg-slate-800 shadow-md overflow-hidden sm:rounded-lg">
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700 dark:text-slate-200">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                        placeholder="nama@email.com"
                    />
                </div>

                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-700 dark:text-slate-200">Password</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3"
                        placeholder="••••••••"
                    />
                </div>

                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-400 dark:border-red-700 rounded-md">
                    <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <label class="inline-flex items-center">
                        <input v-model="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700">
                        <span class="ml-2 text-sm text-gray-600 dark:text-slate-400">Ingat saya</span>
                    </label>

                    <a class="text-sm text-gray-600 hover:text-gray-900 dark:text-slate-400 dark:hover:text-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="#">
                        Lupa password?
                    </a>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <router-link to="/register" class="text-sm text-gray-600 hover:text-gray-900 dark:text-slate-400 dark:hover:text-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 me-4">
                        Daftar
                    </router-link>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-indigo-500 focus:bg-gray-700 dark:focus:bg-indigo-500 active:bg-gray-900 dark:active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                        {{ loading ? 'Memuat...' : 'Masuk' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const email = ref('');
const password = ref('');
const remember = ref(false);
const loading = ref(false);
const error = ref('');

const router = useRouter();
const authStore = useAuthStore();

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        await authStore.login(email.value, password.value);
        router.push({ name: 'dashboard' });
    } catch (e) {
        error.value = e.response?.data?.message || 'Email atau kata sandi salah.';
    } finally {
        loading.value = false;
    }
};
</script>
