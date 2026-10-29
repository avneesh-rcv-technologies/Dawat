importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// The service worker cannot access page-scoped JS variables directly.
// We initialize the Firebase SDK here using values from site settings.

try {
    firebase.initializeApp({
        apiKey: 'AIzaSyAVc8-C-R7fYirqOSvkHJpaWCDzQqMyFiw',
        authDomain: 'dawat-4fae8.firebaseapp.com',
        projectId: 'dawat-4fae8',
        storageBucket: 'dawat-4fae8.firebasestorage.app',
        messagingSenderId: '984041173301',
        appId: '1:984041173301:web:b1b1c62b3c835a5cbf9c7c',
        measurementId: ''
    });

    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
      console.log('[firebase-messaging-sw.js] Received background message', payload);
      const notificationTitle = payload.notification?.title || payload.data?.title || 'Notification';
      const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: fn_get_setting('general.company.favicon', 0)['url'] || '/public/favicon.ico',
      };

      self.registration.showNotification(notificationTitle, notificationOptions);
    });
} catch (e) {
    console.error('Failed to initialize Firebase in service worker:', e);
}