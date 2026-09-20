<?php

use Deldius\UserField\Tests\Fixtures\SelectableUser;
use Deldius\UserField\Tests\Fixtures\TestUserSelect;
use Deldius\UserField\UserSelect;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

require_once __DIR__ . '/Fixtures/UserSelectComponents.php';

beforeEach(function () {
    view()->replaceNamespace('filament-user-field', dirname(__DIR__) . '/resources/views');

    Schema::create('selectable_users', function (Blueprint $table): void {
        $table->id();
        $table->string('external_id')->nullable()->unique();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('display_name')->nullable();
        $table->string('contact_email')->nullable();
        $table->string('avatar_url')->nullable();
        $table->boolean('is_active')->default(true);
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

    config([
        'user-field.user_model.class' => SelectableUser::class,
        'user-field.user_model.fields.id' => 'id',
        'user-field.user_model.fields.avatar_url' => 'avatar_url',
        'user-field.user_model.fields.heading' => 'name',
        'user-field.user_model.fields.description' => 'email',
    ]);
});

it('uses rich searchable non-native select defaults', function () {
    $select = UserSelect::make('user_id');

    expect($select->isSearchable())->toBeTrue()
        ->and($select->isHtmlAllowed())->toBeTrue()
        ->and($select->isNative())->toBeFalse()
        ->and($select->isMultiple())->toBeFalse()
        ->and($select->multiple()->isMultiple())->toBeTrue();
});

it('formats escaped user details and an avatar', function () {
    $user = new SelectableUser([
        'name' => '<script>alert("name")</script>',
        'email' => '<img src=x onerror=alert("email")>',
        'avatar_url' => 'https://example.com/avatar.png',
    ]);

    $label = UserSelect::make('user_id')->formatUserOption($user);

    expect($label)->toContain('fi-user-select-option', 'avatar.png', '&lt;script&gt;', '&lt;img')
        ->not->toContain('<script>', '<img src=x');
});

it('renders a fallback avatar and omits a blank description', function () {
    $user = new SelectableUser(['name' => 'Ada', 'email' => '', 'avatar_url' => null]);
    $label = UserSelect::make('user_id')->formatUserOption($user);

    expect($label)->toContain('fi-user-select-option-fallback', 'Ada')
        ->not->toContain('fi-user-select-option-description');
});

it('injects user record and state aliases into display callbacks', function (Closure $heading, Closure $description) {
    $user = new SelectableUser(['name' => 'Ada', 'email' => 'ada@example.com']);
    $select = UserSelect::make('user_id')
        ->heading($heading)
        ->description($description);

    expect($select->formatUserOption($user))->toContain('Ada', 'ada@example.com');
})->with([
    'user' => [fn ($user) => $user->name, fn ($user) => $user->email],
    'record' => [fn ($record) => $record->name, fn ($record) => $record->email],
    'state' => [fn ($state) => $state->name, fn ($state) => $state->email],
]);

it('renders explicitly trusted htmlable display values', function () {
    $user = new SelectableUser(['name' => 'Ignored', 'email' => 'ignored@example.com']);
    $select = UserSelect::make('user_id')
        ->heading(new HtmlString('<strong>Trusted heading</strong>'))
        ->description(new HtmlString('<em>Trusted description</em>'));

    expect($select->formatUserOption($user))
        ->toContain('<strong>Trusted heading</strong>', '<em>Trusted description</em>');
});

it('omits an htmlable description whose rendered value is empty', function () {
    $user = new SelectableUser(['name' => 'Ada']);
    $description = new class implements Htmlable
    {
        public function toHtml(): string
        {
            return '';
        }
    };
    $select = UserSelect::make('user_id')->description($description);

    expect($select->formatUserOption($user))
        ->not->toContain('fi-user-select-option-description');
});

it('escapes hostile avatar values in the src attribute', function () {
    $user = new SelectableUser([
        'name' => 'Ada',
        'avatar_url' => 'data:image/svg+xml," onerror="alert(1)',
    ]);

    $label = UserSelect::make('user_id')->formatUserOption($user);

    expect($label)->toContain('data:image/svg+xml,&quot; onerror=&quot;alert(1)')
        ->not->toContain('src="data:image/svg+xml," onerror="alert(1)"');
});

it('searches configured heading and description fields within the option limit', function () {
    SelectableUser::query()->insert([
        ['id' => 1, 'name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        ['id' => 2, 'name' => 'Grace Hopper', 'email' => 'compiler@example.com'],
        ['id' => 3, 'name' => 'Alan Turing', 'email' => 'alan@example.com'],
    ]);

    $select = UserSelect::make('user_id')->optionsLimit(1);

    expect($select->getSearchResults('Ada'))->toHaveKey(1)
        ->and($select->getSearchResults('compiler'))->toHaveKey(2)
        ->and($select->getSearchResults('example.com'))->toHaveCount(1);
});

it('does not load initial options unless preloaded', function () {
    SelectableUser::query()->insert([
        ['name' => 'Ada', 'email' => 'ada@example.com'],
        ['name' => 'Grace', 'email' => 'grace@example.com'],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $options = UserSelect::make('user_id')->getOptions();

    expect($options)->toBe([])
        ->and(DB::getQueryLog())->toBe([])
        ->and(UserSelect::make('user_id')->preload()->optionsLimit(1)->getOptions())->toHaveCount(1);
});

it('uses configured heading and description fields', function () {
    config([
        'user-field.user_model.fields.heading' => 'display_name',
        'user-field.user_model.fields.description' => 'contact_email',
    ]);
    SelectableUser::query()->create([
        'name' => 'Ignored Name',
        'email' => 'ignored@example.com',
        'display_name' => 'Configured Name',
        'contact_email' => 'configured@example.com',
    ]);

    $results = UserSelect::make('user_id')->getSearchResults('configured@example.com');

    expect(array_values($results)[0])->toContain('Configured Name', 'configured@example.com')
        ->not->toContain('Ignored Name', 'ignored@example.com');
});

it('binds the search value in one grouped constraint', function () {
    SelectableUser::query()->create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
    ]);
    DB::flushQueryLog();
    DB::enableQueryLog();

    UserSelect::make('user_id')->getSearchResults('Ada');

    $query = DB::getQueryLog()[0];

    expect($query['query'])->toContain('("name" like ? or "email" like ?)')
        ->not->toContain('%Ada%')
        ->and($query['bindings'])->toBe(['%Ada%', '%Ada%']);
});

it('loads multiple selected labels once and preserves state order', function () {
    SelectableUser::query()->insert([
        ['id' => 1, 'name' => 'First', 'email' => 'first@example.com'],
        ['id' => 2, 'name' => 'Second', 'email' => 'second@example.com'],
    ]);

    $select = TestUserSelect::make('users')->multiple();
    $select->testState = ['2', '1'];
    DB::flushQueryLog();
    DB::enableQueryLog();

    $labels = $select->getOptionLabels();

    expect(array_keys($labels))->toBe([2, 1])
        ->and(DB::getQueryLog())->toHaveCount(1);
});

it('applies the query modifier to search preload and selected values', function () {
    SelectableUser::query()->insert([
        ['id' => 1, 'name' => 'Active', 'email' => 'active@example.com', 'is_active' => true],
        ['id' => 2, 'name' => 'Inactive', 'email' => 'inactive@example.com', 'is_active' => false],
    ]);

    $select = UserSelect::make('user_id')
        ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true));
    $selected = TestUserSelect::make('user_id')
        ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true));
    $selected->testState = 2;

    expect($select->getSearchResults('Active'))->toHaveKey(1)->not->toHaveKey(2)
        ->and($select->preload()->getOptions())->toHaveKey(1)->not->toHaveKey(2)
        ->and($selected->getOptionLabel(withDefault: false))->toBeNull();
});

