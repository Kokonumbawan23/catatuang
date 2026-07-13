# 07 - Vue 3 SPA Frontend

## Gambaran Umum

CatatUang memiliki SPA frontend terpisah dibangun dengan Vue 3, Pinia, dan Vue Router. SPA berkomunikasi dengan backend via REST API menggunakan Laravel Sanctum tokens.

## Tech Stack

| Package | Version | Purpose |
|---------|---------|---------|
| Vue | 3.x | UI Framework |
| Pinia | 2.x | State Management |
| Vue Router | 4.x | Routing |
| Axios | 1.x | HTTP Client |

## Project Structure

```
resources/js/spa/
├── App.vue                 # Root component (navigation, bottom tab bar)
├── app.js                  # Vue + Pinia initialization
├── router.js               # Vue Router config with auth guards
├── stores/                 # Pinia stores
│   └── auth.js            # Auth state (user, token, login/logout)
└── pages/                  # Page components
    ├── Login.vue          # Login page
    ├── Register.vue       # Register page
    ├── Dashboard.vue      # Main dashboard with wallet selector
    ├── Wallets.vue        # Wallet CRUD
    ├── Transactions.vue   # Transaction CRUD with analytics
    ├── RecurringTransactions.vue  # Recurring transaction management
    └── Profile.vue        # User profile
```

## App.js Initialization

```javascript
// resources/js/spa/app.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
```

## Router Configuration

```javascript
// resources/js/spa/router.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores/auth'

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
]

const router = createRouter({
  history: createWebHistory('/spa'),
  routes,
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  authStore.initAuth()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
```

## Pinia Stores

### Auth Store

```javascript
// stores/auth.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(email, password) {
      const { data } = await axios.post('/api/auth/login', { email, password })
      this.user = data.user
      this.token = data.token
      localStorage.setItem('token', data.token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
    },

    async register(name, email, password, password_confirmation) {
      const { data } = await axios.post('/api/auth/register', {
        name, email, password, password_confirmation
      })
      this.user = data.user
      this.token = data.token
      localStorage.setItem('token', data.token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
    },

    async logout() {
      await axios.post('/api/auth/logout')
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    },

    async fetchUser() {
      if (!this.token) return
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      const { data } = await axios.get('/api/auth/me')
      this.user = data
    }
  }
})
```

### Wallet Store

```javascript
// stores/wallet.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useWalletStore = defineStore('wallet', {
  state: () => ({
    wallets: [],
    selectedWallet: null,
  }),

  actions: {
    async fetchWallets() {
      const { data } = await axios.get('/api/wallets')
      this.wallets = data
      if (data.length && !this.selectedWallet) {
        this.selectedWallet = data[0]
      }
    },

    async createWallet(walletData) {
      const { data } = await axios.post('/api/wallets', walletData)
      this.wallets.push(data)
      return data
    },

    async updateWallet(id, walletData) {
      const { data } = await axios.put(`/api/wallets/${id}`, walletData)
      const index = this.wallets.findIndex(w => w.id === id)
      if (index !== -1) this.wallets[index] = data
      return data
    },

    async deleteWallet(id) {
      await axios.delete(`/api/wallets/${id}`)
      this.wallets = this.wallets.filter(w => w.id !== id)
    }
  }
})
```

## Page Components

### Dashboard.vue

Features:
- Wallet selector dropdown with "Pilih Dompet" placeholder
- 3-column summary cards (Total Income, Total Expense, Balance)
- Balance alert notification with progress bar
- Recent transactions list

### Wallets.vue

Features:
- Wallet cards with balance display
- Create/Edit/Delete wallet modals
- Balance limit input
- Transaction count per wallet

### Transactions.vue

Features:
- Wallet filter dropdown with "Pilih Dompet" placeholder
- 3-column summary cards (Income, Expense, Balance)
- Transaction form (wallet, type, category, amount, description, date)
- Transaction table with inline edit/delete
- Search functionality
- Monthly filter

### RecurringTransactions.vue

Features:
- Recurring transaction list
- Create/Edit recurring transaction form
- Toggle active/inactive
- Frequency options (daily, weekly, monthly, yearly)

### Profile.vue

Features:
- User name and email display
- Logout functionality

### Login.vue / Register.vue

Features:
- Email and password inputs
- Login/Register with Sanctum token
- Error handling
- Redirect to dashboard when authenticated

## API Integration

### Axios Setup

```javascript
// In auth store after login/register
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

// In app.js boot
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      authStore.logout()
      router.push('/login')
    }
    return Promise.reject(error)
  }
)
```

## Related Files

- `resources/js/spa/app.js` - Vue + Pinia initialization
- `resources/js/spa/router.js` - Vue Router with auth guards
- `resources/js/spa/stores/auth.js` - Auth Pinia store
- `resources/js/spa/pages/*.vue` - All page components
- `resources/js/spa/App.vue` - Root component with navigation
- `resources/views/spa.blade.php` - SPA shell template
