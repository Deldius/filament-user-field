<?php

use Deldius\UserField\Tests\Fixtures\StackedModalUser;
use Deldius\UserField\Tests\Fixtures\TestStackedUserColumn;
use Deldius\UserField\UserEntry;
use Filament\Actions\Action;

require_once __DIR__ . '/Fixtures/StackedComponents.php';

beforeEach(fn () => config(['user-field.stacked.modal' => false]));

it('installs the stacked action from global configuration', function () {
    config(['user-field.stacked.modal' => true]);
    $component = TestStackedUserColumn::make('users');
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction()?->getName())->toBe('viewStackedUsers-users');
});

it('builds a read-only stacked user action with one entry per user', function () {
    $users = collect([
        new StackedModalUser(['id' => 1, 'name' => 'First']),
        new StackedModalUser(['id' => 2, 'name' => 'Second']),
    ]);

    $component = TestStackedUserColumn::make('users');
    $component->testState = $users;
    $component->stackedModal();

    $entries = $component->getStackedModalEntries();

    expect($component->getAction()?->getName())->toBe('viewStackedUsers-users')
        ->and($component->getStackedModalAction()?->getModalSubmitAction())->toBeNull()
        ->and($entries)->toHaveCount(2)
        ->and($entries[0])->toBeInstanceOf(UserEntry::class)
        ->and($entries[0]->getState())->toBe($users[0])
        ->and($entries[1]->getState())->toBe($users[1]);
});

it('hides the generated stacked action unless state is a non-empty collection', function () {
    $component = TestStackedUserColumn::make('users')
        ->stackedModal()
        ->record(new StackedModalUser(['id' => 99]));
    $component->testState = collect();

    expect($component->getAction())->toBeNull();

    $component->testState = new StackedModalUser(['id' => 1]);

    expect($component->getAction())->toBeNull();
});

it('hides the generated stacked action when the modal condition is disabled', function (bool $global, bool $useClosure) {
    config(['user-field.stacked.modal' => $global]);
    $condition = $useClosure ? fn (): bool => false : false;
    $component = TestStackedUserColumn::make('users')
        ->stackedModal($condition)
        ->record(new StackedModalUser(['id' => 99]));
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction())->toBeNull();
})->with([
    'local false' => [false, false],
    'false-returning closure' => [false, true],
    'global true with local false' => [true, false],
]);

it('preserves a custom action configured after the stacked modal', function () {
    $customAction = Action::make('custom');
    $component = TestStackedUserColumn::make('users')->stackedModal(false)->action($customAction);
    $component->testState = collect([new StackedModalUser(['id' => 1])]);

    expect($component->getAction())->toBe($customAction);
});
