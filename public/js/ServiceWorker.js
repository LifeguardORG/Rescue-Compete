// Service Worker für die Fragenformular-App
const CACHE_NAME = 'form-cache-v1';
const urlsToCache = [
    '/',
    '/css/FormViewStyling.css',
    '/css/Colors.css',
    '/css/Navbar.css',
    '/js/FormViewScript.js',
    '/assets/images/logos/ww-favicon.ico',
    '/assets/images/logos/ww-rundlogo.png'
];

// Service Worker installieren und URLs cachen
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Geöffneter Cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Ressourcenanfragen abfangen und bei Netzwerkfehlern aus dem Cache bedienen
self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Cache-Hit - Rückgabe der Ressource aus dem Cache
                if (response) {
                    return response;
                }

                // Cache-Miss - Anfrage klonen und an Netzwerk weiterleiten
                var fetchRequest = event.request.clone();

                return fetch(fetchRequest).then(
                    function(response) {
                        // Prüfen, ob gültige Antwort
                        if(!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Response klonen für Cache und Browser
                        var responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                // Ressource im Cache speichern
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                ).catch(function() {
                    // Wenn Netzwerkanfrage fehlschlägt, leere Seite anzeigen
                    // oder eine spezielle Offline-Seite bereitstellen
                    return new Response('Offline-Modus: Keine Netzwerkverbindung verfügbar.');
                });
            })
    );
});

// Event für Formular-Übermittlung bei Wiederherstellung der Netzwerkverbindung
self.addEventListener('sync', function(event) {
    if (event.tag === 'submit-form') {
        event.waitUntil(submitPendingForms());
    }
});

// Funktion zum Absenden gespeicherter Formulardaten
async function submitPendingForms() {
    // Hier würden wir aus dem Cache oder IndexedDB gespeicherte Formulardaten abrufen
    // und an den Server senden, wenn die Verbindung wiederhergestellt wurde
}

// Aktivieren des Service Workers und Löschen alter Cache-Versionen
self.addEventListener('activate', function(event) {
    var cacheWhitelist = [CACHE_NAME];

    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});