<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserChangePasswordRequest;
use App\Models\Restroom;
use App\Models\User\User;
use App\Services\User\UserService;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UserController extends Controller {

    /**
     * @var UserService $_userService
     */
    private $_userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->_userService = $userService;
    }

    /**
     * @SWG\Post(
     *   tags={"User"},
     *   path="/user/change-password",
     *   summary="Change user password",
     *   operationId="usersChangePassword",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="current_password",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="new_password",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="new_password_confirmation",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     * Change active user password to the new one
     *
     * @param UserChangePasswordRequest $request
     * @return void
     */
    public function changePassword(UserChangePasswordRequest $request)
    {
        $password = $request->get('new_password');

        return $this->_userService->changePassword(
            auth()->user(),
            $password
        );
    }

    /**
     * @SWG\Post(
     *   tags={"User"},
     *   path="/user",
     *   summary="Change user first name, last name and avatar",
     *   operationId="userUpdateProfile",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="first_name",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="avatar",
     *     in="formData",
     *     required=false,
     *     type="file"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Update user profile information and avatar
     *
     * @param UpdateProfileRequest $request
     * @return void
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->_userService->updateProfile(
            auth()->user(),
            Arr::only($request->validated(), [ 'first_name', 'last_name' ]),
            $request->file('avatar')
        );
    }

    /**
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/comments",
     *   summary="Get comments for restroom",
     *   operationId="getRestroomComments",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="ex. 1",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     required=true,
     *     type="number"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Get comments for user
     * @param User $user
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function getComments(User $user, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $comments = $this->_userService->getComments($user, $offset, $limit);
        return response($comments, 200);
    }

    /**
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/ratings",
     *   summary="Get ratings for user",
     *   operationId="getRestroomRatings",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="ex. 1",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     required=true,
     *     type="number"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Get ratings for user
     * @param User $user
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function getRatings(User $user, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $ratings = $this->_userService->getRatings($user, $offset, $limit);
        return response($ratings, 200);
    }
}
