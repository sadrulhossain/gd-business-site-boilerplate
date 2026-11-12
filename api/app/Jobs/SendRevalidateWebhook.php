<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Tells the portal which content tags changed so it can revalidate its cache.
 * The body is signed: HMAC-SHA256 of "<timestamp>.<body>" (see config/portal.php).
 * Unique until processing starts, so a burst of saves collapses into one call per tag set.
 */
class SendRevalidateWebhook implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [10, 60, 300, 900];

    /** @param  list<string>  $tags */
    public function __construct(public array $tags) {}

    public function uniqueId(): string
    {
        return implode(',', $this->tags);
    }

    public function handle(): void
    {
        $url = config('portal.revalidate_url');

        if (blank($url)) {
            return;
        }

        $secret = config('portal.revalidate_secret');

        if (blank($secret)) {
            throw new RuntimeException('PORTAL_REVALIDATE_SECRET is required when PORTAL_REVALIDATE_URL is set.');
        }

        $timestamp = (string) time();
        $body = json_encode(['tags' => $this->tags], JSON_THROW_ON_ERROR);

        Http::withHeaders([
            'X-Webhook-Timestamp' => $timestamp,
            'X-Webhook-Signature' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret),
        ])
            ->withBody($body, 'application/json')
            ->timeout(5)
            ->post($url)
            ->throw();
    }
}
