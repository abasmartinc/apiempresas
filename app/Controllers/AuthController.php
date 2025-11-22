<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends ResourceController
{
    /**
     * Handles user login and generates a temporary JWT token.
     *
     * This endpoint authenticates a user based on their username and password.
     * If successful, it returns a JWT token that can be used for authenticated
     * requests to other endpoints. The endpoint is protected by IP filtering
     * and rate limiting to prevent brute-force attacks.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     *
     * @api
     * @route POST /api/v1/login
     * @tags Authentication
     * @summary User login and JWT token generation
     * @description Authenticate users and return a JWT token if the credentials are valid.
     *
     * @requestBody {
     *   "description": "User login credentials",
     *   "required": true,
     *   "content": {
     *     "application/json": {
     *       "schema": {
     *         "type": "object",
     *         "properties": {
     *           "username": {
     *             "type": "string",
     *             "description": "The username of the user"
     *           },
     *           "password": {
     *             "type": "string",
     *             "description": "The user's password"
     *           }
     *         },
     *         "required": ["username", "password"]
     *       }
     *     }
     *   }
     * }
     *
     * @response 200 {
     *   "description": "Successful login",
     *   "content": {
     *     "application/json": {
     *       "schema": {
     *         "type": "object",
     *         "properties": {
     *           "token": {
     *             "type": "string",
     *             "description": "JWT token for authenticated requests"
     *           }
     *         }
     *       }
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "description": "Invalid credentials"
     * }
     *
     * @response 403 {
     *   "description": "Access denied (IP not allowed)"
     * }
     *
     * @response 429 {
     *   "description": "Too many login attempts"
     * }
     *
     * @response 400 {
     *   "description": "No JSON data provided"
     * }
     *
     * @response 500 {
     *   "description": "Internal server error"
     * }
     *
     * @example {
     *   "username": "admin",
     *   "password": "secret123"
     * }
     */
    public function login()
    {
        $allowed_ips = ALLOWED_IPS;
        $client_ip = $this->request->getIPAddress();
        $endpoint = 'login';

        if (!in_array($client_ip, $allowed_ips)) {
            $this->saveLog($client_ip, null, $endpoint, 'Failed', 'Access denied');
            return $this->respond(['message' => 'Access denied'], ResponseInterface::HTTP_FORBIDDEN);
        }

        if ($this->isLockedOut($client_ip)) {
            $this->saveLog($client_ip, null, $endpoint, 'Failed', 'Too many login attempts. Please try again later.');
            return $this->respond(['message' => 'Too many login attempts. Please try again later.'], ResponseInterface::HTTP_TOO_MANY_REQUESTS);
        }

        $json = $this->request->getJSON();

        if (!$json) {
            $this->saveLog($client_ip, null, $endpoint, 'Failed', 'No JSON data provided');
            return $this->respond(['message' => 'No JSON data provided'], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $username = $json->username ?? null;
        $password = $json->password ?? null;


        $apiDB = \Config\Database::connect('api');

        $user = $apiDB->table('users')->where('username', $username)->get()->getRow();

        if(!$user){
            $this->saveLog($client_ip, $username, $endpoint, 'Failed', 'User not valid');
            $this->incrementLoginAttempts($client_ip);
            return $this->respond(['message' => 'Invalid credentials'], ResponseInterface::HTTP_UNAUTHORIZED);
        }
        if($user->active != 1){
            $this->saveLog($client_ip, $username, $endpoint, 'Failed', 'User not active');
            return $this->respond(['message' => 'Invalid credentials'], ResponseInterface::HTTP_UNAUTHORIZED);
        }


        if ($username == $user->username && $password == $user->password) {
            $this->clearLoginAttempts($client_ip);
            $key = getenv('JWT_SECRET');
            $payload = [
                'iss' => '',
                'aud' => '',
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + 3600,
                'data' => [
                    'username' => $username,
                ]
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            $this->saveLog($client_ip, $username, $endpoint, 'OK');

            return $this->respond(['token' => $jwt], ResponseInterface::HTTP_OK);
        }

        $this->incrementLoginAttempts($client_ip);
        $this->saveLog($client_ip, $username, $endpoint, 'Failed', 'Invalid credentials');
        return $this->respond(['message' => 'Invalid credentials'], ResponseInterface::HTTP_UNAUTHORIZED);
    }


    private function isLockedOut($ip_address)
    {
        $apiDB = \Config\Database::connect('api');
        $max_attempts = 5;
        $lockout_time = 15 * 60; // 15 minutos

        $attempt = $apiDB->table('login_attempts')->where('ip_address', $ip_address)->get()->getRow();

        if ($attempt) {
            $elapsed_time = time() - strtotime($attempt->last_attempt);
            if ($attempt->attempts >= $max_attempts && $elapsed_time < $lockout_time) {
                return true;
            }

            if ($elapsed_time >= $lockout_time) {
                $this->clearLoginAttempts($ip_address);
            }
        }

        return false;
    }


    private function incrementLoginAttempts($ip_address)
    {
        $apiDB = \Config\Database::connect('api');
        $attempt = $apiDB->table('login_attempts')->where('ip_address', $ip_address)->get()->getRow();

        if ($attempt) {
            $apiDB->table('login_attempts')->where('ip_address', $ip_address)->update([
                'attempts' => $attempt->attempts + 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        } else {
            $apiDB->table('login_attempts')->insert([
                'ip_address' => $ip_address,
                'attempts' => 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        }
    }


    private function clearLoginAttempts($ip_address)
    {
        $apiDB = \Config\Database::connect('api');
        $apiDB->table('login_attempts')->where('ip_address', $ip_address)->delete();
    }


    private function saveLog($ip_address, $username, $endpoint, $status, $error_message='')
    {
        $apiDB = \Config\Database::connect('api');
        $apiDB->table('logs')->insert([
            'ip_address' => $ip_address,
            'username' => $username,
            'endpoint' => $endpoint,
            'status' => $status,
            'error_message' => $error_message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }




}

