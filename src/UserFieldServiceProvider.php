<?php

namespace Deldius\UserField;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UserFieldServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-user-field';

    public static string $viewNamespace = 'filament-user-field';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasViews(static::$viewNamespace)
            ->hasConfigFile('user-field')
            ->hasTranslations();
    }

    public function getAssetPackageName(): ?string
    {
        return 'deldius/filament-user-field';
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            [
                Css::make(
                    'filament-user-field',
                    __DIR__ . '/../resources/dist/filament-user-field.css'
                )
                // ->loadedOnRequest()
                ,
            ],
            $this->getAssetPackageName()
        );
    }
}
