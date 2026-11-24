<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasModulePermissions
{
    abstract protected static function getPermissionModule(): string;

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasModuleAccess(static::getPermissionModule()) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'edit') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }

    public static function canForceDeleteAny(): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }

    public static function canRestoreAny(): bool
    {
        return Auth::user()?->hasPermission(static::getPermissionModule(), 'delete') ?? false;
    }
}
