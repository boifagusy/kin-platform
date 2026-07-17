<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TrustedContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(private TrustedContactService $service) {}

    public function verify(Request $request): JsonResponse
    {
        try {
            $contact = $this->service->verify($request->input('token'));
            return response()->json([
                'success' => true,
                'message' => "You are now a verified trusted contact.",
                'data' => ['contact_name' => $contact->name],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    public function resend(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->service->resend($request->user(), $id);
            $maxResends = config('kin.trusted_contacts.max_resends', 3);
            return response()->json([
                'success' => true,
                'message' => 'Verification link resent.',
                'data' => ['resends_remaining' => $maxResends - $contact->resend_count],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
    }
}