it('supports an in-place query modifier and injects search only for searches', function () {
    SelectableUser::query()->insert([
        ['id' => 1, 'name' => 'Active', 'email' => 'active@example.com', 'is_active' => true],
        ['id' => 2, 'name' => 'Inactive', 'email' => 'inactive@example.com', 'is_active' => false],
    ]);
    $searches = [];
    $select = TestUserSelect::make('user_id')
        ->modifyQueryUsing(function (Builder $query, ?string $search) use (&$searches): void {
            $searches[] = $search;
            $query->where('is_active', true);
        });

    expect($select->getSearchResults('Active'))->toHaveKey(1)->not->toHaveKey(2)
        ->and($select->preload()->getOptions())->toHaveKey(1)->not->toHaveKey(2);

    $select->testState = 2;

    expect($select->getOptionLabel(withDefault: false))->toBeNull()
        ->and($searches)->toBe(['Active', null, null]);
});

it('rejects an invalid configured model when queried', function () {
    config(['user-field.user_model.class' => stdClass::class]);

    UserSelect::make('user_id')->getSearchResults('Ada');
})->throws(LogicException::class, 'user-field.user_model.class');

it('uses a configured nonstandard id field for options and selected values', function () {
    config(['user-field.user_model.fields.id' => 'external_id']);
    SelectableUser::query()->create([
        'external_id' => 'user-ada',
        'name' => 'Ada',
        'email' => 'ada@example.com',
    ]);

    $select = TestUserSelect::make('user_id');
    $select->testState = 'user-ada';

    expect($select->getSearchResults('Ada'))->toHaveKey('user-ada')
        ->and($select->getOptionLabel(withDefault: false))->toContain('Ada');
});

it('omits missing selected ids and does not query for empty multiple state', function () {
    $select = TestUserSelect::make('users')->multiple();
    $select->testState = [];
    $missing = TestUserSelect::make('user_id');
    $missing->testState = 999;
    DB::flushQueryLog();
    DB::enableQueryLog();

    expect($select->getOptionLabels(withDefaults: false))->toBe([])
        ->and(DB::getQueryLog())->toBe([])
        ->and($missing->getOptionLabel(withDefault: false))->toBeNull();
});
