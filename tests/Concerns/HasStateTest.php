<?php

use Deldius\UserField\Concerns\HasState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DummyPolymorphicUser extends Model
{
    protected $guarded = [];
}

class DummyUser
{
    public ?int $id;

    public static $queryCount = 0;

    public function __construct(?array $attributes = [])
    {
        $this->id = $attributes['id'] ?? null;
    }

    public static function where(string $field, mixed $value)
    {
        self::$queryCount++;
        // Simulate Eloquent's where()->first()
        if ($field === 'id' && $value === 123) {
            return new class
            {
                public function first()
                {
                    return new DummyUser(['id' => 123]);
                }
            };
        }

        return new class
        {
            public function first()
            {
                return null;
            }
        };
    }

    public function toArray()
    {
        return ['id' => $this->id];
    }
}

class DummyBaseField
{
    protected mixed $state;

    public function getState(): mixed
    {
        return $this->state;
    }
}

class DummyUserFieldWithState extends DummyBaseField
{
    use HasState;

    public function __construct(mixed $state = null)
    {
        $this->state = $state;
    }
}

beforeEach(function () {
    DummyUser::$queryCount = 0;
    config(['user-field.user_model.class' => DummyUser::class]);
    config(['user-field.user_model.fields.id' => 'id']);
});

it('returns model instance if state is already model', function () {
    $user = new DummyUser(['id' => 123]);
    $field = new DummyUserFieldWithState($user);
    expect($field->getState())->toBe($user);
});

it('returns the concrete model from a polymorphic relationship', function () {
    $user = new DummyPolymorphicUser(['id' => 456]);
    $field = new DummyUserFieldWithState($user);

    expect($field->getState())->toBe($user);
});

it('resolves model by id if state is id', function () {
    $field = new DummyUserFieldWithState(123);
    $result = $field->getState();
    expect($result)->toBeInstanceOf(DummyUser::class);
    expect($result->id)->toBe(123);
});

it('returns null if state is null', function () {
    $field = new DummyUserFieldWithState(null);
    expect($field->getState())->toBeNull();
});

it('returns null without querying for falsy scalar state', function (mixed $state) {
    expect((new DummyUserFieldWithState($state))->getState())->toBeNull()
        ->and(DummyUser::$queryCount)->toBe(0);
})->with([0, '0', false]);

it('returns null if state is not found', function () {
    $field = new DummyUserFieldWithState(999);
    expect($field->getState())->toBeNull();
});

it('uses cache for resolving model', function () {
    $field = new DummyUserFieldWithState(123);
    $result1 = $field->getState();
    $result2 = $field->getState();
    expect(DummyUser::$queryCount)->toBe(1);
    expect($result1)->toBeInstanceOf(DummyUser::class);
    expect($result2)->toBeInstanceOf(DummyUser::class);
    expect($result1->id)->toBe(123);
    expect($result2->id)->toBe(123);
});

it('resolves arrays of models and scalar IDs in source order', function () {
    $polymorphicUser = new DummyPolymorphicUser(['id' => 456]);
    $field = new DummyUserFieldWithState([$polymorphicUser, 123, 999, null, new stdClass]);

    $result = $field->getState();

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($result->get(0))->toBe($polymorphicUser)
        ->and($result->get(1))->toBeInstanceOf(DummyUser::class)
        ->and($result->get(1)->id)->toBe(123);
});

it('resolves Laravel collections while preserving duplicates', function () {
    $user = new DummyPolymorphicUser(['id' => 456]);

    $result = (new DummyUserFieldWithState(collect([$user, $user])))->getState();

    expect($result)->toHaveCount(2)
        ->and($result->get(0))->toBe($user)
        ->and($result->get(1))->toBe($user);
});

it('returns an empty collection when no collection items resolve', function () {
    $result = (new DummyUserFieldWithState([999, null, new stdClass]))->getState();

    expect($result)->toBeInstanceOf(Collection::class)->toBeEmpty();
});

it('reuses the scalar cache while resolving collections', function () {
    $field = new DummyUserFieldWithState([123, 123]);

    expect($field->getState())->toHaveCount(2)
        ->and(DummyUser::$queryCount)->toBe(1);
});
