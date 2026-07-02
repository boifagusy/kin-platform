<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SafetyScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateSafetyConfidenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(SafetyScoreService $scoreService): void
    {
        $user = User::find($this->userId);
        
        if (!$user) {
            Log::warning('User not found for confidence update', ['user_id' => $this->userId]);
            return;
        }

        $score = $scoreService->updateForUser($user);
        $tier = $scoreService->getTier($score);

        Log::info('Confidence score updated via job', [
            'user_id' => $user->id,
            'score' => $score,
            'tier' => $tier
        ]);

        // If tier is Orange or worse, trigger escalation check
        if (in_array($tier, [SafetyScoreService::TIER_ORANGE, SafetyScoreService::TIER_RED, SafetyScoreService::TIER_BLACK])) {
            dispatch(new CheckEscalationJob($user->id));
        }
    }
}
