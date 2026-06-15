<?php

namespace Deldius\UserField\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

trait HasState
{
    const CACHE_KEY_PREFIX = 'user_field_state_';

    public function getState(): mixed
    {
        $state = parent::getState();

        /** @disregard P1009 */
        // @phpstan-ignore class.notFound
        $userModel = config('user-field.user_model.class', User::class);
        $userModelId = config('user-field.user_model.fields.id', 'id');

        if ($state instanceof $userModel) {
            return $state;
        }

        if ($state) {
            $data = Cache::remember(
                key: self::CACHE_KEY_PREFIX . $userModel . '_' . $state,
                ttl: new \DateInterval('PT5S'), // 5 seconds
                callback: function () use ($userModel, $userModelId, $state) {
                    return $userModel::where($userModelId, $state)->first()?->toArray();
                }
            );

            if ($data === null) {
                return null;
            }

            return new $userModel($data);
        }

        return null;
    }
}
