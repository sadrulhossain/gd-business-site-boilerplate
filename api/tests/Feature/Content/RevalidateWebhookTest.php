<?php

use App\Jobs\SendRevalidateWebhook;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Post;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['portal.revalidate_url' => 'http://portal.test/api/revalidate', 'portal.revalidate_secret' => 'shh']);
});

it('posts a signed webhook with the changed tag when content is saved', function () {
    Http::fake();
    Faq::create(['question' => 'Q', 'answer' => 'A']);

    Http::assertSent(function (Request $request) {
        $timestamp = $request->header('X-Webhook-Timestamp')[0];
        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->body(), 'shh');

        return $request->url() === 'http://portal.test/api/revalidate'
            && $request->method() === 'POST'
            && $request['tags'] === ['faqs']
            && $request->header('X-Webhook-Signature')[0] === $expected;
    });
});

it('fires on update and delete too, tagged per table', function () {
    Http::fake();
    $post = Post::create(['title' => 'T', 'slug' => 't']);
    $post->update(['title' => 'U']);
    $post->delete();

    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request) => $request['tags'] === ['posts']);
});

it('stays quiet without a URL and for non-public data', function () {
    Http::fake();
    config(['portal.revalidate_url' => null]);
    Faq::create(['question' => 'Q', 'answer' => 'A']);
    Http::assertNothingSent();

    config(['portal.revalidate_url' => 'http://portal.test/api/revalidate']);
    ContactMessage::create(['name' => 'J', 'email' => 'j@example.com', 'message' => 'm']);
    Http::assertNothingSent();
});

it('fails the job (so the queue retries) on a non-2xx answer or a missing secret', function () {
    Http::fake(['*' => Http::response('nope', 500)]);
    expect(fn () => (new SendRevalidateWebhook(['faqs']))->handle())->toThrow(RequestException::class);

    config(['portal.revalidate_secret' => null]);
    expect(fn () => (new SendRevalidateWebhook(['faqs']))->handle())->toThrow(RuntimeException::class);
});

it('retries with backoff and is unique per tag set', function () {
    $job = new SendRevalidateWebhook(['faqs', 'posts']);

    expect($job->tries)->toBe(5)
        ->and($job->backoff)->not->toBeEmpty()
        ->and($job->uniqueId())->toBe('faqs,posts');
});
