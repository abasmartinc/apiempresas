const driver = window.driver.js.driver;

function initRadarTour(force = false) {
    const hasSeenTour = localStorage.getItem('ae_radar_tour_seen');
    
    if (hasSeenTour && !force) return;

    // Helper to check if we are in PRO or SEO
    const isPro = document.querySelector('.ae-radar-page__metrics') !== null;

    const steps = [
        {
            element: '.ae-radar-page__topbar',
            popover: {
                title: '🚀 Tu Centro de Inteligencia',
                description: 'Bienvenido al Radar PRO. Aquí monitorizamos el BORME a diario para entregarte las mejores oportunidades de negocio antes que a nadie.',
                side: "bottom",
                align: 'start'
            }
        }
    ];

    // 1. Daily Nudge
    if (document.querySelector('.ae-radar-page__daily-nudge')) {
        steps.push({
            element: '.ae-radar-page__daily-nudge',
            popover: {
                title: '📅 Tu Selección Diaria',
                description: 'Cada mañana, nuestro algoritmo selecciona las empresas recién constituidas que tienen más probabilidad de necesitar tus servicios hoy mismo.',
                side: "bottom",
                align: 'center'
            }
        });
    }

    // 2. ROI / Pipeline
    if (document.querySelector('.ae-radar-page__roi-box')) {
        steps.push({
            element: '.ae-radar-page__roi-box',
            popover: {
                title: '💰 Potencial Económico',
                description: 'Basándonos en los leads detectados, calculamos el valor de negocio que podrías captar. ¡Solo un cliente puede pagar años de suscripción!',
                side: "right",
                align: 'center'
            }
        });
    }

    // 3. CRM Progress
    if (document.querySelector('.ae-radar-page__crm-progress')) {
        steps.push({
            element: '.ae-radar-page__crm-progress',
            popover: {
                title: '📊 Control de tu Embudo',
                description: 'Visualiza rápidamente cuántas oportunidades has contactado y cuánto valor tienes pendiente de cierre en tu pipeline.',
                side: "bottom",
                align: 'center'
            }
        });
    }

    // 4. Strategic Intelligence
    if (document.querySelector('.ae-radar-page__intel-stats')) {
        steps.push({
            element: '.ae-radar-page__intel-stats',
            popover: {
                title: '🧠 Inteligencia Predictiva',
                description: 'Analizamos cada empresa para decirte cuáles están en su mejor momento de contacto. No pierdas tiempo con leads fríos.',
                side: "top",
                align: 'center'
            }
        });
    }

    // 5. Top Picks
    if (document.querySelector('.ae-radar-page__top-picks')) {
        steps.push({
            element: '.ae-radar-page__top-picks',
            popover: {
                title: '🎯 Empieza por estos 3',
                description: 'Si tienes poco tiempo, nuestro sistema te recomienda las 3 mejores opciones para contactar de inmediato y maximizar tu éxito.',
                side: "top",
                align: 'center'
            }
        });
    }

    // 6. Global Results & Filters
    if (document.querySelector('.ae-radar-page__filters')) {
        steps.push({
            element: '.ae-radar-page__filters',
            popover: {
                title: '🔍 Filtros Avanzados',
                description: 'Segmenta por provincia, sector (CNAE) o capital social para encontrar el cliente ideal para tu producto.',
                side: "top",
                align: 'center'
            }
        });
    }

    const driverObj = driver({
        showProgress: true,
        steps: steps,
        nextBtnText: 'Siguiente →',
        prevBtnText: '← Anterior',
        doneBtnText: '¡Entendido!',
        progressText: 'Paso {{current}} de {{total}}',
        onDestroyStarted: () => {
            if (!driverObj.hasNextStep()) {
                localStorage.setItem('ae_radar_tour_seen', 'true');
            }
            driverObj.destroy();
        }
    });

    driverObj.drive();
}

document.addEventListener('DOMContentLoaded', () => {
    // Add event listener to any "Ver Tour" buttons
    const tourButtons = document.querySelectorAll('.js-start-radar-tour');
    tourButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            initRadarTour(true);
        });
    });

    // Auto-start if not seen
    setTimeout(() => {
        initRadarTour();
    }, 1500);
});
