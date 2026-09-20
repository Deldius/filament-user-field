<?php

use Deldius\UserField\Tests\Fixtures\StackedTableRecord;
use Deldius\UserField\Tests\Fixtures\StackedUsersTable;
use Deldius\UserField\Tests\Fixtures\TwoStackedUsersTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

require_once __DIR__ . '/Fixtures/StackedComponents.php';
require_once __DIR__ . '/Fixtures/StackedUsersTable.php';

beforeEach(function () {
    config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
    view()->replaceNamespace('filament-user-field', dirname(__DIR__) . '/resources/views');

    Schema::create('stacked_table_records', function (Blueprint $table): void {
        $table->id();
    });

    StackedTableRecord::query()->insert([
        ['id' => 1],
        ['id' => 2],
    ]);
});

it('registers and mounts a generated stacked column action', function () {
    $record = StackedTableRecord::query()->findOrFail(1);

    $component = Livewire::test(StackedUsersTable::class)
        ->assertTableActionExists('viewStackedUsers-reviewers', record: $record)
        ->mountTableAction('viewStackedUsers-reviewers', $record);

    expect($component->instance()->getMountedAction()?->getName())->toBe('viewStackedUsers-reviewers')
        ->and($component->instance()->getMountedAction()?->getRecord()?->is($record))->toBeTrue();
});

it('builds the modal entries from the clicked non-final row', function () {
    $record = StackedTableRecord::query()->findOrFail(1);

    $component = Livewire::test(StackedUsersTable::class)
        ->mountTableAction('viewStackedUsers-reviewers', $record);

    $entries = $component->instance()->getSchema('mountedActionSchema0')?->getComponents() ?? [];

    expect($entries)->toHaveCount(2)
        ->and($entries[0]->getState()->name)->toBe('First Reviewer')
        ->and($entries[1]->getState()->name)->toBe('Second Reviewer');
});

it('uses compact spacing between stacked modal entries', function () {
    $record = StackedTableRecord::query()->findOrFail(1);

    $component = Livewire::test(StackedUsersTable::class)
        ->mountTableAction('viewStackedUsers-reviewers', $record);

    expect($component->instance()->getSchema('mountedActionSchema0')?->isDense())->toBeTrue();
});

it('registers distinct actions for two stacked columns', function () {
    $record = StackedTableRecord::query()->findOrFail(1);

    $component = Livewire::test(TwoStackedUsersTable::class)
        ->assertTableActionExists('viewStackedUsers-reviewers', record: $record)
        ->assertTableActionExists('viewStackedUsers-approvers', record: $record)
        ->mountTableAction('viewStackedUsers-reviewers', $record);

    expect($component->instance()->getSchema('mountedActionSchema0')?->getComponents()[0]->getState()->name)
        ->toBe('First Reviewer');

    $component
        ->unmountTableAction()
        ->mountTableAction('viewStackedUsers-approvers', $record);

    expect($component->instance()->getSchema('mountedActionSchema0')?->getComponents()[0]->getState()->name)
        ->toBe('First Approver');
});

it('labels the modal-enabled stack button with its purpose', function () {
    $html = Livewire::test(StackedUsersTable::class)->html();

    expect($html)->toMatch('/<button[^>]*wire:click[^>]*viewStackedUsers-reviewers[^>]*>.*Open user list for Reviewers.*<\/button>/s');
});
