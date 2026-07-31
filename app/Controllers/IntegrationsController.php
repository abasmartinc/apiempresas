<?php

namespace App\Controllers;

use App\Models\ApikeysModel;
use App\Models\UserModel;

class IntegrationsController extends BaseController
{
    /** @var UserModel */
    protected $userModel;
    /** @var ApikeysModel */
    protected $ApikeysModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
    }

    public function googleSheets()
    {
        $data = [
            'title' => 'Integración con Google Sheets - APIEmpresas',
            'api_key' => null,
            'is_logged_in' => session('logged_in')
        ];

        if ($data['is_logged_in']) {
            $userId = session('user_id');
            $data['user'] = $this->userModel->find($userId);
            $apiKeyObj = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
            if ($apiKeyObj) {
                $data['api_key'] = $apiKeyObj->api_key;
            }
        }

        return view('integrations/google_sheets', $data);
    }
}
