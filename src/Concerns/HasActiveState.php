<?php

namespace Deldius\UserField\Concerns;

use Closure;

trait HasActiveState
{
    protected null | bool | Closure $isActiveState = null;

    protected null | bool | Closure $showActiveState = null;

    public function showActiveState(null | bool | Closure $showActiveState = true): static
    {
        $this->showActiveState = $showActiveState;

        return $this;
    }

    public function getShowActiveState(): bool
    {
        if (! is_null($this->showActiveState)) {
            return $this->evaluate($this->showActiveState);
        }

        return config('user-field.active_state.show', false);
    }

    public function isActiveState(null | bool | Closure $isActiveState): static
    {
        $this->isActiveState = $isActiveState;

        return $this;
    }

    public function getIsActiveState(): ?bool
    {
        if (! is_null($this->isActiveState)) {
            return $this->evaluate($this->isActiveState);
        }

        $isActiveField = config('user-field.active_state.field', 'is_active');
        $user = $this->getState();

        return $user->{$isActiveField} ?? null;
    }
}
