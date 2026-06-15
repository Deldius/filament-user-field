<?php

use Deldius\UserField\Concerns\HasState;

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
    protected int|string|DummyUser|null $state;

    public function getState(): mixed
    {
        return $this->state;
    }
}

class DummyUserFieldWithState extends DummyBaseField
{
    use HasState;

    public function __construct(int|string|DummyUser|null $state = null)
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
