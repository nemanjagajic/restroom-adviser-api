<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRestroomCommentRequest;
use App\Http\Requests\CreateRestroomRatingRequest;
use App\Http\Requests\CreateRestroomRequest;
use App\Http\Requests\GetFeedRestroomsRequest;
use App\Models\Restroom;
use App\Models\User\User;
use App\Services\RestroomService;
use Illuminate\Http\Request;

class RestroomController extends Controller
{
    protected $restroomService;

    public function __construct(RestroomService $restroomService)
    {
        $this->restroomService = $restroomService;
    }


    public function index() {
        return $this->restroomService->getAll();
    }

    public function getFeedRestrooms(User $user, GetFeedRestroomsRequest $request) {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $searchValue = $request->input('searchValue');
        $minimalRating = $request->input('minimalRating');
        $onlyMy = $request->input('onlyMy');
        $onlyBookmarked = $request->input('onlyBookmarked');

        $response = [];
        $restrooms = $this->restroomService->getAllFeedRestrooms(
            $user, $offset, $limit, $searchValue, $minimalRating, $onlyMy, $onlyBookmarked
        );
        $response['restrooms'] = $restrooms;
        $response['totalNumber'] = $this->restroomService->getTotalCount(
            $user,
            $searchValue,
            $minimalRating,
            $onlyMy,
            $onlyBookmarked
        );

        return response($response);
    }

    public function store(User $user, CreateRestroomRequest $restroomRequest)
    {
        $inputData = $restroomRequest->except('images');
        $restroom = $this->restroomService->create($user, $inputData, $restroomRequest->only('images'));
        info($restroom);

        return response($restroom, 201);
    }

    public function addComment(User $user, Restroom $restroom, CreateRestroomCommentRequest $request)
    {
        $comment = $this->restroomService->addComment(
            $user->id,
            $restroom->id,
            $request->input('content')
        );

        return response($comment, 201);
    }

    public function getComments(User $user, Restroom $restroom, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $comments = $this->restroomService->getComments($restroom->id, $offset, $limit);
        return response($comments, 200);
    }

    public function addRating(User $user, Restroom $restroom, CreateRestroomRatingRequest $request)
    {
        $rating = $this->restroomService->addRating(
            $user->id,
            $restroom->id,
            $request->input('rating')
        );

        if (!$rating) {
            return response('You have already rated this restaurant', 403);
        }

        return response($rating, 201);
    }

    public function getRatings(User $user, Restroom $restroom, Request $request)
    {
        $includeRatings = $request->has('includeRatings');
        $ratings = $this->restroomService->getRatings($user->id, $restroom->id, $includeRatings);
        return response($ratings, 200);
    }

    public function bookmarkRestroom(User $user, Restroom $restroom)
    {
        $bookmark = $this->restroomService->bookmarkRestroom($user, $restroom);
        return response($bookmark, 201);
    }

    public function unbookmarkRestroom(User $user, Restroom $restroom)
    {
        $bookmark = $this->restroomService->unbookmarkRestroom($user, $restroom);
        return response($bookmark, 200);
    }

    public function getBookmarks(User $user, Restroom $restroom)
    {
        $bookmarks = $this->restroomService->getBookmarks($user, $restroom);
        return response($bookmarks, 200);
    }

    public function getRestroomValidations(User $user, Restroom $restroom)
    {
        $validations = $this->restroomService->getRestroomValidations($user, $restroom);
        return response($validations, 200);
    }

    public function validateRestroom(User $user, Restroom $restroom)
    {
        $validation = $this->restroomService->validateRestroom($user, $restroom);
        return response($validation, 200);
    }

    public function invalidateRestroom(User $user, Restroom $restroom)
    {
        $validation = $this->restroomService->invalidateRestroom($user, $restroom);
        return response($validation, 200);
    }
}
