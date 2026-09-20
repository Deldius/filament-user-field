<?php

namespace Deldius\UserField;

use Closure;
use Deldius\UserField\Concerns\HasAvatar;
use Deldius\UserField\Concerns\HasUserFields;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class UserSelect extends Select
{
    use HasAvatar;
    use HasUserFields;

    protected ?Closure $modifyUserQueryUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable();
        $this->allowHtml();
        $this->native(false);
        $this->options(fn (UserSelect $component): array => $component->getPreloadedUserOptions());
        $this->getSearchResultsUsing(
            fn (UserSelect $component, string $search): array => $component->getUserSearchResults($search),
        );
        $this->getOptionLabelUsing(
            fn (UserSelect $component, mixed $value): ?string => $component->getSelectedUserLabel($value),
        );
        $this->getOptionLabelsUsing(
            fn (UserSelect $component, array $values): array => $component->getSelectedUserLabels($values),
        );
        $this->getOptionLabelFromRecordUsing(
            fn (Model $record): string => $this->formatUserOption($record),
        );
    }

    public function relationship(
        string | Closure | null $name = null,
        string | Closure | null $titleAttribute = null,
        ?Closure $modifyQueryUsing = null,
        bool $ignoreRecord = false,
    ): static {
        $titleAttribute ??= (string) config('user-field.user_model.fields.heading', 'name');

        return parent::relationship($name, $titleAttribute, $modifyQueryUsing, $ignoreRecord);
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyUserQueryUsing = $callback;

        return $this;
    }

    public function getUserSearchResults(?string $search): array
    {
        $query = $this->getUserQuery($search);

        if (filled($search)) {
            $heading = config('user-field.user_model.fields.heading', 'name');
            $description = config('user-field.user_model.fields.description', 'email');

            $query->where(function (Builder $query) use ($description, $heading, $search): void {
                $query->where($heading, 'like', "%{$search}%");

                if ($description !== $heading) {
                    $query->orWhere($description, 'like', "%{$search}%");
                }
            });
        }

        return $this->formatUserOptions($query->limit($this->getOptionsLimit())->get());
    }

    public function getPreloadedUserOptions(): array
    {
        return $this->isPreloaded() ? $this->getUserSearchResults(null) : [];
    }

    public function getSelectedUserLabel(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $idField = config('user-field.user_model.fields.id', 'id');
        $user = $this->getUserQuery()->where($idField, $value)->first();

        return $user ? $this->formatUserOption($user) : null;
    }

    public function getSelectedUserLabels(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $idField = config('user-field.user_model.fields.id', 'id');
        $users = $this->getUserQuery()->whereIn($idField, $values)->get()->keyBy(
            fn (Model $user) => (string) $user->getAttribute($idField),
        );

        return collect($values)
            ->mapWithKeys(function (mixed $value) use ($users): array {
                $user = $users->get((string) $value);

                return $user ? [$value => $this->formatUserOption($user)] : [];
            })
            ->all();
    }

    public function formatUserOption(Model $user): string
    {
        return view('filament-user-field::components.user-select-option', [
            'avatarUrl' => $this->getAvatarUrlFor($user),
            'heading' => $this->getHeadingFor($user),
            'description' => $this->getDescriptionFor($user),
        ])->render();
    }

    protected function getUserQuery(?string $search = null): Builder
    {
        $modelClass = config('user-field.user_model.class');

        if ((! is_string($modelClass)) || (! is_subclass_of($modelClass, Model::class))) {
            throw new LogicException('The [user-field.user_model.class] configuration value must be an Eloquent model class.');
        }

        $query = (new $modelClass)->newQuery();

        if ($this->modifyUserQueryUsing) {
            $query = $this->evaluate($this->modifyUserQueryUsing, [
                'query' => $query,
                'search' => $search,
            ]) ?? $query;
        }

        return $query;
    }

    protected function formatUserOptions(iterable $users): array
    {
        $idField = config('user-field.user_model.fields.id', 'id');

        return collect($users)
            ->mapWithKeys(fn (Model $user): array => [
                $user->getAttribute($idField) => $this->formatUserOption($user),
            ])
            ->all();
    }
}
