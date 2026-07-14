import { ref } from 'vue';
import axios from 'axios';

const VAPID_PUBLIC_KEY = import.meta.env.VITE_VAPID_PUBLIC_KEY || '';

export function usePushNotification() {
    const isSupported = ref('Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window);
    const permission = ref(Notification?.permission || 'default');
    const isSubscribed = ref(false);
    const subscription = ref(null);

    async function checkSubscription() {
        if (!isSupported.value) return false;
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        subscription.value = sub;
        isSubscribed.value = !!sub;
        return !!sub;
    }

    async function subscribe() {
        if (!isSupported.value) throw new Error('Push notifications not supported.');

        if (permission.value === 'default') {
            const result = await Notification.requestPermission();
            permission.value = result;
            if (result !== 'granted') throw new Error('Notification permission denied.');
        }

        if (permission.value !== 'granted') throw new Error('Notification permission not granted.');

        const reg = await navigator.serviceWorker.ready;

        const appServerKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY);
        console.log('[Push] VAPID key length:', appServerKey.length, 'bytes');
        console.log('[Push] VAPID key bytes:', Array.from(appServerKey));

        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: appServerKey,
        });

        await axios.post('/api/push/subscribe', {
            endpoint: sub.endpoint,
            publicKey: sub.getKey('p256dh') ? uint8ArrayToBase64(sub.getKey('p256dh')) : null,
            authToken: sub.getKey('auth') ? uint8ArrayToBase64(sub.getKey('auth')) : null,
            contentEncoding: 'aesgcm',
            expirationTime: sub.expirationTime || null,
        });

        subscription.value = sub;
        isSubscribed.value = true;
    }

    async function unsubscribe() {
        if (!subscription.value) return;

        const sub = subscription.value;
        await axios.delete('/api/push/unsubscribe', {
            data: { endpoint: sub.endpoint },
        });

        await sub.unsubscribe();
        subscription.value = null;
        isSubscribed.value = false;
    }

    async function testNotification() {
        await axios.post('/api/push/test');
    }

    function uint8ArrayToBase64(array) {
        return btoa(String.fromCharCode.apply(null, new Uint8Array(array)));
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

    return {
        isSupported,
        permission,
        isSubscribed,
        subscription,
        checkSubscription,
        subscribe,
        unsubscribe,
        testNotification,
    };
}
