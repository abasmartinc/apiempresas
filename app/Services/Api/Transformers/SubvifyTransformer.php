<?php

namespace App\Services\Api\Transformers;

class SubvifyTransformer implements ClientTransformerInterface
{
    public function transform(array $companyData): array
    {
        $db = \Config\Database::connect();
        
        $municipalityName = $companyData['municipality'] ?? '';
        // $provinceName = $companyData['province'] ?? '';
        
        $customMunicipalityId = null;
        $customProvinceId = null;

        if ($municipalityName) {
            try {
                // Buscamos el ID en la tabla importada. 
                // NOTA: Ajusta 'nombre' y 'id_municipio' por los nombres reales de las columnas en tu tabla municipalities_suvify.
                $row = $db->table('municipalities_suvify')
                          ->where('municpality', $municipalityName) // Nótese el typo en la columna 'municpality' de la BD
                          ->get()
                          ->getRow();
                
                if ($row) {
                    $customMunicipalityId = $row->id ?? null;
                    $customProvinceId = $row->id_province ?? null;
                }
            } catch (\Throwable $e) {
                log_message('error', '[SubvifyTransformer] Error al consultar municipalities_suvify: ' . $e->getMessage());
            }
        }
        // Remove specific fields the client doesn't want
        $fieldsToRemove = ['lat', 'lng', 'ai_tags', 'ai_pitch', 'ai_borme_summary', 'updated_at', 'ai_seo_text', 'ai_faqs'];
        foreach ($fieldsToRemove as $field) {
            unset($companyData[$field]);
        }

        // Devolver todos los datos originales, añadiendo (o sobrescribiendo) los campos requeridos
        $companyData['id_municipality_suvify'] = $customMunicipalityId;
        $companyData['id_province_suvify']     = $customProvinceId;

        return $companyData;
    }
}
