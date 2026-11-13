<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** Body `{ids: [3, 1, 2]}`: the new order of rows. Subclasses name the table and the permission. */
abstract class ReorderRequest extends FormRequest
{
    abstract protected function table(): string;

    abstract protected function permission(): string;

    public function authorize(): bool
    {
        return $this->user()?->can($this->permission()) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'distinct', 'exists:'.$this->table().',id'],
        ];
    }

    /** @return list<int> */
    public function ids(): array
    {
        return array_map('intval', $this->validated('ids'));
    }
}
