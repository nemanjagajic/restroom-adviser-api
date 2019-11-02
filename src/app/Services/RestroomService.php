<?php


namespace App\Services;

use App\Constants\RestroomConstants;
use App\Models\CommentLike;
use App\Models\RestroomBookmark;
use App\Models\RestroomComment;
use App\Models\RestroomImage;
use App\Models\RestroomValidation;
use App\Models\User\User;
use App\Models\Restroom;
use App\Models\RestroomRating;
use App\Services\File\FilesService;
use App\Types\File\CompressImage;

class RestroomService {
    protected $filesService;

    public function __construct(FilesService $filesService)
    {
        $this->filesService = $filesService;
    }

    public function create(User $user, array $inputData, array $images): Restroom
    {
        $restroom = Restroom::create(array_merge(
            ['user_id' => $user->id],
            $inputData
        ));

        if ($images) {
            foreach ($images['images'] as $image) {
                $compressedImage = new CompressImage(
                    $image,
                    RestroomConstants::RESTROOM_IMAGE_WIDTH,
                    RestroomConstants::RESTROOM_IMAGE_HEIGHT
                );
                $path = $this->filesService->compressAndSaveImage(
                    RestroomConstants::formatRestroomImagePath($restroom->id),
                    $compressedImage
                );

                $this->addImage($restroom->id, $path);
            }
        }

        return $restroom;
    }

    public function getAll()
    {
        return Restroom::with('images')->get();
    }

