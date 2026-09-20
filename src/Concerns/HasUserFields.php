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
        return $this->getHeadingFor($this->getState());
    }

    public function getHeadingFor(mixed $user): string
    {
        if ($this->heading) {
            return $this->evaluate($this->heading, [
                'state' => $user,
                'user' => $user,
            ]);
        }

        $headingField = config('user-field.user_model.fields.heading', 'name');

        return $user->{$headingField} ?? '';
    }

    public function getDescription(): string
    {
        return $this->getDescriptionFor($this->getState());
    }

    public function getDescriptionFor(mixed $user): string
    {
        if ($this->description) {
            return $this->evaluate($this->description, [
                'state' => $user,
                'user' => $user,
            ]);
        }

        $descriptionField = config('user-field.user_model.fields.description', 'email');

        return $user->{$descriptionField} ?? '';
    }
}
