import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        currentUser: (state) => state.user,
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/api/auth/login', { email, password });
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async register(name, email, password, password_confirmation) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/api/auth/register', {
                    name, email, password, password_confirmation
                });
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Registration failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await axios.post('/api/auth/logout');
            } catch (error) {
                // Ignore logout errors
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        async fetchUser() {
            if (!this.token) return null;
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/api/auth/me');
                this.user = response.data.user;
                return this.user;
            } catch (error) {
                this.logout();
                return null;
            }
        },

        initAuth() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }
        },
    },
});

export async function subscribePushOnLogin() {
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }
    if (Notification.permission !== 'granted') {
        return;
    }
    try {
        const VAPID_PUBLIC_KEY = import.meta.env.VITE_VAPID_PUBLIC_KEY;
        if (!VAPID_PUBLIC_KEY) return;

        const registration = await navigator.serviceWorker.ready;
        const existing = await registration.pushManager.getSubscription();
        if (existing) return;

        const sub = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        });

        await axios.post('/api/push/subscribe', {
            endpoint: sub.endpoint,
            publicKey: uint8ArrayToBase64(sub.getKey('p256dh')),
            authToken: uint8ArrayToBase64(sub.getKey('auth')),
            contentEncoding: 'aesgcm',
            expirationTime: sub.expirationTime || null,
        });
    } catch (err) {
        console.warn('Push subscription failed:', err);
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function uint8ArrayToBase64(array) {
    return btoa(String.fromCharCode.apply(null, new Uint8Array(array)));
}
