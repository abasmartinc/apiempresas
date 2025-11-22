<?php


namespace Config;

use CodeIgniter\Config\BaseConfig;

class Tenants extends BaseConfig
{
    /**
     * Lista blanca de grupos de BD válidos (deben existir en Config\Database::$defaultGroup/$...).
     * Ejemplos: 'ocs', 'ocs_eu', 'ocs_us', etc.
     */
    public array $allowedDbGroups = [
        'vanessa',
        'virtual',
        'prestige',
        // agrega aquí todos los grupos válidos
    ];

    /**
     * Nombre del header y query param aceptados.
     */
    public string $headerName = 'X-DB-Group';
    public string $queryParam = 'db_group';

    /**
     * Grupo por defecto (opcional). Si lo dejas null, se exigirá explícitamente en cada request.
     */
    public ?string $defaultDbGroup = null;
}
