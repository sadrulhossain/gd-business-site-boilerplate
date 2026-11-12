<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** One-time arithmetic captcha. The answer lives only in the cache (database store). */
class Captcha
{
    /** @return array{id: string, question: string} */
    public function issue(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $id = (string) Str::uuid();

        Cache::put($this->key($id), $a + $b, config('contact.captcha_ttl'));

        return ['id' => $id, 'question' => "{$a} + {$b}"];
    }

    /** True once per issued id: the entry is removed whether the answer is right or not. */
    public function check(string $id, int $answer): bool
    {
        $expected = Cache::pull($this->key($id));

        // (int): some cache stores hand numbers back as strings.
        return $expected !== null && (int) $expected === $answer;
    }

    private function key(string $id): string
    {
        return 'captcha:'.$id;
    }
}
