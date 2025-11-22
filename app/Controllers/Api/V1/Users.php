<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * @OA\Get(
 *     path="/api/v1/users",
 *     tags={"Users"},
 *     summary="Obtener usuarios activos",
 *     @OA\Parameter(
 *         name="com_id",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer"),
 *         description="Filtrar por com_id"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de usuarios",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="username", type="string"),
 *                 @OA\Property(property="com_id", type="integer"),
 *                 @OA\Property(property="role_id", type="integer"),
 *                 @OA\Property(property="active", type="boolean")
 *             )
 *         )
 *     )
 * )
 */
class Users extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $users = $builder->get()->getResult();
        return $this->respond($users, ResponseInterface::HTTP_OK);
    }
}
