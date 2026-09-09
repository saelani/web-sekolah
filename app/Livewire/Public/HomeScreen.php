<?php

namespace App\Livewire\Public;

use App\Models\Post;
use App\Models\SchoolAchievement;
use App\Models\SchoolFacility;
use App\Models\SchoolProfile;
use Livewire\Component;
use Livewire\WithPagination;

class HomeScreen extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $profile = SchoolProfile::first();
        
        $posts = Post::with(['category', 'author'])
            ->where('is_published', true)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->latest('published_at')
            ->paginate(6);

        $facilities = SchoolFacility::latest()->take(6)->get();
        $achievements = SchoolAchievement::latest('achievement_date')->take(6)->get();

        return view('livewire.public.home-screen', [
            'profile' => $profile,
            'posts' => $posts,
            'facilities' => $facilities,
            'achievements' => $achievements,
        ])->layout('components.layouts.app', ['title' => $profile->school_name ?? 'Web Sekolah']);
    }
}