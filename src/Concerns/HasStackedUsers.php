<?php

namespace Deldius\UserField\Concerns;

use Closure;
use Deldius\UserField\UserEntry;
use Filament\Actions\Action;
use Illuminate\Support\Collection;

trait HasStackedUsers
{
    protected int | Closure | null $stackedLimit = null;

    protected bool | Closure | null $stackedModal = null;

    protected string | Closure | null $stackedModalWidth = null;

    protected ?Action $stackedModalAction = null;

    public function stackedLimit(int | Closure | null $limit): static
    {
        $this->stackedLimit = $limit;

        return $this;
    }

    public function getStackedLimit(): int
    {
        return max(0, (int) ($this->evaluate($this->stackedLimit)
            ?? config('user-field.stacked.limit', 5)));
    }

    public function isStackedState(): bool
    {
        return $this->getState() instanceof Collection;
    }

    public function hasStackedUsers(): bool
    {
        return $this->isStackedState() && $this->getStackedUsers()->isNotEmpty();
    }

    public function getStackedUsers(): Collection
    {
        $state = $this->getState();

        return $state instanceof Collection ? $state : collect();
    }

    public function getVisibleStackedUsers(): Collection
    {
        return $this->getStackedUsers()->take($this->getStackedLimit())->values();
    }

    public function getStackedRemainingCount(): int
    {
        return max(0, $this->getStackedUsers()->count() - $this->getVisibleStackedUsers()->count());
    }

    public function stackedModal(bool | Closure $condition = true): static
    {
        $this->stackedModal = $condition;
        $this->stackedModalAction = $this->makeStackedModalAction();
        $this->action($this->stackedModalAction);

        return $this;
    }

    public function hasStackedModal(): bool
    {
        return (bool) ($this->evaluate($this->stackedModal)
            ?? config('user-field.stacked.modal', false));
    }

    public function stackedModalWidth(string | Closure | null $width): static
    {
        $this->stackedModalWidth = $width;

        return $this;
    }

    public function getStackedModalWidth(): ?string
    {
        return $this->evaluate($this->stackedModalWidth)
            ?? config('user-field.stacked.modal_width');
    }

    public function getStackedModalAction(): ?Action
    {
        return $this->stackedModalAction;
    }

    /**
     * @return array<UserEntry>
     */
    public function getStackedModalEntries(): array
    {
        return $this->getStackedUsers()
            ->values()
            ->map(fn (mixed $user, int $index): UserEntry => UserEntry::make("stacked-user-{$index}")
                ->hiddenLabel()
                ->state($user)
                ->showAvatar($this->showAvatar)
                ->avatarUrl($this->avatarUrl)
                ->heading($this->heading)
                ->description($this->description)
                ->size($this->size)
                ->showActiveState($this->showActiveState)
                ->isActiveState($this->isActiveState))
            ->all();
    }

    protected function makeStackedModalAction(): Action
    {
        return Action::make($this->getStackedModalActionName())
            ->modalHeading(fn (): string => (string) ($this->getLabel() ?: 'Users'))
            ->modalSubmitAction(false)
            ->modalWidth(fn (): ?string => $this->getStackedModalWidth())
            ->schema(fn (Action $action): array => $this->getStackedModalEntriesForAction($action));
    }

    /**
     * @return array<UserEntry>
     */
    protected function getStackedModalEntriesForAction(Action $action): array
    {
        return $this->getStackedModalEntries();
    }

    protected function getStackedModalActionName(): string
    {
        return 'viewStackedUsers';
    }

    protected function setUpStackedUsers(): void
    {
        if (config('user-field.stacked.modal', false)) {
            $this->stackedModal();
        }
    }
}
