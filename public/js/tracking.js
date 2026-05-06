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

    // Only track passive noise (scroll, time) on content-heavy pages like Home or Blog
    const shouldTrackPassive = () => {
        const path = window.location.pathname;
        return path === '/' || path.includes('/blog');
    };

    // 2. Global trackEvent Function
    window.trackEvent = async (eventName, metadata = {}, element = null) => {
        // [V2.1 - GLOBAL BLOCK] Kill noise events on functional pages
        const noiseEvents = ['time_on_page', 'scroll_depth', 'section_view'];
        if (noiseEvents.includes(eventName) && !shouldTrackPassive()) {
            // console.log(`[Tracking] Blocked noise event: ${eventName}`);
            return;
        }

        // Backward compatibility for different signatures
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
            element: element || 'document',
            metadata: metadata
        };

        let baseUrl = (window.ae_base_url || '').replace(/\/$/, '');
        const endpoint = baseUrl + '/api/tracking/event';

        try {
            // Using beacon if available for "fire and forget" on unload, else fetch
            if (navigator.sendBeacon && (eventName === 'time_on_page' || eventName === 'page_unload')) {
                navigator.sendBeacon(endpoint, JSON.stringify(payload));
            } else {
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    keepalive: true
                });
            }
        } catch (e) {
            // Silently fail to not interrupt UX
        }
    };

    // 3. Base Events
    // 3.1 Page View
    trackEvent('page_view', { referrer: document.referrer });

    // 3.2 Scroll Depth (Optimized) - Disabled on functional pages
    let triggeredDepths = new Set();
    const handleScroll = () => {
        if (!shouldTrackPassive()) return;
        
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight;
        const winHeight = window.innerHeight;
        const scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);

        [25, 50, 75, 100].forEach(depth => {
            if (scrollPercent >= depth && !triggeredDepths.has(depth)) {
                triggeredDepths.add(depth);
                trackEvent('scroll_depth', { depth: depth + '%' });
            }
        });
    };
    window.addEventListener('scroll', handleScroll, { passive: true });

    // 3.3 Section View (IntersectionObserver)
    const trackedSections = new Set();
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const sectionId = entry.target.getAttribute('data-track-section');
                if (sectionId && !trackedSections.has(sectionId)) {
                    trackedSections.add(sectionId);
                    trackEvent('section_view', { 
                        section: sectionId,
                        percent_visible: Math.round(entry.intersectionRatio * 100)
                    });
                }
            }
        });
    }, { threshold: [0.5] }); // Trigger when 50% visible

    document.querySelectorAll('[data-track-section]').forEach(el => sectionObserver.observe(el));

    // 3.4 Global Click Listener (Data Attributes)
    document.addEventListener('click', (e) => {
        const target = e.target.closest('[data-track-event]');
        if (target) {
            const eventName = target.getAttribute('data-track-event');
            let metadata = {};
            try {
                const metaAttr = target.getAttribute('data-track-metadata');
                if (metaAttr) metadata = JSON.parse(metaAttr);
            } catch (err) {}

            trackEvent(eventName, metadata, target.tagName + (target.id ? '#' + target.id : ''));
        }

        // Automatic CTA tracking (backward compatibility)
        const cta = e.target.closest('.btn, .cta-link, a[href*="register"], a[href*="pricing"], a[href*="radar"]');
        if (cta && !cta.hasAttribute('data-track-event')) {
            trackEvent('click_cta', {
                text: cta.innerText.trim(),
                href: cta.getAttribute('href'),
                classes: cta.className
            }, cta.tagName + (cta.id ? '#' + cta.id : ''));
        }
    }, { capture: true });

    // 3.5 Time on Page - Disabled on functional pages
    let startTime = Date.now();
    setInterval(() => {
        if (!shouldTrackPassive()) return;
        
        const timeSpent = Math.round((Date.now() - startTime) / 1000);
        if (timeSpent > 0 && (timeSpent === 10 || timeSpent === 30 || timeSpent === 60 || timeSpent % 120 === 0)) {
            trackEvent('time_on_page', { seconds: timeSpent });
        }
    }, 1000);

})();
