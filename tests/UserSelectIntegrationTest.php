<?php

use Deldius\UserField\Tests\Fixtures\ConfiguredUserSelectForm;
use Deldius\UserField\Tests\Fixtures\SelectableUser;
use Deldius\UserField\Tests\Fixtures\UserSelectForm;
use Deldius\UserField\Tests\Fixtures\UserSelectHost;
use Deldius\UserField\UserSelect;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

require_once __DIR__ . '/Fixtures/UserSelectComponents.php';

beforeEach(function () {
    config([
        'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
        'user-field.user_model.class' => SelectableUser::class,
        'user-field.user_model.fields.id' => 'id',
        'user-field.user_model.fields.avatar_url' => 'avatar_url',
        'user-field.user_model.fields.heading' => 'name',
        'user-field.user_model.fields.description' => 'email',
    ]);

    view()->addNamespace('user-select-tests', __DIR__ . '/Fixtures');

    Schema::create('selectable_users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('avatar_url')->nullable();
    });

    Schema::create('user_select_hosts', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->nullable();
    });

    Schema::create('user_select_host_reviewer', function (Blueprint $table): void {
        $table->foreignId('user_select_host_id');
        $table->foreignId('selectable_user_id');
        $table->primary(['user_select_host_id', 'selectable_user_id']);
    });
});

it('defaults a relationship title to the configured user heading field', function () {
    expect(UserSelect::make('user')->relationship('user')->getRelationshipTitleAttribute())
        ->toBe('name');
});

it('renders rich relationship labels for single and multiple selected users', function () {
    $primary = SelectableUser::query()->create([
        'name' => 'Primary User',
        'email' => 'primary@example.com',
        'avatar_url' => null,
    ]);
    $reviewer = SelectableUser::query()->create([
        'name' => 'Review User',
        'email' => 'review@example.com',
        'avatar_url' => null,
    ]);
    $host = UserSelectHost::query()->create(['user_id' => $primary->id]);
    $host->reviewers()->attach($reviewer);

    $component = Livewire::test(UserSelectForm::class, ['record' => $host]);

    $component
        ->assertSee('Primary User')
        ->assertSee('primary@example.com')
        ->assertSee('Review User')
        ->assertSee('review@example.com');
});

it('hydrates configured-model scalar and array state in a rendered form', function () {
    $primary = SelectableUser::query()->create([
        'name' => 'Primary User',
        'email' => 'primary@example.com',
    ]);
    $reviewer = SelectableUser::query()->create([
        'name' => 'Review User',
        'email' => 'review@example.com',
    ]);

    Livewire::test(ConfiguredUserSelectForm::class, [
        'userId' => $primary->id,
        'userIds' => [(string) $reviewer->id, (string) $primary->id],
    ])
        ->assertSet('data.user_id', $primary->id)
        ->assertSet('data.user_ids', [(string) $reviewer->id, (string) $primary->id])
        ->assertSee('Primary User')
        ->assertSee('primary@example.com')
        ->assertSee('Review User')
        ->assertSee('review@example.com');
});

it('persists single and multiple relationship selections', function () {
    $first = SelectableUser::query()->create(['name' => 'First', 'email' => 'first@example.com']);
    $second = SelectableUser::query()->create(['name' => 'Second', 'email' => 'second@example.com']);
    $third = SelectableUser::query()->create(['name' => 'Third', 'email' => 'third@example.com']);
    $host = UserSelectHost::query()->create(['user_id' => $first->id]);
    $host->reviewers()->attach($second);

    Livewire::test(UserSelectForm::class, ['record' => $host])
        ->set('data.user_id', $second->id)
        ->set('data.reviewers', [(string) $first->id, (string) $third->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($host->refresh()->user_id)->toBe($second->id)
        ->and($host->reviewers()->pluck('selectable_users.id')->all())
        ->toEqualCanonicalizing([$first->id, $third->id]);
});
