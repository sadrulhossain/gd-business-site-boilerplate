<?php

namespace App\Http\Requests\Api\V1;

use App\Services\Captcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Public contact form. `website` is the honeypot: real visitors never see or fill it. */
class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            'captcha_id' => ['required', 'string', 'max:64'],
            'captcha_answer' => ['required', 'integer'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * The captcha is consumed on every attempt (right or wrong, other fields valid or not),
     * so one challenge cannot be brute-forced across retries.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $id = $this->input('captcha_id');
            $answer = $this->input('captcha_answer');

            if (! is_string($id) || ! is_numeric($answer)) {
                return; // the field rules already report these
            }

            if (! app(Captcha::class)->check($id, (int) $answer)) {
                $validator->errors()->add('captcha_answer', 'The captcha answer is wrong or expired.');
            }
        }];
    }
}
