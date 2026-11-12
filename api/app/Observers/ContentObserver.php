<?php

namespace App\Observers;

use App\Jobs\SendRevalidateWebhook;
use Illuminate\Database\Eloquent\Model;

/** Public content changed: queue a signed revalidate webhook tagged with the table name. */
class ContentObserver
{
    /** Dispatch only once the surrounding transaction has committed. */
    public bool $afterCommit = true;

    public function saved(Model $model): void
    {
        SendRevalidateWebhook::dispatch([$model->getTable()]);
    }

    public function deleted(Model $model): void
    {
        SendRevalidateWebhook::dispatch([$model->getTable()]);
    }
}