    public function getAllFeedRestrooms($user, $offset, $limit, $searchValue, $minimalRating, $onlyMy, $onlyBookmarked)
    {
        if ($searchValue === null) $searchValue = '';
        if ($minimalRating === null) $minimalRating = 0;

        $query = Restroom::leftJoin('restroom_ratings', 'restrooms.id', '=', 'restroom_ratings.restroom_id');

        if ($onlyMy === 'true') {
            $query->where('restrooms.user_id', $user->id);
        }

        if ($onlyBookmarked) {
            $bookmarkedRestroomsIds = RestroomBookmark::where('user_id', $user->id)->pluck('restroom_id')->toArray();
            $query->whereIn('restrooms.id', $bookmarkedRestroomsIds);
        }

        $restrooms = $query
            ->select('restrooms.*')
            ->groupBy('restrooms.id')
            ->havingRaw(
                '? = 0 OR COUNT(restroom_ratings.id) > 0 AND SUM(rating) / COUNT(restroom_ratings.id) >= ?',
                [$minimalRating, $minimalRating]
            )
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('location_text', 'like', '%' . $searchValue . '%');
            })
            ->orderBy('updated_at', 'desc')
            ->offset($offset)->limit($limit)->with(['images', 'ratings'])
            ->get();

        $restroomsResponse = [];
        foreach ($restrooms as $restroom) {
            $newRestroom = new Restroom();
            $newRestroom->id = $restroom->id;
            $newRestroom->user_id = $restroom->user_id;
            $newRestroom->name = $restroom->name;
            $newRestroom->latitude = $restroom->latitude;
            $newRestroom->longitude = $restroom->longitude;
            $newRestroom->location_text = $restroom->location_text;
            $newRestroom->working_hours = $restroom->working_hours;
            $newRestroom->rating = $this->calculateTotalRating($restroom->ratings);
            $newRestroom->image = sizeof($restroom->images) > 0 ? $restroom->images[0] : null;
            $newRestroom->created_at = $restroom->created_at;
            $newRestroom->updated_at = $restroom->updated_at;
            array_push($restroomsResponse, $newRestroom);
        }

        return $restroomsResponse;
    }

    public function getTotalCount($user, $searchValue, $minimalRating, $onlyMy, $onlyBookmarked)
    {
        if ($searchValue === null) $searchValue = '';
        if ($minimalRating === null) $minimalRating = 0;

        $query = Restroom::leftJoin('restroom_ratings', 'restrooms.id', '=', 'restroom_ratings.restroom_id');
        if ($onlyMy === 'true') {
            $query->where('restrooms.user_id', $user->id);
        }

        if ($onlyBookmarked) {
            $bookmarkedRestroomsIds = RestroomBookmark::where('user_id', $user->id)->pluck('restroom_id')->toArray();
            $query->whereIn('restrooms.id', $bookmarkedRestroomsIds);
        }

        return $query
            ->select('restrooms.*')
            ->groupBy('restrooms.id')
            ->havingRaw(
                '? = 0 OR COUNT(restroom_ratings.id) > 0 AND SUM(rating) / COUNT(restroom_ratings.id) >= ?',
                [$minimalRating, $minimalRating]
            )
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('location_text', 'like', '%' . $searchValue . '%');
            })
            ->get()
            ->count();
    }


    public function addImage(int $restroomId, string $path)
    {
        RestroomImage::create([
            'restroom_id' => $restroomId,
            'path' => $path
        ]);
    }

    public function addComment(int $userId, int $restroomId, string $content): RestroomComment
    {
        return RestroomComment::create([
            'user_id' => $userId,
            'restroom_id' => $restroomId,
            'content' => $content
        ]);
    }

    public function getComments(int $restroomId, $offset, $limit)
    {
        $comments =  RestroomComment::where('restroom_id', $restroomId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->offset($offset)->limit($limit)
            ->get();

        foreach ($comments as $comment) {
            $numberOfLikes = CommentLike::where('restroom_comment_id', $comment->id)->count();
            $myLikesNumber = CommentLike::where('user_id', auth()->user()->id)
                ->where('restroom_comment_id', $comment->id)
                ->get()
                ->count();

            $comment->numberOfLikes = $numberOfLikes;
            $comment->isLikedByMe = $myLikesNumber !== 0;
        }

        return [
            'comments' => $comments,
            'numberOfComments' => RestroomComment::where('restroom_id', $restroomId)->get()->count()
        ];
    }

    public function addRating(int $userId, int $restroomId, int $rating): RestroomRating
    {
        $foundRestroom = RestroomRating::where('user_id', $userId)
            ->where('restroom_id', $restroomId)->first();

        if ($foundRestroom) {
            $foundRestroom->update(['rating' => $rating]);
            return $foundRestroom;
        };

        return RestroomRating::create([
            'user_id' => $userId,
            'restroom_id' => $restroomId,
            'rating' => $rating
        ]);
    }

    public function getRatings(int $userId, int $restroomId, $includeRatings)
    {
        $ratings = RestroomRating::where('restroom_id', $restroomId)->get();

        $ratingsReversed = [];
        $totalRating = 0;
        $numberOfRatings = 0;
        foreach ($ratings as $rating) {
            $totalRating += $rating->rating;
            $numberOfRatings++;
            array_unshift($ratingsReversed, $rating);
        }

        $myRating = 0;
        $myRatingInfo = RestroomRating::where('user_id', $userId)->where('restroom_id', $restroomId)->get();
        if (sizeof($myRatingInfo) !== 0) {
            $myRating = $myRatingInfo[0]->rating;
        }

        if (!$includeRatings) {
            return [
                'rating' => $numberOfRatings !== 0 ? $totalRating / $numberOfRatings : 0,
                'totalRating' => $totalRating,
                'numberOfRatings' => $numberOfRatings,
                'myRating' => $myRating
            ];
        }

        return [
            'rating' => $numberOfRatings !== 0 ? $totalRating / $numberOfRatings : 0,
            'ratings' => $ratingsReversed,
            'totalRating' => $totalRating,
            'numberOfRatings' => $numberOfRatings,
            'myRating' => $myRating
        ];
    }


    public function calculateTotalRating($ratings)
    {
        $totalRating = 0;
        $numberOfRatings = 0;
        foreach ($ratings as $rating) {
            $totalRating += $rating->rating;
            $numberOfRatings++;
        }

        $totalRating = $numberOfRatings !== 0 ? $totalRating / $numberOfRatings : 0;

        return [
            'totalRating' => $totalRating,
            'numberOfRatings' => $numberOfRatings
        ];
    }

    public function bookmarkRestroom(User $user, Restroom $restroom)
    {

        $bookmarks = $this->getBookmarks($user, $restroom);

        if (sizeof($bookmarks) === 0) {
            return RestroomBookmark::create([
                'user_id' => $user->id,
                'restroom_id' => $restroom->id
            ]);
        }

        return null;
    }

    public function unbookmarkRestroom(User $user, Restroom $restroom)
    {

        $bookmarks = $this->getBookmarks($user, $restroom);
        $bookmarks[0]->delete();
        return $bookmarks[0];
    }

    public function getBookmarks(User $user, Restroom $restroom)
    {
        return RestroomBookmark::where('user_id', $user->id)
            ->where('restroom_id', $restroom->id)
            ->get();
    }

    public function getRestroomValidations(User $user, Restroom $restroom)
    {
        $positive = RestroomValidation::where('restroom_id', $restroom->id)
            ->where('is_existing', true)
            ->get()
            ->count();

        $negative = RestroomValidation::where('restroom_id', $restroom->id)
            ->where('is_existing', false)
            ->get()
            ->count();

        $myValidations = $this->getRestroomValidationsByUser($user, $restroom);

        return [ 'positive' => $positive, 'negative' => $negative, 'myValidations' => $myValidations ];
    }

    public function validateRestroom(User $user, Restroom $restroom)
    {
        $validations = $this->getRestroomValidationsByUser($user, $restroom);

        if (sizeof($validations) === 0) {
            return RestroomValidation::create([
                'user_id' => $user->id,
                'restroom_id' => $restroom->id,
                'is_existing' => true
            ]);
        } else if ($validations[0] && !$validations[0]->is_existing) {
            $validations[0]->update(['is_existing' => true]);
            return $validations[0];
        }

        return null;
    }

    public function invalidateRestroom(User $user, Restroom $restroom)
    {
        $validations = $this->getRestroomValidationsByUser($user, $restroom);

        if (sizeof($validations) === 0) {
            return RestroomValidation::create([
                'user_id' => $user->id,
                'restroom_id' => $restroom->id,
                'is_existing' => false
            ]);
        } else if ($validations[0] && $validations[0]->is_existing) {
            $validations[0]->update(['is_existing' => false]);
            return $validations[0];
        }

        return null;
    }

    private function getRestroomValidationsByUser(User $user, Restroom $restroom)
    {
        return RestroomValidation::where('user_id', $user->id)
            ->where('restroom_id', $restroom->id)
            ->get();
    }
}