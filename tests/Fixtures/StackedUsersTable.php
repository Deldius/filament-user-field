<?php

namespace Deldius\UserField\Tests\Fixtures;

use Deldius\UserField\UserColumn;
use Filament\Tables\Table;
use Filament\Tables\TableComponent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;

class StackedTableRecord extends Model
{
    protected $guarded = [];

    protected $table = 'stacked_table_records';

    public function getReviewersAttribute(): mixed
    {
        return match ($this->getKey()) {
            1 => collect([
                new StackedModalUser(['id' => 11, 'name' => 'First Reviewer']),
                new StackedModalUser(['id' => 12, 'name' => 'Second Reviewer']),
            ]),
            default => collect([
                new StackedModalUser(['id' => 21, 'name' => 'Final Reviewer']),
            ]),
        };
    }

    public function getApproversAttribute(): mixed
    {
        return match ($this->getKey()) {
            1 => collect([
                new StackedModalUser(['id' => 31, 'name' => 'First Approver']),
            ]),
            default => collect([
                new StackedModalUser(['id' => 41, 'name' => 'Final Approver']),
            ]),
        };
    }
}

class StackedUsersTable extends TableComponent
{
    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StackedTableRecord::query())
            ->columns([
                UserColumn::make('reviewers')
                    ->stackedModal(),
            ])
            ->paginated(false);
    }

    public function render(): View
    {
        return view()->file(__DIR__ . '/stacked-users-table.blade.php');
    }
}

class TwoStackedUsersTable extends TableComponent
{
    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StackedTableRecord::query())
            ->columns([
                UserColumn::make('reviewers')->stackedModal(),
                UserColumn::make('approvers')->stackedModal(),
            ])
            ->paginated(false);
    }

    public function render(): View
    {
        return view()->file(__DIR__ . '/stacked-users-table.blade.php');
    }
}
