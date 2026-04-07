<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $organization = $user->currentOrganization;

        if (! $organization) {
            return response()->json(['message' => 'No organization'], 400);
        }

        $members = $organization->users()->withPivot('role_id')->get();

        return response()->json($members);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'role_id' => 'required|integer',
        ]);

        $authUser = $request->user();
        $organization = $authUser->currentOrganization;

        if (! $organization) {
            return response()->json(['message' => 'No organization'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // attach user
        $organization->users()->syncWithoutDetaching([
            $user->id => ['role_id' => $request->role_id]
        ]);

        return response()->json([
            'message' => 'Member added successfully'
        ]);
    }
}