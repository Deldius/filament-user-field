<?php

use Deldius\UserField\Tests\Fixtures\StackedModalUser;
use Deldius\UserField\Tests\Fixtures\TestStackedUserEntry;
use Deldius\UserField\UserEntry;
use Filament\Actions\Action;
use Filament\Support\Enums\Size;

require_once __DIR__ . '/Fixtures/StackedComponents.php';

beforeEach(fn () => config(['user-field.stacked.modal' => false]));

it('installs the stacked action from global configuration', function () {
    config(['user-field.stacked.modal' => true]);
    $component = TestStackedUserEntry::make('users');
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction()?->getName())->toBe('viewStackedUsers');
});

it('builds a read-only stacked user action with one entry per user', function () {
    $users = collect([
        new StackedModalUser(['id' => 1, 'name' => 'First']),
        new StackedModalUser(['id' => 2, 'name' => 'Second']),
    ]);

    $component = TestStackedUserEntry::make('users');
    $component->testState = $users;
    $component->stackedModal();

    $entries = $component->getStackedModalEntries();

    expect($component->getAction()?->getName())->toBe('viewStackedUsers')
        ->and($component->getStackedModalAction()?->getModalSubmitAction())->toBeNull()
        ->and($entries)->toHaveCount(2)
        ->and($entries[0])->toBeInstanceOf(UserEntry::class)
        ->and($entries[0]->getState())->toBe($users[0])
        ->and($entries[1]->getState())->toBe($users[1]);
});

it('transfers unevaluated display settings to each modal entry', function () {
    $user = new StackedModalUser([
        'id' => 1,
        'name' => 'First',
        'is_active' => true,
    ]);

    $component = TestStackedUserEntry::make('users')
        ->showAvatar(false)
        ->avatarUrl(fn (StackedModalUser $state): string => "https://example.com/{$state->id}.png")
        ->heading(fn (StackedModalUser $state): string => "User {$state->name}")
        ->description(fn (StackedModalUser $state): string => "ID {$state->id}")
        ->size(fn (StackedModalUser $state): string => $state->id === 1 ? 'base' : 'sm')
        ->showActiveState(fn (StackedModalUser $state): bool => $state->is_active)
        ->isActiveState(fn (StackedModalUser $state): bool => $state->is_active);
    $component->testState = collect([$user]);

    $entry = $component->getStackedModalEntries()[0];

    expect($entry->getShowAvatar())->toBeFalse()
        ->and($entry->getAvatarUrl())->toBe('https://example.com/1.png')
        ->and($entry->getHeading())->toBe('User First')
        ->and($entry->getDescription())->toBe('ID 1')
        ->and($entry->getSize())->toBe(Size::Medium)
        ->and($entry->getShowActiveState())->toBeTrue()
        ->and($entry->getIsActiveState())->toBeTrue();
});

it('hides the generated stacked action unless state is a non-empty collection', function () {
    $component = TestStackedUserEntry::make('users')->stackedModal();
    $component->testState = collect();

    expect($component->getAction())->toBeNull();

    $component->testState = new StackedModalUser(['id' => 1]);

    expect($component->getAction())->toBeNull();
});

it('hides the generated stacked action when the modal condition is disabled', function (bool $global, bool $useClosure) {
    config(['user-field.stacked.modal' => $global]);
    $condition = $useClosure ? fn (): bool => false : false;
    $component = TestStackedUserEntry::make('users')->stackedModal($condition);
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction())->toBeNull();
})->with([
    'local false' => [false, false],
    'false-returning closure' => [false, true],
    'global true with local false' => [true, false],
]);

it('preserves a custom action configured after the stacked modal', function () {
    $customAction = Action::make('custom');
    $component = TestStackedUserEntry::make('users')->stackedModal(false)->action($customAction);
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction())->toBe($customAction);
});
