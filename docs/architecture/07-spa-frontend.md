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
├── App.vue                 # Root component
├── app.js                  # Vue initialization
├── router.js               # Vue Router config
├── stores/                 # Pinia stores
│   ├── auth.js            # Auth state
│   └── wallet.js          # Wallet state
└── pages/                  # Page components
    ├── Login.vue          # Login page
    ├── Register.vue       # Register page
    ├── Dashboard.vue      # Main dashboard
    ├── Wallets.vue        # Wallet management
    └── Transactions.vue   # Transaction CRUD
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
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('./pages/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('./pages/Register.vue'),
    meta: { guest: true }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('./pages/Dashboard.vue'),
    meta: { auth: true }
  },
  {
    path: '/wallets',
    name: 'wallets',
    component: () => import('./pages/Wallets.vue'),
    meta: { auth: true }
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: () => import('./pages/Transactions.vue'),
    meta: { auth: true }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.auth && !authStore.token) {
    next('/login')
  } else if (to.meta.guest && authStore.token) {
    next('/dashboard')
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
- Wallet selector dropdown
- 3-column summary cards (In, Out, Balance)
- Recent transactions list
- Balance alert notification
- Balance progress bar

### Wallets.vue

Features:
- Wallet cards with gradient backgrounds
- Create wallet modal
- Edit wallet modal
- Delete confirmation modal
- Balance limit input
- Balance progress bar per wallet

### Transactions.vue

Features:
- 3-column layout (form + summary + table)
- Transaction form with category dropdown
- Inline edit/delete
- Transaction table with category icons
- Delete confirmation modal

### Login.vue

Features:
- Email input
- Password input
- Login button
- Link to register
- Error handling

### Register.vue

Features:
- Name input
- Email input
- Password input
- Password confirmation
- Register button
- Link to login

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

- `resources/js/spa/app.js`
- `resources/js/spa/router.js`
- `resources/js/spa/stores/auth.js`
- `resources/js/spa/stores/wallet.js`
- `resources/js/spa/pages/*.vue`
- `resources/js/spa/App.vue`
