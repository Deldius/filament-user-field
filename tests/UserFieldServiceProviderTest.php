<?php

use Deldius\UserField\UserFieldServiceProvider;

function getPackageProviders($app)
{
    return [UserFieldServiceProvider::class];
}

test('it can publish config file', function () {
    $this->artisan('vendor:publish --tag=filament-user-field-config')
        ->assertSuccessful();

    $configPath = app()->configPath('user-field.php');

    $this->assertFileExists($configPath);
});

test('it can publish view file', function () {
    $this->artisan('vendor:publish --tag=filament-user-field-views')
        ->assertSuccessful();

    $viewsPath = resource_path('views/vendor/filament-user-field/user-entry.blade.php');

    $this->assertFileExists($viewsPath);
});
