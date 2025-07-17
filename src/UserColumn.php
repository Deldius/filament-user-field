<?php

namespace Deldius\UserField;

use Deldius\UserField\Concerns\HasActiveState;
use Deldius\UserField\Concerns\HasAvatar;
use Deldius\UserField\Concerns\HasEmptyState;
use Deldius\UserField\Concerns\HasSize;
use Deldius\UserField\Concerns\HasState;
use Deldius\UserField\Concerns\HasUserFields;
use Filament\Tables\Columns\Column;

class UserColumn extends Column
{
    use HasActiveState;
    use HasAvatar;
    use HasEmptyState;
    use HasSize;
    use HasState;
    use HasUserFields;

    protected string $view = 'filament-user-field::user-entry';
}
