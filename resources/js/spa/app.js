import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#spa-app');

if ('serviceWorker' in navigator) {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.deferredPrompt = e;
        window.dispatchEvent(new CustomEvent('pwa-install-available'));
    });

    window.addEventListener('appinstalled', () => {
        window.deferredPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });
}

export function usePWAInstall() {
    const install = async () => {
        if (!window.deferredPrompt) return false;
        window.deferredPrompt.prompt();
        const { outcome } = await window.deferredPrompt.userChoice;
        window.deferredPrompt = null;
        return outcome === 'accepted';
    };

    return { install };
}
