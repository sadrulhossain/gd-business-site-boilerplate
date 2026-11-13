<?php

namespace App\Http\Requests\Api\V1;

class ReorderMenusRequest extends ReorderRequest
{
    protected function table(): string
    {
        return 'menus';
    }

    protected function permission(): string
    {
        return 'menus.update';
    }
}
