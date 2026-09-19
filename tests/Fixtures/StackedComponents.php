<?php

namespace Deldius\UserField\Tests\Fixtures;

use Deldius\UserField\UserColumn;
use Deldius\UserField\UserEntry;
use Illuminate\Database\Eloquent\Model;

class StackedModalUser extends Model
{
    protected $guarded = [];
}

class TestStackedUserEntry extends UserEntry
{
    public mixed $testState = null;

    public function getState(): mixed
    {
        return $this->testState;
    }
}

class TestStackedUserColumn extends UserColumn
{
    public mixed $testState = null;

    public function getState(): mixed
    {
        return $this->testState;
    }
}
