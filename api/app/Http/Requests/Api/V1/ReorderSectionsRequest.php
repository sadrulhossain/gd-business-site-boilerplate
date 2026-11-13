<?php

namespace App\Http\Requests\Api\V1;

class ReorderSectionsRequest extends ReorderRequest
{
    protected function table(): string
    {
        return 'sections';
    }

    protected function permission(): string
    {
        return 'sections.update';
    }
}
