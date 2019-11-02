<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserChangePasswordRequest;
use App\Models\Restroom;
use App\Models\RestroomComment;
use App\Models\User\User;
use App\Services\User\UserService;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UserController extends Controller {

    private $_userService;

    public function __construct(UserService $userService)
    {
        $this->_userService = $userService;
    }

    public function changePassword(UserChangePasswordRequest $request)
    {
        $password = $request->get('new_password');

        return $this->_userService->changePassword(
            auth()->user(),
            $password
        );
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->_userService->updateProfile(
            auth()->user(),
            Arr::only($request->validated(), [ 'first_name', 'last_name' ]),
            $request->file('avatar')
        );
    }

    public function getComments(User $user, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $comments = $this->_userService->getComments($user, $offset, $limit);
        return response($comments, 200);
    }

    public function getRatings(User $user, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $ratings = $this->_userService->getRatings($user, $offset, $limit);
        return response($ratings, 200);
    }

    public function likeComment(User $user, RestroomComment $restroomComment)
    {
        $restroomComment = $this->_userService->likeComment($user, $restroomComment);
        return response($restroomComment, 201);
    }

    public function unlikeComment(User $user, RestroomComment $restroomComment)
    {
        $restroomComment = $this->_userService->unlikeComment($user, $restroomComment);
        return response($restroomComment, 200);
    }
}
