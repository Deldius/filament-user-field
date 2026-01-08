<?php

namespace Deldius\UserField\Concerns;

use Closure;
use Filament\Infolists\Components\ImageEntry;
use Filament\Models\Contracts\HasAvatar as ContractsHasAvatar;

trait HasAvatar
{
    protected string | Closure | null $avatarUrl = null;

    protected bool $showAvatar = true;

    public function showAvatar(bool $show = true): static
    {
        $this->showAvatar = $show;

        return $this;
    }

    public function avatarUrl(string | Closure | null $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getShowAvatar(): bool
    {
        return $this->showAvatar;
    }

    public function getAvatarUrl(): ?string
    {
        if ($this->avatarUrl) {
            return $this->evaluate($this->avatarUrl);
        }

        $user = $this->getState();

        // Check if User implements HasAvatar
        if (
            $user instanceof ContractsHasAvatar
            && $userAvatarUrl = $user->getFilamentAvatarUrl()
        ) {
            return $this->getImageUrl($userAvatarUrl);
        }

        $avatarUrlField = config('user-field.user_model.fields.avatar_url', 'avatar_url');

        return $this->getImageUrl($user->{$avatarUrlField} ?? null);
    }

    public function getImageUrl(?string $state = null): ?string
    {
        if (! $state) {
            return null;
        }

        return ImageEntry::make('tmp')->getImageUrl($state);
    }
}
