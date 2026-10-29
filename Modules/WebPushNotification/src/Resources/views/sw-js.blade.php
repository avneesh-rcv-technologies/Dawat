/*
  Service Worker for Firebase Cloud Messaging
  This file is served via a route so we can inject settings from the DB.
  URL: /firebase-messaging-sw.js
*/

// Use the compat build matching the client SDK version (12.5.0) so imports behave in the service worker
importScripts('https://www.gstatic.com/firebasejs/12.5.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "{{ fn_get_setting('firebase_api_key', '') }}",
    authDomain: "{{ fn_get_setting('firebase_auth_domain', '') }}",
    projectId: "{{ fn_get_setting('firebase_project_id', '') }}",
    storageBucket: "{{ fn_get_setting('firebase_storage_bucket', '') }}",
    messagingSenderId: "{{ fn_get_setting('firebase_messaging_sender_id', '') }}",
    appId: "{{ fn_get_setting('firebase_app_id', '') }}",
    measurementId: "{{ fn_get_setting('firebase_measurement_id', '') }}",
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
    try {
        const notification = payload.notification || {};
        const title = notification.title || 'Notification';
        const options = {
            body: notification.body || (payload.data && payload.data.body) || '',
            icon: notification.icon || '/favicon.ico',
            data: payload.data || {}
        };

        self.registration.showNotification(title, options);
    } catch (e) {
        // Fail silently; log to console for debugging
        console.error('Error handling background message', e, payload);
    }
});

// Optional: handle notificationclick to focus/open window
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const clickAction = event.notification?.data?.click_action || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === clickAction && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(clickAction);
            }
        })
    );
});
