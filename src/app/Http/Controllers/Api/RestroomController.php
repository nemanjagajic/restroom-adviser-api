<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRestroomRequest;
use App\Models\Restroom;
use App\Models\User\User;
use App\Services\RestroomService;

class RestroomController extends Controller
{
    protected $restroomService;

    public function __construct(RestroomService $restroomService)
    {
        $this->restroomService = $restroomService;
    }

    /**
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom",
     *   summary="Add new restroom",
     *   operationId="getRestrooms",
     *   produces={"application/json"},
     *
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Fetches all restrooms
     * @return array
     */
    public function index() {
        return $this->restroomService->getAll();
    }

    /**
     * @SWG\Post(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom",
     *   summary="Add new restroom",
     *   operationId="addRestroom",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="ex. 1",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="description",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="latitude",
     *     in="formData",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="longitude",
     *     in="formData",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="location_text",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=201, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Create new restroom
     * @param User $user
     * @param CreateRestroomRequest $restroomRequest
     * @return Restroom
     */
    public function store(User $user, CreateRestroomRequest $restroomRequest): Restroom
    {
        $inputData = $restroomRequest->except('images');
        return $this->restroomService->create($user, $inputData, $restroomRequest->only('images'));
    }
}
