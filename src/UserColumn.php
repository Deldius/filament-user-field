<?php

namespace Deldius\UserField;

use Closure;
use Deldius\UserField\Concerns\HasActiveState;
use Deldius\UserField\Concerns\HasAvatar;
use Deldius\UserField\Concerns\HasEmptyState;
use Deldius\UserField\Concerns\HasSize;
use Deldius\UserField\Concerns\HasStackedUsers;
use Deldius\UserField\Concerns\HasState;
use Deldius\UserField\Concerns\HasUserFields;
use Filament\Actions\Action;
use Filament\Tables\Columns\Column;

class UserColumn extends Column
{
    use HasActiveState;
    use HasAvatar;
    use HasEmptyState;
    use HasSize;
    use HasStackedUsers;
    use HasState;
    use HasUserFields;

    protected string $view = 'filament-user-field::user-column';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpStackedUsers();
    }

    public function getAction(): Closure | Action | null
    {
        $action = parent::getAction();

        if (($action === $this->getStackedModalAction()) && ($this->getRecord() !== null) && (! ($this->hasStackedUsers() && $this->hasStackedModal()))) {
            return null;
        }

        return $action;
    }

    protected function getStackedModalEntriesForAction(Action $action): array
    {
        $record = $action->getRecord();

        if ($record === null) {
            return [];
        }

        $this->record($record);

        return $this->getStackedModalEntries();
    }

    protected function getStackedModalActionName(): string
    {
        return "viewStackedUsers-{$this->getName()}";
    }
}
