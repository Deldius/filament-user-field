<?php

namespace Tests\Concerns;

use Closure;
use Deldius\UserField\Concerns\HasStackedUsers;
use Deldius\UserField\UserEntry;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class StackedUser extends Model
{
    protected $guarded = [];
}

class DummyStackedUsersComponent
{
    use HasStackedUsers;

    private ?Action $action = null;

    public function __construct(private mixed $state) {}

    public function getState(): mixed
    {
        return $this->state;
    }

    protected function evaluate(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    public function action(?Action $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getLabel(): string
    {
        return 'Users';
    }
}

beforeEach(fn () => config([
    'user-field.stacked.limit' => 5,
    'user-field.stacked.modal' => false,
]));

it('detects only collection state as stacked', function () {
    expect((new DummyStackedUsersComponent(collect([1])))->isStackedState())->toBeTrue()
        ->and((new DummyStackedUsersComponent(1))->isStackedState())->toBeFalse();
});

it('stacks array state after the real component normalizes it', function () {
    $users = [
        new StackedUser(['id' => 1]),
        new StackedUser(['id' => 2]),
        new StackedUser(['id' => 3]),
    ];
    $component = UserEntry::make('users')->state($users)->stackedLimit(2);

    expect($component->isStackedState())->toBeTrue()
        ->and($component->getStackedUsers()->all())->toBe($users)
        ->and($component->getVisibleStackedUsers()->all())->toBe([$users[0], $users[1]])
        ->and($component->getStackedRemainingCount())->toBe(1);
});

it('uses global and component stack limits', function () {
    $component = new DummyStackedUsersComponent(collect(range(1, 8)));

    expect($component->getStackedLimit())->toBe(5)
        ->and($component->stackedLimit(3)->getStackedLimit())->toBe(3)
        ->and($component->stackedLimit(fn () => 7)->getStackedLimit())->toBe(7);
});

it('keeps the stacked modal disabled by default and supports overrides', function () {
    $component = new DummyStackedUsersComponent(collect([1]));

    expect($component->hasStackedModal())->toBeFalse()
        ->and($component->stackedModal()->hasStackedModal())->toBeTrue()
        ->and($component->stackedModal(false)->hasStackedModal())->toBeFalse();
});

it('uses the global stacked modal setting and evaluates local conditions', function () {
    config(['user-field.stacked.modal' => true]);

    $component = new DummyStackedUsersComponent(collect([1]));

    expect($component->hasStackedModal())->toBeTrue()
        ->and($component->stackedModal(fn () => false)->hasStackedModal())->toBeFalse();
});

it('calculates visible users and the remaining count', function () {
    $component = (new DummyStackedUsersComponent(collect(range(1, 8))))->stackedLimit(5);

    expect($component->getVisibleStackedUsers()->all())->toBe([1, 2, 3, 4, 5])
        ->and($component->getHiddenStackedUsers()->all())->toBe([6, 7, 8])
        ->and($component->getStackedRemainingCount())->toBe(3);
});

it('represents every user in the remaining count for a non-positive limit', function () {
    $component = (new DummyStackedUsersComponent(collect([1, 2])))->stackedLimit(0);

    expect($component->getVisibleStackedUsers())->toBeEmpty()
        ->and($component->getStackedRemainingCount())->toBe(2);
});
