<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAdminOrHeadmasterAccess
{
    /**
     * Cek apakah user yang login adalah Admin/Headmaster
     */
    protected static function hasAdminOrHeadmasterAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user && $user->hasRole('admin', 'headmaster', 'super_admin');
    }

    public static function canCreate(): bool
    {
        return static::hasAdminOrHeadmasterAccess();
    }

    public static function canEdit($record): bool
    {
        return static::hasAdminOrHeadmasterAccess();
    }

    public static function canDelete($record): bool
    {
        return static::hasAdminOrHeadmasterAccess();
    }

    public static function canDeleteAny(): bool
    {
        return static::hasAdminOrHeadmasterAccess();
    }
}