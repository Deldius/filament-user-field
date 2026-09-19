<?php

namespace Deldius\UserField;

use Deldius\UserField\Concerns\HasActiveState;
use Deldius\UserField\Concerns\HasAvatar;
use Deldius\UserField\Concerns\HasEmptyState;
use Deldius\UserField\Concerns\HasSize;
use Deldius\UserField\Concerns\HasStackedUsers;
use Deldius\UserField\Concerns\HasState;
use Deldius\UserField\Concerns\HasUserFields;
use Filament\Actions\Action;
use Filament\Infolists\Components\Entry;

class UserEntry extends Entry
{
    use HasActiveState;
    use HasAvatar;
    use HasEmptyState;
    use HasSize;
    use HasStackedUsers;
    use HasState;
    use HasUserFields;

    protected string $view = 'filament-user-field::user-entry';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpStackedUsers();
    }

    public function getAction(string | array | null $name = null): ?Action
    {
        $action = parent::getAction($name);

        if (($name === null) && ($action === $this->getStackedModalAction()) && (! ($this->hasStackedUsers() && $this->hasStackedModal()))) {
            return null;
        }

        return $action;
    }
}
