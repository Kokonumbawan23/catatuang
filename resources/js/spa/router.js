import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('./pages/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('./pages/Register.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('./pages/Dashboard.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/wallets',
        name: 'wallets',
        component: () => import('./pages/Wallets.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/transactions',
        name: 'transactions',
        component: () => import('./pages/Transactions.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('./pages/Profile.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/recurring',
        name: 'recurring',
        component: () => import('./pages/RecurringTransactions.vue'),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory('/spa'),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    authStore.initAuth();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
