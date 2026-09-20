<?php

namespace Deldius\UserField\Tests\Fixtures;

use Deldius\UserField\UserSelect;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class SelectableUser extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}

class UserSelectHost extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(SelectableUser::class, 'user_id');
    }

    public function reviewers()
    {
        return $this->belongsToMany(SelectableUser::class, 'user_select_host_reviewer');
    }
}

class TestUserSelect extends UserSelect
{
    public mixed $testState = null;

    public function getState(): mixed
    {
        return $this->testState;
    }
}

class ConfiguredUserSelectForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }

    public function mount(mixed $userId, array $userIds): void
    {
        $this->form->fill([
            'user_id' => $userId,
            'user_ids' => $userIds,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                UserSelect::make('user_id'),
                UserSelect::make('user_ids')->multiple(),
            ]);
    }

    public function render()
    {
        return view('user-select-tests::user-select-form');
    }
}

class UserSelectForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public UserSelectHost $record;

    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }

    public function mount(UserSelectHost $record): void
    {
        $this->record = $record;
        $this->form->fill($record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->record)
            ->statePath('data')
            ->components([
                UserSelect::make('user_id')->relationship('user'),
                UserSelect::make('reviewers')->relationship('reviewers')->multiple(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $this->record->fill(['user_id' => $data['user_id'] ?? null])->save();
        $this->form->model($this->record)->saveRelationships();
    }

    public function render()
    {
        return view('user-select-tests::user-select-form');
    }
}
