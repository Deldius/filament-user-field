<?php

namespace Deldius\UserField\Concerns;

use Closure;
use Filament\Support\Enums\Size;

trait HasSize
{
    protected Size | string | Closure | null $size = null;

    public function size(Size | string | Closure | null $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(): Size | string
    {
        $size = $this->evaluate($this->size);

        if (blank($size)) {
            return Size::Small;
        }

        if (is_string($size)) {
            $size = Size::tryFrom($size) ?? $size;
        }

        if ($size === 'base') {
            return Size::Medium;
        }

        return $size;
    }
}
