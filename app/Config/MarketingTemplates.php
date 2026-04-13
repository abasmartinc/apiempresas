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
            'body' => "Hola {NOMBRE},\n\nHemos visto que estás exprimiendo al máximo nuestra versión gratuita de APIEmpresas (especialmente con {SEARCHES} búsquedas realizadas).\n\nQueremos ofrecerte un descuento del 20% en tu primer mes del Plan PRO para que puedas automatizar tus procesos sin límites.\n\nUsa el código: APILOVER20\n\nSaludos,\nEl equipo de APIEmpresas."
        ],
        [
            'slug' => 'trial_reminder',
            'subject' => 'Sácale el máximo partido a tu cuenta gratuita',
            'body' => "Hola {NOMBRE},\n\n¿Sabías que además de búsquedas web, nuestra API permite descargar balances detallados?\n\nHemos notado que aún no has probado la integración API. Si necesitas ayuda para empezar, responde a este correo.\n\n¡Un saludo!"
        ],
        [
            'slug' => 'inactive_reengagement',
            'subject' => 'Te echamos de menos en APIEmpresas',
            'body' => "Hola {NOMBRE},\n\nHace tiempo que no te vemos por el panel. Han cambiado muchas cosas y ahora la búsqueda es un 40% más rápida.\n\nEntra ahora y echa un vistazo a las nuevas empresas registradas: {SITE_URL}\n\n¡Te esperamos!"
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
