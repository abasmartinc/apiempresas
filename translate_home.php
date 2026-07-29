<?php
$file = 'd:/laragon/www/apiempresas/app/Views/home_en_standalone.php';
$content = file_get_contents($file);

// 1. Update partials
$content = str_replace("view('partials/header')", "view('partials/header_en')", $content);
$content = str_replace("view('partials/footer')", "view('partials/footer_en')", $content);
$content = str_replace("view('partials/head')", "view('partials/head', ['title' => 'Spanish Company Data API | spaincompanyapi.com'])", $content);

// 2. Remove Section 4: Dos formas de crecer
$content = preg_replace('/<!-- 4\. DOS FORMAS DE CRECER -->.*?<!-- 5\. BLOQUE RADAR -->/s', "<!-- 5. BLOQUE RADAR -->", $content);

// 3. Remove Section 5: Bloque Radar
$content = preg_replace('/<!-- 5\. BLOQUE RADAR -->.*?<!-- 6\. BLOQUE API -->/s', "<!-- 6. BLOQUE API -->", $content);

// 4. In Section 7 (Comparativa), remove the Radar card
$content = preg_replace('/<div class="comp-card-premium card-radar.*?<\/div>\s*<div class="comp-card-premium card-api/s', '<div class="comp-card-premium card-api', $content);

// Also remove the "Dos formas de acceder..." intro text in Section 7
$content = preg_replace('/<div class="pro-badge pro-badge-blue reveal" style="background: rgba\(37,99,235,0\.05\);.*?<\/div>/s', '', $content);
$content = preg_replace('/<h2 class="reveal delay-1">¿Radar o API\? Elige según cómo trabajas<\/h2>/', '<h2 class="reveal delay-1">Developer-Oriented API</h2>', $content);
$content = preg_replace('/<p class="reveal delay-2">Dos formas de acceder.*?<\/p>/s', '<p class="reveal delay-2">Access our database exclusively via REST API. Perfect for engineering teams building automated validation systems.</p>', $content);

// 5. Remove Radar Pricing Banner (Section 8)
$content = preg_replace('/<div class="reveal delay-3 pricing-radar-banner".*?<\/div>\s*<\/div>\s*<!-- Custom Bonus Banner -->/s', '<!-- Custom Bonus Banner -->', $content);

// 6. Fix "Los planes siguientes corresponden al acceso a la API..." text in Pricing
$content = preg_replace('/<strong style="color: var\(--ae-dark\);">Los planes siguientes corresponden al acceso a la API.*?<\/strong>/s', '', $content);

// 7. General Translations

