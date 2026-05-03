<?php

namespace App\Libraries;

/**
 * RadarAnalyzer
 * 
 * Provides heuristic-based commercial analysis for B2B leads in the Spanish market.
 * Designed to work with partial data using a multi-layered fallback strategy.
 */
class RadarAnalyzer
{
    /**
     * Entry point: Analyzes a company and returns a structured commercial profile.
     */
    public static function analyze(array $company, int $userId = 0): array
    {
        $profile = self::detectCommercialProfile($company);
        $needs = self::detectProbableNeeds($company, $profile);
        $offers = self::detectFirstOffers($company, $profile);
        $contactStatus = self::detectContactStatus($company);

        // 0. Obtener estado de seguimiento si existe
        $followupInfo = [
            'exists'              => false,
            'status'              => null,
            'notify_when_contact' => false,
            'prepared_at'         => null,
            'message_saved'       => false
        ];

        if ($userId > 0) {
            $fModel = new \App\Models\LeadFollowupModel();
            $mModel = new \App\Models\LeadPreparedMessageModel();
            
            $followupData = $fModel->getFollowup($userId, $company['id']);
            if ($followupData) {
                $followupInfo['exists']              = true;
                $followupInfo['status']              = $followupData['status'];
                $followupInfo['notify_when_contact'] = (bool)$followupData['notify_when_contact'];
                $followupInfo['prepared_at']         = $followupData['prepared_at'];
                
                // Verificar si tiene mensaje guardado
                $msg = $mModel->getMessage($userId, $company['id']);
                $followupInfo['message_saved'] = !empty($msg);
            }
        }
        
        $analysis = [
            'summary'                => self::buildSummary($company, $profile),
            'commercial_profile'     => $profile['label'],
            'needs'                  => $needs,
            'first_offers'           => $offers,
            'sales_approach'         => self::buildSalesApproach($company, $profile, $needs),
            'first_message'          => self::buildFirstMessage($company, $profile, $needs),
            'signals'                => self::buildDetectedSignals($company, $profile),
            // Bloques de acción comercial
            'conversion_probability' => self::detectConversionProbability($company),
            'contact_window'         => self::detectContactWindow($company),
            'estimated_ticket'       => self::detectEstimatedTicket($company, $profile),
            'likely_objection'       => self::detectLikelyObjection($company, $profile),
            'attack_angle'           => self::detectAttackAngle($company, $profile)
        ];

        // Nuevos campos de contacto y oportunidad temprana
        return array_merge($analysis, [
            'contact_status'             => $contactStatus,
            'primary_action'             => self::buildPrimaryAction($analysis, $contactStatus),
            'operational_recommendation' => self::buildOperationalRecommendation($contactStatus),
            'early_opportunity_message'  => self::buildEarlyOpportunityMessage($company, $contactStatus),
            'followup'                   => $followupInfo
        ]);
    }

    /**
     * Step 1: Detect Commercial Profile using CNAE, Social Object, or Company Name.
     */
    private static function detectCommercialProfile(array $company): array
    {
        $text = mb_strtolower(
            ($company['cnae_label'] ?? '') . ' ' . 
            ($company['objeto_social'] ?? '') . ' ' . 
            ($company['company_name'] ?? '')
        );

        $mappings = [
            'tech' => [
                'label' => 'Tecnología / Software / Digital',
                'keywords' => ['software', 'tecnologia', 'digital', 'informatica', 'programacion', 'apps', 'saas', 'sistemas', 'computacion', 'it', 'web', 'data']
            ],
            'consulting' => [
                'label' => 'Consultoría / Servicios B2B',
                'keywords' => ['consultoria', 'asesoramiento', 'expertos', 'servicios profesionales', 'b2b', 'management', 'estrategia', 'marketing', 'publicidad']
            ],
            'construction' => [
                'label' => 'Construcción / Reformas / Instalaciones',
                'keywords' => ['construccion', 'reformas', 'rehabilitacion', 'instalaciones', 'fontaneria', 'electricidad', 'pintor', 'albañil', 'climatizacion', 'obras']
            ],
            'energy' => [
                'label' => 'Energías Renovables / Sostenibilidad',
                'keywords' => ['solar', 'fotovoltaica', 'energias renovables', 'placas solares', 'eolico', 'medio ambiente', 'sostenible', 'eficiencia energetica']
            ],
            'real_estate' => [
                'label' => 'Inmobiliario / PropTech',
                'keywords' => ['inmobiliaria', 'fincas', 'alquiler', 'inmuebles', 'viviendas', 'promocion inmobiliaria', 'real estate']
            ],
            'health' => [
                'label' => 'Salud / Clínica / Estética',
                'keywords' => ['salud', 'clinica', 'odontologia', 'dental', 'fisioterapia', 'estetico', 'gimnasio', 'fitness', 'medico', 'wellness']
            ],
            'hospitality' => [
                'label' => 'Hostelería / Restauración',
                'keywords' => ['hosteleria', 'restaurante', 'cafeteria', 'bar', 'hotel', 'alojamiento', 'gourmet', 'catering', 'comidas']
            ],
            'commerce' => [
                'label' => 'Comercio / Retail / Ecommerce',
                'keywords' => ['comercio', 'retail', 'tienda', 'venta menor', 'boutique', 'mercado', 'tienda online', 'ecommerce', 'shopify', 'prestashop', 'marketplace']
            ],
            'logistics' => [
                'label' => 'Transporte / Logística',
                'keywords' => ['transporte', 'logistica', 'mensajeria', 'mudanzas', 'almacen', 'envios', 'distribucion', 'carga']
            ],
            'legal' => [
                'label' => 'Legal / Fiscal / Laboral',
                'keywords' => ['abogado', 'legal', 'fiscal', 'laboral', 'juridico', 'gestoria', 'despacho', 'leyes']
            ],
        ];

        foreach ($mappings as $key => $data) {
            foreach ($data['keywords'] as $word) {
                if (mb_stripos($text, $word) !== false) {
                    return array_merge($data, ['slug' => $key]);
                }
            }
        }

        // Final Fallback
        return [
            'slug' => 'general',
            'label' => 'Sociedad de reciente creación / Actividad general',
            'keywords' => []
        ];
    }

