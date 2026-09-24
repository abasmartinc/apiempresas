<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MarketingTemplates extends BaseConfig
{
    /**
     * Predefined marketing templates
     */
    public $templates = [
        [
            'slug' => 'api_offer',
            'subject' => '¿Necesitas más potencia? Oferta especial en nuestra API',
            'body' => "Hola {NOMBRE},\n\nHemos visto que estás exprimiendo al máximo nuestra versión gratuita de APIEmpresas (especialmente con {SEARCHES} búsquedas realizadas).\n\nCon el Plan PRO tienes 3.000 consultas cada mes, la dirección completa de cada empresa y el scoring comercial, sin cambiar tu API Key ni tu código.\n\nSi tienes dudas sobre qué plan te encaja, responde a este correo y te ayudamos.\n\nSaludos,\nEl equipo de APIEmpresas."
        ],
        [
            'slug' => 'trial_reminder',
            'subject' => 'Sácale el máximo partido a tu cuenta gratuita',
            'body' => "Hola {NOMBRE},\n\n¿Sabías que, además de las búsquedas web, puedes consultar cualquier empresa por su CIF desde tu propio código con nuestra API?\n\nHemos notado que aún no has probado la integración API. Si necesitas ayuda para empezar, responde a este correo.\n\n¡Un saludo!"
        ],
        [
            'slug' => 'inactive_reengagement',
            'subject' => 'Te echamos de menos en APIEmpresas',
            'body' => "Hola {NOMBRE},\n\nHace tiempo que no te vemos por el panel. Hemos añadido novedades y seguimos incorporando empresas nuevas cada día.\n\nEntra ahora y echa un vistazo a las nuevas empresas registradas: {SITE_URL}\n\n¡Te esperamos!"
        ],
        [
            'slug' => 'custom',
            'subject' => '',
            'body' => ''
        ]
    ];

    /**
     * Get a template by slug
     */
    public function getTemplate($slug)
    {
        foreach ($this->templates as $template) {
            if ($template['slug'] === $slug) {
                return $template;
            }
        }
        return null;
    }
}
