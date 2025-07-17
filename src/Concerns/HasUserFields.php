<?php

namespace Deldius\UserField\Concerns;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

trait HasUserFields
{
    protected string | Htmlable | Closure | null $heading = null;

    protected string | Htmlable | Closure | null $description = null;

    public function heading(string | Htmlable | Closure | null $heading = null): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function description(string | Htmlable | Closure | null $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function getHeading(): string
    {
        if ($this->heading) {
            return $this->evaluate($this->heading);
        }

        $headingField = config('user-field.user_model.fields.heading', 'name');
        $user = $this->getState();

        return $user->{$headingField} ?? '';
    }

    public function getDescription(): string
    {
        if ($this->description) {
            return $this->evaluate($this->description);
        }

        $descriptionField = config('user-field.user_model.fields.description', 'email');
        $user = $this->getState();

        return $user->{$descriptionField} ?? '';
    }
}