    /**
     * Step 2: Detect Probable Needs based on Profile and Signal context.
     */
    private static function detectProbableNeeds(array $company, array $profile): array
    {
        $baseNeeds = ['Gestoría / Fiscal / Laboral', 'Software de facturación', 'Seguros de actividad'];
        
        $profileNeeds = [
            'tech' => ['Presencia web premium', 'CRM comercial', 'Captación de talento', 'Infraestructura cloud'],
            'consulting' => ['Branding corporativo', 'Herramientas de productividad', 'Marketing LinkedIn', 'Web profesional'],
            'construction' => ['Marketing local', 'Prevención de riesgos (PRL)', 'Gestión de cobros', 'Web básica'],
            'real_estate' => ['CRM inmobiliario', 'Posicionamiento SEO local', 'Firma digital', 'Fotografía / Video'],
            'health' => ['Software de citas', 'Protección de datos (RGPD)', 'Marketing local', 'Web de servicios'],
            'hospitality' => ['TPV / Sistema de gestión', 'Marketing en redes sociales', 'Reservas online', 'Presencia en Google Maps'],
            'commerce' => ['Ecommerce o Web catálogo', 'TPV / Pagos', 'Branding', 'Control de stock'],
            'logistics' => ['Gestión de flotas', 'Software de rutas', 'Seguros de carga', 'Web informativa'],
            'legal' => ['Software de gestión de expedientes', 'Ciberseguridad', 'Firma electrónica', 'Web de servicios'],
            'general' => ['Página web inicial', 'Email corporativo', 'Asesoramiento legal inicial', 'Imagen de marca básica']
        ];

        $needs = array_merge($profileNeeds[$profile['slug']] ?? $profileNeeds['general'], $baseNeeds);
        return array_slice($needs, 0, 5);
    }

    /**
     * Step 3: Detect First Offers (Prioritized).
     */
    private static function detectFirstOffers(array $company, array $profile): array
    {
        $offers = [
            'tech' => ['Web Corporativa + Email Pro', 'Implementación de CRM', 'Pack de Ciberseguridad'],
            'consulting' => ['Identidad Visual + Web', 'Estrategia de Captación B2B', 'Software de Gestión'],
            'construction' => ['Pack Web + SEO Local', 'Seguros para Autónomos/Pymes', 'Gestión de Nóminas/PRL'],
            'real_estate' => ['Web Inmobiliaria + CRM', 'Gestión de Leads Digitales', 'Tour Virtual / Foto Pro'],
            'health' => ['Web Médica + Reservas', 'Auditoría RGPD', 'Marketing en Buscadores'],
            'hospitality' => ['Página de Reservas + TPV', 'Gestión de Reseñas / Google', 'Pack de Identidad Visual'],
            'commerce' => ['Tienda Online (Prestashop/Shopify)', 'Sistema de Fidelización', 'Branding'],
            'logistics' => ['Web de Seguimiento', 'Asesoría en Seguros', 'Optimización de Rutas'],
            'legal' => ['Digitalización de Despacho', 'Certificados Digitales', 'Web de Servicios Legales'],
            'general' => ['Pack "Puesta en Marcha" (Web+Email+Logo)', 'Gestoría Integral', 'Software de Facturación Cloud']
        ];

        return array_slice($offers[$profile['slug']] ?? $offers['general'], 0, 3);
    }

