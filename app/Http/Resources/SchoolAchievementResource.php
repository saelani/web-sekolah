<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolAchievementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'level' => $this->level,
            'winner_name' => $this->winner_name,
            'achievement_date' => $this->achievement_date?->format('d M Y'),
            'image_url' => $this->image_path ? asset('storage/'.$this->image_path) : null,
        ];
    }
}
