<?php

namespace App\Services\User;

use App\Models\CommentLike;
use App\Models\RestroomComment;
use App\Models\RestroomRating;
use App\Models\User\User;
use App\Services\File\FilesService;
use Illuminate\Http\UploadedFile;
use App\Constants\UserConstants;
use App\Types\File\CompressImage;

class UserService {

    private $_filesService;

    /**
     * @param FilesService $filesService
     */
    public function __construct(FilesService $filesService)
    {
        $this->_filesService = $filesService;
    }

    /**
     * Change the password on the user that is passed
     * to the method
     *
     * @param User $user
     * @param string $newPassword
     * @return User
     */
    public function changePassword(User $user, string $newPassword) : User
    {
        $user->password = $newPassword;
        $user->save();

        return $user;
    }

    /**
     * Update user profile information and avatar if it's passed
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $updateData, ?UploadedFile $avatarFile) : User
    {
        $user->update($updateData);

        if (!empty($avatarFile)) {
            $imagePath = $user->avatar ?? UserConstants::formatAvatarPath($user->id);
            $compressImage = new CompressImage(
                $avatarFile,
                UserConstants::AVATAR_WIDTH,
                UserConstants::AVATAR_HEIGHT
            );
            if ($user->avatar) {
                $this->_filesService->removeImage($user->avatar);
            }
            $user->avatar = $this->_filesService->compressAndSaveImage(
                $imagePath,
                $compressImage
            );

            $user->save();
        }

        return $user;
    }

    public function getComments($user, $offset, $limit)
    {
        $comments = RestroomComment::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->offset($offset)->limit($limit)
            ->with('restroom')
            ->get();

        return [
            'comments' => $comments,
            'numberOfComments' => RestroomComment::where('user_id', $user->id)->get()->count()
        ];
    }

    public function getRatings($user, $offset, $limit)
    {
        $ratings = RestroomRating::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->offset($offset)->limit($limit)
            ->with('restroom')
            ->get();

        return [
            'ratings' => $ratings,
            'numberOfRatings' => RestroomRating::where('user_id', $user->id)->get()->count()
        ];
    }

    public function likeComment(User $user, RestroomComment $restroomComment)
    {

        $likes = $this->getLikes($user, $restroomComment);

        if (sizeof($likes) === 0) {
            return CommentLike::create([
                'user_id' => $user->id,
                'restroom_comment_id' => $restroomComment->id
            ]);
        }

        return null;
    }

    public function unlikeComment(User $user, RestroomComment $restroomComment)
    {
        $likes = $this->getLikes($user, $restroomComment);
        $likes[0]->delete();
        return $likes[0];
    }

    public function getLikes(User $user, RestroomComment $restroomComment)
    {
        return CommentLike::where('user_id', $user->id)
            ->where('restroom_comment_id', $restroomComment->id)
            ->get();
    }
}
