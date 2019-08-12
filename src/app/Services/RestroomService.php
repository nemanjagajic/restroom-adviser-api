<?php


namespace App\Services;

use App\Models\User\User;
use App\Models\Restroom;

class RestroomService
{
    public function create(User $user, array $inputData): Restroom
    {
        return Restroom::create(array_merge(
            ['user_id' => $user->id],
            $inputData
        ));
    }

    public function getAll() {
        return Restroom::all();
    }
}