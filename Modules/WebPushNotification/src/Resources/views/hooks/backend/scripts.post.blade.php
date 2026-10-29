<script type="module">
    // Import the functions you need from the SDKs you need
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/12.5.0/firebase-app.js";
        import {
            getAnalytics
        } from "https://www.gstatic.com/firebasejs/12.5.0/firebase-analytics.js";
        // TODO: Add SDKs for Firebase products that you want to use
        // https://firebase.google.com/docs/web/setup#available-libraries

        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        const firebaseConfig = {
            apiKey: '{{ fn_get_setting("firebase_api_key", "") }}',
            authDomain: '{{ fn_get_setting("firebase_auth_domain", "") }}',
            projectId: '{{ fn_get_setting("firebase_project_id", "") }}',
            storageBucket: '{{ fn_get_setting("firebase_storage_bucket", "") }}',
            messagingSenderId: '{{ fn_get_setting("firebase_messaging_sender_id", "") }}',
            appId: '{{ fn_get_setting("firebase_app_id", "") }}',
            measurementId: '{{ fn_get_setting("firebase_measurement_id", "") }}'
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
        // Initialize Firebase Messaging (for Web Push)
        import {
            getMessaging,
            getToken,
            onMessage
        } from "https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging.js";

        const messaging = getMessaging(app);

        // Register service worker at site root so it has scope for the whole origin
        if ('serviceWorker' in navigator) {
            // Register service worker using explicit origin path to avoid issues when app is served on a non-root path or different port
            const swPath = `${window.location.origin}/firebase-messaging-sw.js`;
            navigator.serviceWorker.register(swPath)
                .then(async (registration) => {
                    console.info('Service Worker registered for FCM:', registration.scope);

                    try {
                        // Request permission from the user
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') {
                            console.warn('Notifications permission not granted');
                        } else {
                            // Send firebase config to the service worker so it can initialize (SW runs in its own scope)
                            try {
                                const activeWorker = registration.installing || registration.waiting || registration.active;
                                if (activeWorker) {
                                    activeWorker.postMessage({
                                        type: 'SET_FIREBASE_CONFIG',
                                        config: firebaseConfig
                                    });
                                } else {
                                    // Ensure the active worker is ready and then send config
                                    navigator.serviceWorker.ready.then((reg) => {
                                        reg.active && reg.active.postMessage({
                                            type: 'SET_FIREBASE_CONFIG',
                                            config: firebaseConfig
                                        });
                                    }).catch(() => {});
                                }
                            } catch (e) {
                                console.warn('Unable to send firebase config to SW', e);
                            }

                            // Get token using VAPID key from settings
                            const vapidKey = '{{ fn_get_setting("firebase_vapid_key", "") }}';
                            const currentToken = await getToken(messaging, {
                                vapidKey: vapidKey,
                                serviceWorkerRegistration: registration
                            });

                            if (currentToken) {
                                // Send token to backend to store for the current admin user
                                await fetch('/api/firebase/subscribe', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        fcm_token: currentToken,
                                        device: 'web' 
                                    })
                                }).then(res => res.json()).then(data => {
                                    console.info('FCM token sent to server', data);
                                }).catch(err => {
                                    console.error('Failed to send FCM token to server', err);
                                });

                                // Expose a global helper to unsubscribe
                                window.unsubscribeFromPush = async function() {
                                    try {
                                        await fetch('/api/firebase/unsubscribe', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            },
                                            body: JSON.stringify({
                                                token: currentToken
                                            })
                                        }).then(r => r.json()).then(d => console.info('Unsubscribe response', d)).catch(e => console.error(e));
                                    } catch (e) {
                                        console.error('Unsubscribe error', e);
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.error('FCM setup error:', e);
                    }

                    // Always attempt to refresh token and re-send on each page load (helps with rotation)
                    try {
                        const vapidKey = '{{ fn_get_setting("firebase_vapid_key", "") }}';
                        const refreshedToken = await getToken(messaging, {
                            vapidKey: vapidKey,
                            serviceWorkerRegistration: registration
                        });
                        if (refreshedToken) {
                            // send again in case it changed
                            await fetch('/api/firebase/subscribe', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    fcm_token: refreshedToken,
                                    device_id: navigator.userAgent,
                                    device: "web"
                                })
                            }).then(res => res.json()).then(data => {
                                console.info('Refreshed FCM token sent to server', data);
                            }).catch(err => {
                                console.error('Failed to send refreshed FCM token to server', err);
                            });
                        }
                    } catch (e) {
                        // Not critical
                    }

                    // Handle messages when the page is in the foreground
                    onMessage(messaging, (payload) => {
                        console.info('FCM foreground message', payload);
                        const notif = payload.notification || {};
                        // Optionally show a browser Notification (requires permission)
                        if (Notification.permission === 'granted') {
                            try {
                                new Notification(notif.title || 'Notification', {
                                    body: notif.body || (payload.data && payload.data.body) || '',
                                    icon: notif.icon || '/favicon.ico',
                                    data: payload.data || {}
                                });
                            } catch (e) {
                                console.error('Unable to display notification', e);
                            }
                        }
                    });
                }).catch(err => {
                    console.error('Service Worker registration failed:', err);
                });
        }
</script>