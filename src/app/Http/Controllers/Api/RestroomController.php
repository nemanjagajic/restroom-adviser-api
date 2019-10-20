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
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     *   summary="Get all restrooms",
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
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom/feedRestrooms",
     *   summary="Get all restrooms for feed",
     *   operationId="getFeedRestrooms",
     *   produces={"application/json"},
     *
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
     *   @SWG\Parameter(
     *     name="searchValue",
     *     in="query",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="minimalRating",
     *     in="query",
     *     required=false,
     *     type="number"
     *   ),
     *
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Fetches all restrooms
     * @param User $user
     * @param GetFeedRestroomsRequest $request
     * @return array
     */
    public function getFeedRestrooms(User $user, GetFeedRestroomsRequest $request) {
        $offset = $request->input('offset');
        $limit = $request->input('limit');
        $searchValue = $request->input('searchValue');
        $minimalRating = $request->input('minimalRating');

        $response = [];
        $restrooms = $this->restroomService->getAllFeedRestrooms($user, $offset, $limit, $searchValue, $minimalRating);
        $response['restrooms'] = $restrooms;
        if ($minimalRating) {
            $response['totalNumber'] = sizeof($restrooms);
        } else {
            $response['totalNumber'] = $this->restroomService->getTotalCount($searchValue, $minimalRating);
        }

        return response($response);
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
     * @return ResponseFactory|Response
     */
    public function store(User $user, CreateRestroomRequest $restroomRequest)
    {
        $inputData = $restroomRequest->except('images');
        $restroom = $this->restroomService->create($user, $inputData, $restroomRequest->only('images'));
        info($restroom);

        return response($restroom, 201);
    }

    /**
     * @SWG\Post(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom/{restroom_id}/comments",
     *   summary="Add comment for restroom",
     *   operationId="addRestroomComment",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="ex. 1",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="restroom_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="content",
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
     * Adds comment for restroom
     * @param User $user
     * @param Restroom $restroom
     * @param CreateRestroomCommentRequest $request
     * @return ResponseFactory|Response
     */
    public function addComment(User $user, Restroom $restroom, CreateRestroomCommentRequest $request)
    {
        $comment = $this->restroomService->addComment(
            $user->id,
            $restroom->id,
            $request->input('content')
        );

        return response($comment, 201);
    }

    /**
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom/{restroom_id}/comments",
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
     *     name="restroom_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Get comments for restroom
     * @param User $user
     * @param Restroom $restroom
     * @return ResponseFactory|Response
     */
    public function getComments(User $user, Restroom $restroom)
    {
        $comments = $this->restroomService->getComments($restroom->id);
        return response($comments, 200);
    }

    /**
     * @SWG\Post(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom/{restroom_id}/ratings",
     *   summary="Add rating for restroom",
     *   operationId="addRestroomRating",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="ex. 1",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="restroom_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="rating",
     *     in="formData",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=201, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Adds rating for restroom
     * @param User $user
     * @param Restroom $restroom
     * @param CreateRestroomRatingRequest $request
     * @return ResponseFactory|Response
     */
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

    /**
     * @SWG\Get(
     *   tags={"Restroom"},
     *   path="/user/{user_id}/restroom/{restroom_id}/ratings",
     *   summary="Get ratings for restroom",
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
     *     name="restroom_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="includeRatings",
     *     in="query",
     *     required=false,
     *     type="bool"
     *   ),
     *   security={{"authorization_token":{}}},
     *   @SWG\Response(response=200, description="Successful operation"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=422, description="Validation failed"),
     *   @SWG\Response(response=500, description="Internal server error")
     * )
     *
     * Get ratings for restroom
     * @param User $user
     * @param Restroom $restroom
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function getRatings(User $user, Restroom $restroom, Request $request)
    {
        $includeRatings = $request->has('includeRatings');
        $ratings = $this->restroomService->getRatings($user->id, $restroom->id, $includeRatings);
        return response($ratings, 200);
    }
}