$translations = [
    'API de empresas para validar CIF y' => 'Spanish Company Data API for',
    '<span class="gradient-text">verificar datos oficiales en España</span>' => '<span class="gradient-text">CIF Validation & B2B Data</span>',
    'Consulta datos oficiales de empresas españolas y accede a nuevas oportunidades de negocio con información basada en Registro Mercantil y BORME. También puedes detectar empresas recién creadas en España antes que tu competencia con el Radar B2B.' => 'Access official data of Spanish companies and integrate business intelligence based on the Mercantile Registry and BORME. Perfect for KYC, KYB, and B2B workflows.',
    'Validar CIF gratis' => 'Validate CIF Free',
    'Ver Radar en acción' => 'View API Docs',
    'getRadarRedirect(\'home_hero\')' => 'site_url(\'documentation\')',
    'Datos empresariales oficiales · Registro Mercantil · BORME' => 'Official Business Data · Mercantile Registry · BORME',
    
    // Search Block
    'Acceso a Base de Datos Oficial' => 'Official Database Access',
    'Validador de CIF y <span class="highlight">Buscador Oficial</span>' => 'CIF Validator & <span class="highlight">Official Search</span>',
    'Valide datos en segundos con conexión directa al Registro Mercantil y BORME.' => 'Validate data in seconds with direct connection to the Mercantile Registry and BORME.',
    'Ej: B12345678 o Nombre de Empresa' => 'Ex: B12345678 or Company Name',
    'Validar empresa ahora' => 'Validate Company Now',
    '¿Buscas oportunidades para prospectar?' => 'Looking for API documentation?',
    'Ver Radar B2B.' => 'View Docs.',
    'getRadarRedirect(\'home_search\')' => 'site_url(\'documentation\')',
    
    // Value Props
    'Datos siempre listos para validar, integrar y automatizar' => 'Data always ready to validate, integrate and automate',
    'Nuestra infraestructura procesa y normaliza datos complejos del BOE y Registro Mercantil para que tú solo tengas que hacer una petición JSON.' => 'Our infrastructure processes and normalizes complex data from the BOE and Mercantile Registry so you only have to make a simple JSON request.',
    'Información actualizada' => 'Updated Information',
    'Monitorizamos fuentes oficiales para reflejar cambios en tiempo real.' => 'We monitor official sources to reflect changes in real time.',
    'Normalización de datos' => 'Data Normalization',
    'Direcciones, códigos postales y CNAE limpios y estandarizados.' => 'Clean and standardized addresses, zip codes and CNAE codes.',
    'Alta disponibilidad' => 'High Availability',
    'Infraestructura escalable capaz de procesar validaciones masivas.' => 'Scalable infrastructure capable of processing massive validations.',
    'Prospección comercial' => 'Automated Workflows',
    'Identifique empresas recién creadas antes que su competencia y priorice sus esfuerzos comerciales con precisión.' => 'Streamline your KYC/KYB processes by verifying company existence and registry details instantly.',
    'Integración vía API' => 'API Integration',
    'Automatice sus flujos internos conectando su CRM o ERP directamente a nuestra base de datos oficial.' => 'Automate your internal workflows by connecting your CRM or ERP directly to our official database.',
    
    // API Block
    'Para Desarrolladores' => 'For Developers',
    'API de empresas para validar, <span class="gradient-text">consultar e integrar datos</span>' => 'Company API to validate, <span class="gradient-text">query and integrate data</span>',
    'Incorpore información empresarial oficial directamente en sus procesos de registro, formularios o aplicaciones internas.' => 'Incorporate official business information directly into your registration processes, forms or internal applications.',
    'Validación automática de CIF en tiempo real' => 'Automatic real-time CIF validation',
    'Información estratégica de nuevas empresas' => 'Strategic information on new companies',
    'Fácil integración vía JSON / REST' => 'Easy integration via JSON / REST',
    'Explorar API' => 'Explore API',
    
    // Pricing
    'Planes transparentes para cualquier volumen' => 'Transparent plans for any volume',
    'Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business con control de consumo y trazabilidad. Sin permanencias, sin costes ocultos.' => 'Start validating CIFs and company names in the Sandbox. When moving to production, scale to Pro/Business with usage control and traceability. No commitments, no hidden costs.',
    'Mensual' => 'Monthly',
    'Anual' => 'Annual',
    'AHORRA' => 'SAVE',
    'Para probar la API' => 'To test the API',
    'Prueba la API con datos reales y valida resultados antes de pasar a producción.' => 'Test the API with real data and validate results before moving to production.',
    'único' => 'once',
    'consultas garantizadas' => 'guaranteed requests',
    'Acceso a endpoint /companies' => 'Access to /companies endpoint',
    'Datos básicos oficiales (CIF, Razón Social, CNAE)' => 'Basic official data (CIF, Name, CNAE)',
    'Sin tarjeta de crédito' => 'No credit card required',
    'Empezar gratis' => 'Start for free',
    'MÁS ELEGIDO' => 'MOST POPULAR',
    'Para automatizar validaciones' => 'To automate validations',
    'La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.' => 'The ideal option for SaaS, ERPs and products that require validation in production.',
    'mes' => 'mo',
    'consultas al mes' => 'requests / month',
    'Datos completos BORME y Actividad' => 'Full BORME and Activity data',
    'Scoring Comercial IA (0-100)' => 'AI Commercial Scoring (0-100)',
    'Listado de Constituciones' => 'List of New Companies',
    'Grafos de Poder Societario' => 'Corporate Power Graphs',
    'Empezar con Pro' => 'Start with Pro',
    'ESCALA' => 'SCALE',
    'Para equipos y alto volumen' => 'For teams and high volume',
    'Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.' => 'Designed for platforms with higher load, critical processes and high availability needs.',
    'Webhooks Push (Notificaciones BORME)' => 'Push Webhooks (BORME Notifications)',
    'IA Predictiva de Oportunidades' => 'Predictive AI Opportunities',
    'Calculadora de Match B2B' => 'B2B Match Calculator',
    'Soporte Prioritario Slack / Email' => 'Priority Slack / Email Support',
    'Empezar con Business' => 'Start with Business',
    
    // Bonus Banner
    'Nuevo Plan a Medida' => 'New Custom Plan',
    '¿Prefieres pagar solo por lo que usas?' => 'Prefer to pay only for what you use?',
    'Diseña tu propio <strong style="color: #0f172a;">Bono de Créditos Prepago</strong>. Paga una sola vez, consúmelo a tu ritmo y consigue descuentos automáticos por volumen.' => 'Design your own <strong style="color: #0f172a;">Prepaid Credit Bonus</strong>. Pay once, use it at your own pace and get automatic volume discounts.',
    'Crear mi Bono Personalizado' => 'Create my Custom Bonus',
    
    // Feature Comparison
    'Comparativa detallada de funciones' => 'Detailed feature comparison',
    'Función / Capacidad' => 'Feature / Capability',
    'Validación y Enriquecimiento' => 'Validation and Enrichment',
    'Valida la existencia de una sociedad y obtén sus datos oficiales (CNAE, Domicilio, Capital).' => 'Validate the existence of a company and obtain its official data (CNAE, Address, Capital).',
    'Ver Respuesta JSON' => 'View JSON Response',
    'Buscador Inteligente' => 'Intelligent Search',
    'Encuentra empresas por nombre o razón social con autocompletado y normalización.' => 'Find companies by name with autocomplete and normalization.',
    'Consulta Múltiple (Batch)' => 'Batch Query',
    'Consulta hasta 100 CIFs en una única petición ahorrando tiempos de red.' => 'Query up to 100 CIFs in a single request, saving network times.',
    'Clasifica empresas por potencial de compra y salud financiera mediante nuestro algoritmo.' => 'Classify companies by purchasing potential and financial health using our algorithm.',
    'Señales Societarias BORME' => 'BORME Corporate Signals',
    'Monitoriza eventos reales: ampliaciones de capital, cambios de administrador y más.' => 'Monitor real events: capital increases, changes in administrators and more.',
    'Historial Actos BORME' => 'BORME Acts History',
    'Consulta todo el historial de actos publicados en el BOE para cualquier empresa española.' => 'Consult the entire history of acts published in the BOE for any Spanish company.',
    
    // Final Call to Action
    '¿Listo para validar empresas en segundos?' => 'Ready to validate companies in seconds?',
    'Si no encuentras la respuesta que buscas, nuestro equipo de expertos está disponible para ayudarte a integrar la API o configurar tu Radar B2B al máximo nivel.' => 'If you can\'t find the answer you\'re looking for, our team of experts is available to help you integrate the API.',
    'Soporte Técnico e Integración' => 'Technical Support & Integration',
    'Contactar con Soporte' => 'Contact Support',
    '¿Dudas? Habla con ventas' => 'Questions? Talk to Sales',
    'Preguntas Frecuentes' => 'Frequently Asked Questions',
    '¿Qué fuentes de datos utilizáis?' => 'What data sources do you use?',
    'Nuestra base de datos se alimenta diariamente de fuentes públicas y oficiales como el Boletín Oficial del Registro Mercantil (BORME), la Agencia Tributaria (AEAT), el Instituto Nacional de Estadística (INE), y la base de datos VIES a nivel europeo.' => 'Our database is fed daily from public and official sources such as the Official Gazette of the Mercantile Registry (BORME), the Tax Agency (AEAT), the National Institute of Statistics (INE), and the VIES database at the European level.',
    '¿Qué diferencia hay entre API y Radar?' => 'What is the difference between the API and Radar?',
    'Radar B2B es una plataforma visual lista para usar, ideal para equipos comerciales que buscan detectar oportunidades temprano. La API es un servicio técnico (endpoints JSON) pensado para que los desarrolladores integren los datos directamente en su propio software (CRM, ERP, procesos de alta).' => 'The API is a technical service (JSON endpoints) designed for developers to integrate data directly into their own software (CRM, ERP, registration processes).',
    '¿Los datos están normalizados?' => 'Is the data normalized?',
    'Sí. Toda la información en bruto (como los CNAE, descripciones de cargos o direcciones postales) pasa por nuestro motor de normalización antes de servirse por la API, garantizando que puedas automatizar tus procesos sin lidiar con errores tipográficos.' => 'Yes. All raw information (such as CNAE, job descriptions or postal addresses) goes through our normalization engine before being served by the API, guaranteeing that you can automate your processes without dealing with typographical errors.',
    '¿Puedo saber si una empresa es de reciente creación?' => 'Can I know if a company is recently created?',
    'Sí, especialmente a través de nuestro producto Radar B2B. Podrás detectar diariamente qué nuevas empresas se han creado en España y filtrarlas por sector o provincia para llegar a ellas antes que tu competencia.' => 'Yes. Through the API you can identify the incorporation date of any Spanish company based on its BORME publication.',
    '¿Cómo funciona el límite de peticiones (Rate Limit)?' => 'How does the Rate Limit work?',
    'Para evitar abusos y garantizar la disponibilidad, el plan Free tiene un límite estricto por IP. Los planes de pago utilizan un Token JWT que te autentica y permite hasta 5 peticiones por segundo. Para volúmenes mayores, puedes contactar con ventas.' => 'To prevent abuse and guarantee availability, the Free plan has a strict limit per IP. Paid plans use a JWT Token that authenticates you and allows up to 5 requests per second. For larger volumes, you can contact sales.',
    'Consulta datos empresariales, intégralos en tu sistema o trabaja oportunidades con Radar B2B.' => 'Query business data and integrate it into your system securely and instantly.',
    'Ver oportunidades' => 'Get Started',
    'getRadarRedirect(\'home_final\')' => 'site_url(\'register\')'
];

foreach ($translations as $es => $en) {
    $content = str_replace($es, $en, $content);
}

file_put_contents($file, $content);
echo "Done";
