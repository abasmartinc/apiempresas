/**
 * Global Tracking System - APIEmpresas
 * Modular and reusable system for user behavior tracking.
 */

(function() {
    const STORAGE_KEY_ANON = 'ae_anon_id';
    const STORAGE_KEY_SESS = 'ae_sess_id';
    
    // 1. Identification
    const getAnonymousId = () => {
        let anonId = localStorage.getItem(STORAGE_KEY_ANON);
        if (!anonId) {
            // Fallback for non-secure contexts (http) where crypto.randomUUID might be missing
            anonId = 'anon_' + (typeof crypto.randomUUID === 'function' 
                ? crypto.randomUUID() 
                : Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15));
            localStorage.setItem(STORAGE_KEY_ANON, anonId);
        }
        return anonId;
    };

    const getSessionId = () => {
        let sessId = sessionStorage.getItem(STORAGE_KEY_SESS);
        if (!sessId) {
            sessId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            sessionStorage.setItem(STORAGE_KEY_SESS, sessId);
        }
        return sessId;
    };

    const userId = window.ae_user_id || null;
    const anonId = getAnonymousId();
    const sessId = getSessionId();

    // 2. Global trackEvent Function
    window.trackEvent = async (eventName, metadata = {}, element = null) => {
        // Backward compatibility for object-based signature
        if (typeof eventName === 'object' && eventName !== null) {
            const obj = eventName;
            eventName = obj.event_name || obj.event_type || 'unknown';
            metadata = { ...obj };
            delete metadata.event_name;
            delete metadata.event_type;
        }

        const payload = {
            event_name: eventName,
            page: window.location.href,
            user_id: userId,
            session_id: sessId,
            anonymous_id: anonId,
            element: element,
            metadata: metadata
        };

        let baseUrl = (window.ae_base_url || '').replace(/\/$/, '');
        const endpoint = baseUrl + '/api/tracking/event';

        try {
            // Using beacon if available for "fire and forget" on unload, else fetch
            if (navigator.sendBeacon && (eventName === 'time_on_page' || eventName === 'page_unload')) {
                navigator.sendBeacon(endpoint, JSON.stringify(payload));
            } else {
                await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    keepalive: true
                });
            }
        } catch (e) {
            console.warn('Tracking failed', e);
        }
    };

    // 3. Base Events
    // 3.1 Page View
    trackEvent('page_view');

    // 3.2 Scroll Depth
    let scrollDepths = [25, 50, 75, 100];
    let triggeredDepths = new Set();
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight;
        const winHeight = window.innerHeight;
        const scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);

        scrollDepths.forEach(depth => {
            if (scrollPercent >= depth && !triggeredDepths.has(depth)) {
                triggeredDepths.add(depth);
                trackEvent('scroll_depth', { depth: depth + '%' });
            }
        });
    }, { passive: true });

    // 3.3 Time on Page
    let startTime = Date.now();
    setInterval(() => {
        const timeSpent = Math.round((Date.now() - startTime) / 1000);
        if (timeSpent % 30 === 0) { // Every 30 seconds
            trackEvent('time_on_page', { seconds: timeSpent });
        }
    }, 1000);

    // 3.4 Click CTA
    document.addEventListener('click', (e) => {
        const cta = e.target.closest('.btn, .cta-link, a[href*="register"], a[href*="pricing"]');
        if (cta) {
            trackEvent('click_cta', {
                text: cta.innerText.trim(),
                href: cta.getAttribute('href'),
                classes: cta.className
            }, cta.tagName + (cta.id ? '#' + cta.id : ''));
        }
    }, { capture: true });

})();
