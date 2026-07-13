import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching';
import { clientsClaim } from 'workbox-core';
import { registerRoute } from 'workbox-routing';
import { CacheFirst, StaleWhileRevalidate } from 'workbox-strategies';
import { ExpirationPlugin } from 'workbox-expiration';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';

cleanupOutdatedCaches();
precacheAndRoute(self.__WB_MANIFEST);

self.skipWaiting();
clientsClaim();

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch {
        data = { title: 'CatatUang', body: event.data.text() };
    }

    const options = {
        body: data.body || '',
        icon: data.icon || '/icons/icon-192.png',
        badge: data.badge || '/icons/icon-192.png',
        tag: data.tag || 'catatuang-notification',
        data: data.data || {},
        actions: [],
        vibrate: [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'CatatUang', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    const urlToOpen = data.urlToOpen || '/spa/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes('/spa') && 'focus' in client) {
                    client.focus();
                    client.postMessage({
                        type: 'PUSH_NOTIFICATION_CLICKED',
                        data: {
                            ...data,
                            clickedAt: Date.now(),
                        },
                    });
                    return;
                }
            }
            return clients.openWindow(urlToOpen).then((client) => {
                if (client) {
                    client.postMessage({
                        type: 'PUSH_NOTIFICATION_CLICKED',
                        data: {
                            ...data,
                            clickedAt: Date.now(),
                        },
                    });
                }
            });
        })
    );
});

registerRoute(
    ({ url }) => url.origin === 'https://fonts.bunny.net',
    new CacheFirst({
        cacheName: 'google-fonts-cache',
        plugins: [
            new ExpirationPlugin({ maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 }),
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    })
);

registerRoute(
    ({ url }) => url.origin === 'https://fonts.googleapis.com',
    new StaleWhileRevalidate({
        cacheName: 'google-fonts-stylesheets',
    })
);
