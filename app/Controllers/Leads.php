<?php

namespace App\Controllers;

use App\Models\LeadModel;
use CodeIgniter\API\ResponseTrait;

class Leads extends BaseController
{
    use ResponseTrait;

    public function subscribe()
    {
        $email = $this->request->getPost('email');
        $province = $this->request->getPost('province');
        $source = $this->request->getPost('source') ?: 'home_search';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Por favor, introduce un correo electrónico válido.');
        }

        $leadModel = new LeadModel();

        try {
            $leadModel->insert([
                'email'    => $email,
                'province' => $province,
                'source'   => $source
            ]);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => '¡Registro completado! Pronto empezarás a recibir las nuevas empresas.'
            ]);
        } catch (\Exception $e) {
            return $this->fail('Lo sentimos, hubo un error al procesar tu solicitud. Inténtalo de nuevo más tarde.');
        }
    }
}
