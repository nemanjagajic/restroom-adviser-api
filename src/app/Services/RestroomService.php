<?php


namespace App\Services;

use App\Constants\RestroomConstants;
use App\Models\RestroomComment;
use App\Models\RestroomImage;
use App\Models\User\User;
use App\Models\Restroom;
use App\RestroomRating;
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

    public function getAllFeedRestrooms($offset, $limit)
    {
        return Restroom::offset($offset)->limit($limit)->get();
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

    public function getComments(int $restroomId)
    {
        $comments =  RestroomComment::where('restroom_id', $restroomId)->with('user')->get();
        $commentsReversed = [];
        foreach ($comments as $comment) {
            array_unshift($commentsReversed, $comment);
        }

        return $commentsReversed;
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

    public function getRatings(int $userId, int $restroomId)
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

        return [
            'rating' => $numberOfRatings !== 0 ? $totalRating / $numberOfRatings : 0,
            'ratings' => $ratingsReversed,
            'totalRating' => $totalRating,
            'numberOfRatings' => $numberOfRatings,
            'myRating' => $myRating
        ];
    }
}