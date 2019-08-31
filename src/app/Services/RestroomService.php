<?php


namespace App\Services;

use App\Constants\RestroomConstants;
use App\Models\RestroomComment;
use App\Models\RestroomImage;
use App\Models\User\User;
use App\Models\Restroom;
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
}