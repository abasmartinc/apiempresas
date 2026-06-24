<?php

namespace App\Services\Api\Transformers;

interface ClientTransformerInterface
{
    /**
     * Transform the standard company data into the client's custom format.
     *
     * @param array $companyData The raw company data array from CompanyModel.
     * @return array The formatted data as required by the client.
     */
    public function transform(array $companyData): array;
}
