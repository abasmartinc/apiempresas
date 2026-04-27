<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SectorContent extends BaseConfig
{
    public $sectors = [
        'hosteleria' => [
            'label' => 'Hostelería, Restaurantes y Catering',
            'needs' => 'captación de clientes, reservas, presencia online y marketing local',
            'services' => 'marketing digital, software de reservas, TPV, proveedores alimentarios, gestorías y servicios de limpieza',
            'pain' => 'alta competencia local, necesidad de visibilidad y captación rápida de clientes',
            'opportunity' => 'momento de alta rotación comercial donde detectar el negocio antes que otros es la clave',
            'buyer_intent' => 'negocios en fase crítica de lanzamiento que necesitan soluciones operativas inmediatas'
        ],

        'construccion' => [
            'label' => 'Construcción e Inmobiliaria',
            'needs' => 'proveedores, licencias, seguros, maquinaria, financiación y captación de proyectos',
            'services' => 'asesoría técnica, prevención de riesgos, seguros, financiación, maquinaria, materiales y servicios comerciales',
            'pain' => 'control de costes, gestión de plazos, cumplimiento normativo y necesidad de proveedores fiables',
            'opportunity' => 'proyectos de alto ticket detectables antes de que consoliden su red de proveedores',
            'buyer_intent' => 'empresas que necesitan asegurar su estructura operativa y financiera desde el primer día'
        ],

        'software' => [
            'label' => 'Tecnología y Software',
            'needs' => 'captación B2B, desarrollo comercial, financiación, cloud, automatización y herramientas de crecimiento',
            'services' => 'marketing B2B, CRM, cloud hosting, ciberseguridad, analítica, automatización y consultoría tecnológica',
            'pain' => 'captación inicial de clientes, diferenciación competitiva y escalabilidad',
            'opportunity' => 'crecimiento rápido donde la ventaja competitiva se gana por timing en la prospección',
            'buyer_intent' => 'empresas escalables que buscan sus primeros partners tecnológicos estratégicos'
        ],

        'marketing' => [
            'label' => 'Marketing y Publicidad',
            'needs' => 'captación de clientes, portfolio, automatización comercial y herramientas de reporting',
            'services' => 'CRM, diseño web, herramientas de email marketing, analítica, software de gestión y prospección B2B',
            'pain' => 'alta competencia, necesidad de diferenciarse y generación constante de oportunidades',
            'opportunity' => 'negocios de alta rotación detectables antes de que su competencia los identifique',
            'buyer_intent' => 'empresas que necesitan acelerar su tracción comercial de forma inmediata'
        ],

        'default' => [
            'label' => '{{sector}}',
            'needs' => 'gestión, digitalización, captación de clientes, cumplimiento normativo y expansión comercial',
            'services' => 'asesoría fiscal y contable, desarrollo web, marketing digital, software de gestión, seguros, financiación y servicios legales',
            'pain' => 'necesidad de organizar procesos, captar clientes y operar de forma eficiente desde el inicio',
            'opportunity' => 'ventana de oportunidad temprana donde todavía no han sido contactadas por otros proveedores',
            'buyer_intent' => 'empresas recién creadas que buscan establecer su primer ecosistema de soluciones operativas'
        ]
    ];

    public $provinceContext = [
        'MADRID' => 'MADRID concentra una parte importante de la actividad empresarial nacional, lo que aumenta la competencia pero también el volumen de oportunidades.',
        'BARCELONA' => 'BARCELONA destaca por su ecosistema empresarial dinámico y por la presencia de negocios orientados a servicios, tecnología y comercio.',
        'VALENCIA' => 'VALENCIA combina actividad comercial, servicios y crecimiento empresarial, convirtiéndose en una plaza interesante para la prospección B2B.',
        'default' => '{{provincia}} ofrece oportunidades comerciales para proveedores que buscan contactar con empresas en etapas tempranas.'
    ];

    public $introVariants = [
        "El sector {{sector_label}} en {{provincia}} continúa mostrando actividad empresarial relevante.",
        "Las nuevas empresas de {{sector_label}} en {{provincia}} reflejan oportunidades comerciales en fase temprana.",
        "La creación de empresas de {{sector_label}} en {{provincia}} muestra señales de movimiento y demanda en el mercado local."
    ];
}
