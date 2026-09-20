<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil nama dari User terpilih untuk diisikan ke kolom 'name' tabel acad_teachers
        if (! empty($data['user_id'])) {
            $user = User::withoutGlobalScopes()->find($data['user_id']);
            if ($user) {
                $data['name'] = $user->name;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