    /**
     * Step 4: Build Summary based on Profile.
     */
    private static function buildSummary(array $company, array $profile): string
    {
        if ($profile['slug'] !== 'general') {
            return "Empresa identificada en el sector de " . $profile['label'] . " con perfil comercial probable y necesidad de estructura operativa inicial.";
        }

        // Fallback for general
        if (!empty($company['main_act_type'])) {
            return "Sociedad con señales de " . $company['main_act_type'] . " reciente, orientada probablemente a servicios locales u operativos.";
        }

        return "Empresa de reciente creación con actividad aún por definir, pero con perfil activo para soluciones de puesta en marcha.";
    }

    /**
     * Step 5: Build Sales Approach.
     */
    private static function buildSalesApproach(array $company, array $profile, array $needs): string
    {
        $focus = "rapidez de puesta en marcha, imagen profesional y herramientas básicas de gestión";
        if (in_array('CRM comercial', $needs)) $focus = "captación de clientes y automatización comercial";
        if (in_array('Marketing local', $needs)) $focus = "visibilidad en la zona y captación de clientes locales";

        return "Enfocar el primer contacto en " . $focus . ". Evitar tecnicismos innecesarios y priorizar el ahorro de tiempo en esta fase de inicio.";
    }

    /**
     * Step 6: Build First Message.
     */
    private static function buildFirstMessage(array $company, array $profile, array $needs): string
    {
        $name = $company['company_name'] ?? 'vuestra empresa';
        return "Hola, hemos visto que acabáis de constituir " . $name . ". En esta fase inicial, muchas empresas como la vuestra necesitan resolver rápido temas de presencia web, gestión fiscal y captación de clientes. Te escribo porque ayudamos a negocios de " . $profile['label'] . " a arrancar con todo esto de forma muy práctica. ¿Te encajaría comentarlo brevemente?";
    }

    /**
     * Step 7: Build Detected Signals.
     */
    private static function buildDetectedSignals(array $company, array $profile): array
    {
        $signals = [];
        if (!empty($company['main_act_type'])) $signals[] = "Acto: " . $company['main_act_type'];
        if (($company['score_total'] ?? 0) >= 80) $signals[] = "Score de Oportunidad Alto";
        if (!empty($company['priority_level']) && $company['priority_level'] !== 'baja') $signals[] = "Prioridad: " . ucfirst($company['priority_level']);
        if (!empty($company['capital_social_raw'])) $signals[] = "Capital detectado: " . $company['capital_social_raw'];
        if ($profile['slug'] !== 'general') $signals[] = "Perfil detectado: " . $profile['slug'];
        if (!empty($company['municipality'])) $signals[] = "Ubicación: " . $company['municipality'];
        
        $signals[] = "Detectada en Radar recientemente";
        
        return $signals;
    }

    /**
     * Step 8: Detect Conversion Probability.
     */
    private static function detectConversionProbability(array $company): array
    {
        // Re-calculamos usando el nuevo sistema para coherencia en informes IA
        $scoring = \App\Libraries\RadarScoringSystem::calculate($company);
        $score = $scoring['final_score'];

        if ($score >= 85) {
            return [
                'label' => 'Muy Alta',
                'description' => 'Prioridad máxima: Lead con señales BORME inminentes y perfil verificado.'
            ];
        }

        if ($score >= 70) {
            return [
                'label' => 'Alta',
                'description' => 'Alta probabilidad de conversión según señales registrales y perfil.'
            ];
        }

        if ($score >= 50) {
            return [
                'label' => 'Media',
                'description' => 'Probabilidad media: Requiere validación comercial de la necesidad.'
            ];
        }

        return [
            'label' => 'Baja',
            'description' => 'Baja probabilidad actual: Perfil latente o sin señales de urgencia.'
        ];
    }


    /**
     * Step 9: Detect Contact Window.
     */
    private static function detectContactWindow(array $company): array
    {
        $score = $company['score_total'] ?? 0;
        $actType = $company['main_act_type'] ?? '';

        if ($actType === 'Constitución' && $score >= 70) {
            return [
                'label' => '0–7 días',
                'description' => 'Conviene contactar cuanto antes, en fase de arranque'
            ];
        }

        if ($score >= 50) {
            return [
                'label' => '7–15 días',
                'description' => 'El mejor momento comercial suele estar en las primeras semanas'
            ];
        }

        return [
            'label' => '15–30 días',
            'description' => 'Empresa con tiempos de maduración más lentos o señales diferidas'
        ];
    }

