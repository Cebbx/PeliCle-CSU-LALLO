<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasBackButton;

    protected static string $resource = UserResource::class;
}
