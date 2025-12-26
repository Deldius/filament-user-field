<?php

namespace Deldius\UserField\Concerns;

use Illuminate\Support\Facades\Cache;

trait HasState
{
    public function getState(): mixed
    {
        $state = parent::getState();

        /** @disregard P1009 */
        // @phpstan-ignore class.notFound
        $userModel = config('user-field.user_model.class', \App\Models\User::class);
        $userModelId = config('user-field.user_model.fields.id', 'id');

        if ($state instanceof $userModel) {
            return $state;
        }

        if ($state) {
            return Cache::remember(
                $userModel . '_' . $state,
                new \DateInterval('PT5S'), // 5 seconds
                function () use ($userModel, $userModelId, $state) {
                    return $userModel::where($userModelId, $state)->first();
                }
            );
        }

        return null;
    }
}
