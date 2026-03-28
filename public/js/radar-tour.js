const driver = window.driver.js.driver;

function initRadarTour(force = false) {
    const hasSeenTour = localStorage.getItem('ae_radar_tour_seen');
    
    if (hasSeenTour && !force) return;

    // Helper to check if we are in PRO or SEO
    const isPro = document.querySelector('.ae-radar-page__metrics') !== null;

    const steps = [
        {
            element: isPro ? '.ae-radar-page__topbar' : '.ae-radar-page__pill',
            popover: {
                title: '🚀 Bienvenido al Radar',
                description: isPro ? 'Este es tu panel profesional para monitorizar nuevas empresas en tiempo real.' : 'Estás en el centro de control de nuevas empresas en España. Aquí monitorizamos el BORME a diario para ti.',
                side: "bottom",
                align: 'start'
            }
        },
        {
            element: isPro ? '.ae-radar-page__metrics' : '.ae-radar-page__stats',
            popover: {
                title: '📊 Estadísticas de Impacto',
                description: 'Visualiza rápidamente cuántas empresas se han constituido hoy, esta semana o este mes.',
                side: "bottom",
                align: 'center'
            }
        }
    ];

    // Filters step
    if (document.querySelector('.ae-radar-page__filters')) {
        steps.push({
            element: '.ae-radar-page__filters',
            popover: {
                title: '🔍 Filtros Estratégicos',
                description: 'Encuentra exactamente lo que buscas filtrando por provincia, sector o palabras clave específicas.',
                side: "top",
                align: 'center'
            }
        });
    }

    // Lead list / table
    const resultsSelector = isPro ? '#radar-results-container' : '.ae-radar-page__lead-grid';
    if (document.querySelector(resultsSelector)) {
        steps.push({
            element: resultsSelector,
            popover: {
                title: '🔥 Oportunidades Detectadas',
                description: 'Este es tu listado de prospección con las últimas sociedades registradas.',
                side: "top",
                align: 'center'
            }
        });
    }

    // AI Analysis (PRO only)
    if (isPro) {
        steps.push({
            element: '.ae-radar-page__nav-link[href*="trends"]',
            popover: {
                title: '📈 Análisis de Tendencias',
                description: 'Descubre qué sectores y zonas están creciendo más para anticiparte al mercado.',
                side: "right",
                align: 'center'
            }
        });

        if (document.querySelector('.ae-radar-page__nav-link[href*="favoritos"]')) {
            steps.push({
                element: '.ae-radar-page__nav-link[href*="favoritos"]',
                popover: {
                    title: '⭐ Mis Favoritos',
                    description: 'Guarda las empresas que más te interesen para tenerlas siempre a mano.',
                    side: "right",
                    align: 'center'
                }
            });
        }

        if (document.querySelector('.ae-radar-page__nav-link[href*="kanban"]')) {
            steps.push({
                element: '.ae-radar-page__nav-link[href*="kanban"]',
                popover: {
                    title: '📋 Embudo de Ventas',
                    description: 'Gestiona tus prospectos favoritos y muévelos a través de tu funnel comercial (Kanban).',
                    side: "right",
                    align: 'center'
                }
            });
        }

        if (document.querySelector('.ae-radar-page__nav-link[href*="invoices"]')) {
            steps.push({
                element: '.ae-radar-page__nav-link[href*="invoices"]',
                popover: {
                    title: '🧾 Mis Facturas',
                    description: 'Accede rápidamente a tus facturas y gestiona tu suscripción desde este apartado.',
                    side: "right",
                    align: 'center'
                }
            });
        }
    } else if (document.querySelector('.ae-radar-page__territory-grid')) {
        steps.push({
            element: '.ae-radar-page__territory-grid',
            popover: {
                title: '📍 Hubs de Actividad',
                description: 'Identifica qué provincias están liderando el crecimiento empresarial.',
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
