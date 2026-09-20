<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class BaseResource extends Resource
{
    /**
     * 1. HAK AKSES MENU (SIDEBAR)
     * Menentukan siapa saja yang dapat melihat menu Resource ini di nav.
     */
    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && in_array($user->role, ['admin', 'headmaster', 'teacher']);
    }

    /**
     * 2. FILTERING DATA
     * Menyeleksi baris data yang tampil di tabel berdasarkan role user.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User|null $user */
        $user = auth()->user();

        if ($user && $user->role === 'teacher') {
            $model = new (static::getModel());
            $table = $model->getTable();

            // Saring otomatis jika tabel memiliki kolom teacher_id atau user_id
            if (Schema::hasColumn($table, 'teacher_id')) {
                return $query->where($table.'.teacher_id', $user->id);
            }

            if (Schema::hasColumn($table, 'user_id')) {
                return $query->where($table.'.user_id', $user->id);
            }
        }

        // Admin dan Headmaster dapat melihat seluruh data
        return $query;
    }

    /**
     * 3. HAK AKSES MEMBUAT DATA (CREATE)
     */
    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        // Hanya Admin dan Teacher yang boleh menambah data
        return $user && in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * 4. HAK AKSES MENGEDIT DATA (EDIT)
     */
    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Teacher hanya boleh mengedit data miliknya sendiri
        if ($user->role === 'teacher') {
            $teacherId = $record->teacher_id ?? $record->user_id ?? null;

            return $teacherId === $user->id;
        }

        // Admin dan Headmaster boleh mengedit semua data
        return in_array($user->role, ['admin', 'headmaster']);
    }

    /**
     * 5. HAK AKSES MENAMPILKAN DETAIL DATA (VIEW)
     */
    public static function canView(Model $record): bool
    {
        return static::canEdit($record);
    }

    /**
     * 6. HAK AKSES MENGHAPUS DATA (DELETE)
     */
    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        // Hanya Admin yang diberi wewenang menghapus data
        return $user && $user->role === 'admin';
    }
}
