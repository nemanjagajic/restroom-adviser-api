<?php


namespace App\Constants;


use Illuminate\Support\Str;

class RestroomConstants
{
    const RESTROOMS_PATH = '/restrooms/';

    const RESTROOM_EXTENSION = 'jpg';

    const RESTROOM_IMAGE_WIDTH = 300;

    const RESTROOM_IMAGE_HEIGHT = 300;

    /**
     * Format the avatar storage path
     *
     * @param int $restroomId
     * @return string
     */
    public static function formatRestroomImagePath(int $restroomId) : string
    {
        return self::RESTROOMS_PATH.$restroomId.'/'.Str::random().'.'.self::RESTROOM_EXTENSION;
    }
}