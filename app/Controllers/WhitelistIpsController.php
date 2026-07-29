<?php

namespace App\Controllers;

use App\Models\WhitelistIpModel;
use CodeIgniter\Controller;

class WhitelistIpsController extends BaseController
{
    protected $whitelistModel;

    public function __construct()
    {
        $this->whitelistModel = new WhitelistIpModel();
    }

    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $ips = $this->whitelistModel->getIpsByUser($userId);

        $data = [
            'title' => 'Lista Blanca de IPs | APIEmpresas',
            'ips' => $ips
        ];

        return view('whitelist_ips/index', $data);
    }

    public function add()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $ipAddress = $this->request->getPost('ip_address');
        $description = $this->request->getPost('description');

        if (empty($ipAddress)) {
            session()->setFlashdata('error', lang('Messages.flash_81'));
            return redirect()->to(site_url('whitelist-ips'));
        }

        // Basic IP validation
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            session()->setFlashdata('error', lang('Messages.flash_82'));
            return redirect()->to(site_url('whitelist-ips'));
        }

        // Check if it already exists
        if ($this->whitelistModel->isIpWhitelisted($userId, $ipAddress)) {
            session()->setFlashdata('error', lang('Messages.flash_83'));
            return redirect()->to(site_url('whitelist-ips'));
        }

        $this->whitelistModel->insert([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'description' => $description
        ]);

        session()->setFlashdata('success', lang('Messages.flash_84'));
        return redirect()->to(site_url('whitelist-ips'));
    }

    public function delete($id)
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        
        $ip = $this->whitelistModel->find($id);
        if ($ip && (int)$ip->user_id === (int)$userId) {
            $this->whitelistModel->delete($id);
            session()->setFlashdata('success', lang('Messages.flash_85'));
        } else {
            session()->setFlashdata('error', lang('Messages.flash_86'));
        }

        return redirect()->to(site_url('whitelist-ips'));
    }
}
