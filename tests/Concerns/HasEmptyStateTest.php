<?php

use Deldius\UserField\Concerns\HasEmptyState;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

class TestClassWithEmptyState
{
    use HasEmptyState;

    public function evaluate($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

class MockHtmlable implements Htmlable
{
    public function __construct(private string $html) {}

    public function toHtml(): string
    {
        return $this->html;
    }
}

beforeEach(function () {
    $this->testClass = new TestClassWithEmptyState;
    $this->mockView = Mockery::mock(View::class);
    $this->mockHtmlable = new MockHtmlable('<div>Empty State</div>');
});

it('can set empty state with view', function () {
    $result = $this->testClass->emptyState($this->mockView);

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyState())->toBe($this->mockView);
});

it('can set empty state with htmlable', function () {
    $result = $this->testClass->emptyState($this->mockHtmlable);

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyState())->toBe($this->mockHtmlable);
});

it('can set empty state with closure', function () {
    $closure = fn () => $this->mockView;
    $result = $this->testClass->emptyState($closure);

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyState())->toBe($this->mockView);
});

it('returns null when no empty state is set', function () {
    expect($this->testClass->getEmptyState())->toBeNull();
});

it('can set empty state heading with string', function () {
    $result = $this->testClass->emptyStateHeading('No Items Found');

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyStateHeading())->toBe('No Items Found');
});

it('can set empty state heading with htmlable', function () {
    $result = $this->testClass->emptyStateHeading($this->mockHtmlable);

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyStateHeading())->toBe($this->mockHtmlable);
});

it('can set empty state heading with closure', function () {
    $closure = fn () => 'Dynamic Heading';
    $result = $this->testClass->emptyStateHeading($closure);

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyStateHeading())->toBe('Dynamic Heading');
});

it('returns null when no empty state description is set', function () {
    expect($this->testClass->getEmptyStateDescription())->toBeNull();
});

it('can chain method calls', function () {
    $result = $this->testClass
        ->emptyState($this->mockView)
        ->emptyStateHeading('Test Heading');

    expect($result)->toBe($this->testClass);
    expect($this->testClass->getEmptyState())->toBe($this->mockView);
    expect($this->testClass->getEmptyStateHeading())->toBe('Test Heading');
});

it('can set empty state to null', function () {
    $this->testClass->emptyState($this->mockView);
    $this->testClass->emptyState(null);

    expect($this->testClass->getEmptyState())->toBeNull();
});

it('can set empty state heading to null', function () {
    $this->testClass->emptyStateHeading('Test Heading');
    $this->testClass->emptyStateHeading(null);

    expect($this->testClass->getEmptyStateHeading())->toBe(__('filament-user-field::user-field.empty_state_heading'));
});

class DummyUserFieldWithEmptyState
{
    use HasEmptyState;

    protected $state;

    public function __construct($state = null)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    // Simulate Filament's evaluate method
    protected function evaluate($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

it('can set and get emptyState directly', function () {
    $field = new DummyUserFieldWithEmptyState;
    $view = Mockery::mock(View::class);
    $field->emptyState($view);
    expect($field->getEmptyState())->toBe($view);

    $htmlable = new MockHtmlable('<div>Empty</div>');
    $field->emptyState($htmlable);
    expect($field->getEmptyState())->toBe($htmlable);

    $field->emptyState(fn () => $view);
    expect($field->getEmptyState())->toBe($view);
});

it('returns null if emptyState is not set and state is null', function () {
    $field = new DummyUserFieldWithEmptyState;
    $field->emptyState(null);
    expect($field->getEmptyState())->toBeNull();
});

it('returns false if state is not empty', function () {
    $field = new DummyUserFieldWithEmptyState('not_empty');
    $field->emptyState(null);
    expect($field->getEmptyState())->toBeNull();
});

it('returns true if state is empty', function () {
    $field = new DummyUserFieldWithEmptyState('');
    $field->emptyState(null);
    expect($field->getEmptyState())->toBeNull();
});
