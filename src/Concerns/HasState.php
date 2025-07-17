<?php

namespace Deldius\UserField\Concerns;

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
            return $userModel::where($userModelId, $state)->first();
        }

        return null;
    }
}
