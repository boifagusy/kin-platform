<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TrustedContact;
use App\Actions\Auth\SaveTrustedContactAction;
use App\Actions\Auth\AcceptTrustedContactAction;
use App\Actions\Auth\DeclineTrustedContactAction;
use App\Actions\Auth\VerifyInvitationAction;
use App\Actions\Auth\RemoveTrustedContactAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * TrustedContactController
 *
 * Orchestration layer only.
 * All business logic delegated to Actions.
 * Controller handles:
 * - Request validation
 * - Action invocation
 * - API response formatting
 * - Authorization
 */
class TrustedContactController extends Controller
{
    /**
     * POST /api/v1/trusted-contacts
     * Add a new trusted contact
     */
    public function store(
        Request $request,
        SaveTrustedContactAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'contact_name' => 'required|string|min:2',
            'contact_phone' => 'required|string|min:10',
            'invite_sent' => 'boolean',
        ]);

        $result = $action->execute(
            $request->user()->id,
            $validated['contact_name'],
            $validated['contact_phone'],
            $validated['invite_sent'] ?? false
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Contact added',
            'data' => $result['data'],
            'status' => $result['status'] ?? null,
            'verification_token' => $result['verification_token'] ?? null,
        ], 201);
    }

    /**
     * GET /api/v1/trusted-contacts
     * Get current trusted contact (single contact per free tier)
     */
    public function index(Request $request): JsonResponse
    {
        $contact = TrustedContact::where('user_id', $request->user()->id)
            ->active()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * GET /api/v1/trusted-contacts/pending
     * Get all pending requests and invitations
     */
    public function pending(Request $request): JsonResponse
    {
        $contacts = TrustedContact::where('user_id', $request->user()->id)
            ->pending()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contacts,
            'count' => $contacts->count(),
        ]);
    }

    /**
     * POST /api/v1/trusted-contacts/{id}/accept
     * Accept a pending request
     */
    public function accept(
        Request $request,
        int $id,
        AcceptTrustedContactAction $action
    ): JsonResponse {
        $result = $action->execute($request->user()->id, $id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['error'] === 'Unauthorized' ? 403 : 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    /**
     * POST /api/v1/trusted-contacts/{id}/decline
     * Decline a pending request or invitation
     */
    public function decline(
        Request $request,
        int $id,
        DeclineTrustedContactAction $action
    ): JsonResponse {
        $result = $action->execute($request->user()->id, $id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['error'] === 'Unauthorized' ? 403 : 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    /**
     * POST /api/v1/trusted-contacts/verify
     * Verify an invitation token (unregistered user accepting invite)
     */
    public function verify(
        Request $request,
        VerifyInvitationAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $result = $action->execute($validated['token']);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    /**
     * DELETE /api/v1/trusted-contacts/{id}
     * Remove a trusted contact
     */
    public function destroy(
        Request $request,
        int $id,
        RemoveTrustedContactAction $action
    ): JsonResponse {
        $result = $action->execute($request->user()->id, $id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['error'] === 'Unauthorized' ? 403 : 404);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
