<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentAttendance;
use App\Models\StudentSaving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah',
            ], 401);
        }

        $token = $user->createToken('student_token')->plainTextToken;

        return $this->buildResponse($user, $token, 'Login berhasil');
    }

    public function me(Request $request)
    {
        return $this->buildResponse($request->user(), null, 'Data profil berhasil dimuat');
    }

    private function buildResponse(User $user, ?string $token, string $message)
    {
        // Ambil profil siswa
        $student = $user->student()->with(['classRoom'])->first();

        $attendances = [];
        $savings = [];

        if ($student) {
            // Cukup cari berdasarkan student_id
            $attendances = StudentAttendance::where('student_id', $student->id)
                ->orderBy('date', 'desc')
                ->get();

            // Cukup cari berdasarkan student_id
            $savings = StudentSaving::where('student_id', $student->id)
                ->orderBy('date', 'desc')
                ->get();
        }

        // Format photo_url
        $studentData = $student ? $student->toArray() : null;
        if ($studentData) {
            $studentData['photo_url'] = !empty($studentData['photo_path']) 
                ? "http://192.168.100.234:8004/storage/" . $studentData['photo_path'] 
                : null;
            
            $studentData['attendances'] = $attendances;
            $studentData['savings']     = $savings;
        }

        $response = [
            'success' => true,
            'message' => $message,
            'data'    => [
                'user'    => $user,
                'student' => $studentData,
            ],
        ];

        if ($token) {
            $response['token'] = $token;
        }

        return response()->json($response, 200);
    }
}