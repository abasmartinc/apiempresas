<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Datos de Adrián para los correos que redacta /admin/prospeccion (16-10-2026).
 * Cámbialos a tu gusto: la IA los usa para presentarte y firmar.
 */
class Prospeccion extends BaseConfig
{
    /** Cómo te presentas y firmas */
    public string $nombre = 'Adrián';

    /** Tu web o portfolio (sale en la firma) */
    public string $web = 'https://freelance-programador.com';

    /** Lo que ofreces, en una o dos frases. La IA elige de aquí lo que encaje con cada empresa. */
    public string $servicios = 'Desarrollo web y de aplicaciones a medida: webs para negocios, tiendas online, sistemas de reservas y citas, '
        . 'áreas de clientes, paneles de gestión, integraciones entre programas y automatización de tareas repetitivas.';

    /**
     * Lo que sabes hacer, para que la IA decida qué licitaciones te encajan (tecnologías, tipo de
     * proyectos). REVÍSALO: lo rellené yo a partir de lo que conozco de tu trabajo en APIEmpresas.
     */
    public string $tecnologias = 'PHP (CodeIgniter), JavaScript, HTML/CSS, MySQL/MariaDB, Python para datos y automatización, '
        . 'APIs REST e integraciones, scraping y tratamiento de datos, paneles de gestión y aplicaciones web a medida.';

    /** Lo que no haces o no quieres (la IA lo usa para bajar el encaje) */
    public string $noHago = 'Revender licencias o hardware, auditorías y certificaciones (ISO, ENS), formación oficial de fabricantes, '
        . 'redes y sistemas físicos, aplicaciones móviles nativas, proyectos que exijan un producto propio ya implantado en otros clientes.';

    /** Importe anual (sin IVA) que puedes asumir tú solo, más o menos */
    public int $importeMaximo = 60000;

    /** Algo que dé confianza: años de experiencia, tipo de clientes, un caso. Vacío = no se menciona. */
    public string $experiencia = '';

    /** 'tu' o 'usted' */
    public string $tratamiento = 'tu';

    /** Días hasta el recordatorio cuando marcas un correo como enviado */
    public int $diasRecordatorio = 7;
}
