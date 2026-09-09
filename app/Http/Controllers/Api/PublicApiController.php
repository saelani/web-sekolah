<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\SchoolAchievementResource;
use App\Http\Resources\SchoolFacilityResource;
use App\Models\Post;
use App\Models\SchoolAchievement;
use App\Models\SchoolFacility;
use App\Models\SchoolProfile;
use Illuminate\Http\JsonResponse;

class PublicApiController extends Controller
{
    // 1. Profil Sekolah
    public function profile(): JsonResponse
    {
        $profile = SchoolProfile::first();
        if ($profile && $profile->logo_path) {
            $profile->logo_url = asset('storage/' . $profile->logo_path);
        }
        return response()->json(['data' => $profile]);
    }

    // 2. Daftar Berita / Artikel (Untuk Slide / List di Android)
    public function posts(): JsonResponse
    {
        $posts = Post::with(['category', 'author'])
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate(10);

        return response()->json(PostResource::collection($posts)->response()->getData());
    }

    // 3. Detail Berita
    public function postDetail($slug): JsonResponse
    {
        $post = Post::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment Views
        $post->increment('views_count');

        return response()->json(['data' => new PostResource($post)]);
    }

    // 4. Fasilitas Sekolah
    public function facilities(): JsonResponse
    {
        $facilities = SchoolFacility::latest()->get();

        return response()->json([
            'data' => SchoolFacilityResource::collection($facilities)
        ]);
    }

    // 5. Prestasi Sekolah
    public function achievements(): JsonResponse
    {
        $achievements = SchoolAchievement::latest('achievement_date')->get();

        return response()->json([
            'data' => SchoolAchievementResource::collection($achievements)
        ]);
    }
}