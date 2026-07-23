<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\TrustedContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\TrustedContactService;

class TrustedContactController extends Controller
{
    protected TrustedContactService $service;

    public function __construct(TrustedContactService $service)
    {
        $this->service = $service;
    }

    const FREE_LIMIT = 1;
    const PREMIUM_LIMIT = 5;
    const REMOVAL_COOLDOWN_DAYS = 30;

    public function index(Request $request)
    {
        $user = $request->user();

        $contacts = $user->trustedContacts()->orderBy('created_at', 'asc')->get();

        // Add removal eligibility info
        $contacts = $contacts->map(function ($contact) {
            $daysSinceCreation = Carbon::parse($contact->created_at)->diffInDays(now());
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'verified' => $contact->verified,
                'active' => $contact->active,
                'created_at' => $contact->created_at,
                'can_remove' => $daysSinceCreation >= self::REMOVAL_COOLDOWN_DAYS,
                'days_until_removal' => max(0, ceil(self::REMOVAL_COOLDOWN_DAYS - $daysSinceCreation)),
            ];
        });

        return ApiResponse::success([
            'contacts' => $contacts,
            'limit' => self::FREE_LIMIT,
            'used' => $contacts->count(),
            'can_add' => $contacts->count() < self::FREE_LIMIT,
            'removal_cooldown_days' => self::REMOVAL_COOLDOWN_DAYS,
        ], 'Trusted contacts retrieved');
    }

    public function store(Request $request)    {
        $request->validate([
            'name' => 'required|string|min:2|max:100',
            'contact_phone' => 'required|string|min:10|max:15',
        ]);

        $user = $request->user();

        // Validation 1: Cannot add yourself
        $cleanContactPhone = preg_replace('/[^0-9]/', '', $request->contact_phone);
        $cleanUserPhone = preg_replace('/[^0-9]/', '', $user->phone);

        if ($cleanContactPhone === $cleanUserPhone) {
            return ApiResponse::error('You cannot add yourself as a trusted contact', 422);
        }

        // Validation 2: Check if contact already exists
        $existing = TrustedContact::where('user_id', $user->id)
            ->where('phone', $request->contact_phone)
            ->first();

        if ($existing) {
            return ApiResponse::error('This contact is already in your trusted list', 422);
        }

        // Validation 3: Check limit
        $currentCount = $user->trustedContacts()->count();
        if ($currentCount >= self::FREE_LIMIT) {
            return ApiResponse::error('Free plan limit reached. Upgrade to premium for more contacts.', 403);
        }

        $contact = $this->service->create($user, [
            'name' => $request->name,
            'contact_phone' => $request->contact_phone,
        ]);

        return ApiResponse::success($contact, 'Trusted contact added', 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $contact = TrustedContact::where('id', $id)->where('user_id', $user->id)->first();

        if (!$contact) {            return ApiResponse::notFound('Contact not found');
        }

        // Check 30-day cooldown
        $daysSinceCreation = Carbon::parse($contact->created_at)->diffInDays(now());

        if ($daysSinceCreation < self::REMOVAL_COOLDOWN_DAYS) {
            $daysRemaining = ceil(self::REMOVAL_COOLDOWN_DAYS - $daysSinceCreation);
            return ApiResponse::error("Cannot remove trusted contact for {$daysRemaining} more days. This prevents abuse.", 403);
        }

        $contact->delete();

        return ApiResponse::success(null, 'Trusted contact removed');
    }

    public function verify($token)
    {
        $contact = $this->service->verify($token);

        if (!$contact) {
            return response("Invalid or expired verification link.", 404);
        }

        return response("You have been verified as a trusted contact for KIN. Thank you!", 200);
    }

    public function approve(Request $request, $id)
    {
        $user = $request->user();
        $contact = $this->service->approveFromDashboard($user, (int)$id);

        return ApiResponse::success($contact, 'Trusted contact approved', 200);
    }

    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $this->service->rejectFromDashboard($user, (int)$id);

        return ApiResponse::success(null, 'Trusted contact rejected', 200);
    }
}
