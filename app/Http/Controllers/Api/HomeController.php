<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SchoolAchievement;
use App\Models\SchoolFacility;
use App\Models\SchoolProfile;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $profile = SchoolProfile::first();

        $posts = Post::with(['category', 'author'])
            ->where('is_published', true)
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->latest('published_at')
            ->paginate(6);

        $facilities = SchoolFacility::latest()->take(6)->get();
        $achievements = SchoolAchievement::latest('achievement_date')->take(6)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'profile' => $profile,
                'posts' => $posts,
                'facilities' => $facilities,
                'achievements' => $achievements,
            ]
        ]);
    }
}