    /**
     * Step 10: Detect Estimated Ticket.
     */
    private static function detectEstimatedTicket(array $company, array $profile): array
    {
        $tickets = [
            'tech'         => ['label' => '500€ – 3.000€', 'description' => 'Servicios de alto valor añadido'],
            'consulting'   => ['label' => '500€ – 3.000€', 'description' => 'Servicios recurrentes B2B'],
            'construction' => ['label' => '300€ – 2.500€', 'description' => 'Instalaciones y operativa técnica'],
            'health'       => ['label' => '300€ – 1.500€', 'description' => 'Especialidades y gestión local'],
            'hospitality'  => ['label' => '150€ – 1.200€', 'description' => 'Servicios operativos de proximidad'],
            'commerce'     => ['label' => '150€ – 1.200€', 'description' => 'Retail y servicios transaccionales'],
            'general'      => ['label' => '100€ – 900€', 'description' => 'Servicios generales de inicio']
        ];

        return $tickets[$profile['slug']] ?? $tickets['general'];
    }

    /**
     * Step 11: Detect Likely Objection.
     */
    private static function detectLikelyObjection(array $company, array $profile): string
    {
        if ($profile['slug'] === 'tech' || $profile['slug'] === 'consulting') {
            return 'Ya tenemos a alguien montando esta parte internamente';
        }
        
        if ($profile['slug'] === 'hospitality' || $profile['slug'] === 'commerce') {
            return 'Ahora mismo vamos paso a paso, no queremos contratar nada más';
        }

        if ($profile['slug'] === 'construction') {
            return 'Primero queremos cerrar la parte operativa y luego veremos servicios';
        }

        return 'Todavía estamos arrancando, contactad más adelante';
    }

    /**
     * Step 12: Detect Attack Angle.
     */
    private static function detectAttackAngle(array $company, array $profile): string
    {
        if ($profile['slug'] === 'tech') {
            return 'Enfocar en rapidez de puesta en marcha y escalabilidad técnica';
        }

        if ($profile['slug'] === 'consulting') {
            return 'Enfatizar ahorro de tiempo y profesionalización temprana del equipo';
        }

        return 'Entrar por operativa básica y necesidad inmediata de facturación';
    }

    /**
     * Step 13: Detect Contact Status.
     */
    private static function detectContactStatus(array $company): array
    {
        $hasPhone = !empty($company['phone']) || !empty($company['phone_mobile']);
        // Se asume que company_url_id o url indican presencia de web
        $hasWeb = !empty($company['company_url_id']) || !empty($company['url']);
        $hasEmail = false; // El esquema actual no parece tener email directo aún
        
        $hasAnyContact = $hasPhone || $hasWeb || $hasEmail;

        if ($hasAnyContact) {
            return [
                'has_email'       => $hasEmail,
                'has_phone'       => $hasPhone,
                'has_web'         => $hasWeb,
                'has_any_contact' => true,
                'status_label'    => 'contacto_disponible',
                'status_title'    => 'Contacto disponible',
                'status_message'  => 'Se ha detectado al menos una vía de contacto directa para esta empresa.'
            ];
        }

        return [
            'has_email'       => false,
            'has_phone'       => false,
            'has_web'         => false,
            'has_any_contact' => false,
            'status_label'    => 'sin_contacto',
            'status_title'    => 'Sin contacto detectado todavía',
            'status_message'  => 'No se ha detectado email, teléfono ni web por el momento, pero sigue siendo una oportunidad temprana valiosa.'
        ];
    }

    /**
     * Step 14: Build Primary Action CTA.
     */
    private static function buildPrimaryAction(array $analysis, array $contactStatus): array
    {
        if ($contactStatus['has_any_contact']) {
            return [
                'label' => 'Contactar ahora',
                'mode'  => 'direct_contact'
            ];
        }

        return [
            'label' => 'Preparar contacto',
            'mode'  => 'prepare_contact'
        ];
    }

    /**
     * Step 15: Build Operational Recommendation.
     */
    private static function buildOperationalRecommendation(array $contactStatus): string
    {
        if ($contactStatus['has_any_contact']) {
            return "Acción recomendada: realizar primer contacto en la ventana óptima detectada utilizando el mensaje sugerido.";
        }

        return "Acción recomendada: guardar este lead, preparar el mensaje y activar seguimiento para contactar en cuanto aparezca una vía directa.";
    }

    /**
     * Step 16: Build Early Opportunity Message.
     */
    private static function buildEarlyOpportunityMessage(array $company, array $contactStatus): ?string
    {
        $score = $company['score_total'] ?? 0;
        
        if (!$contactStatus['has_any_contact'] && $score >= 50) {
            return "Lead temprano: aunque todavía no hay contacto visible, es precisamente en esta fase cuando se pueden detectar oportunidades antes que la competencia.";
        }

        return null;
    }
}
