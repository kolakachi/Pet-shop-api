<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ];
        $user = request()->attributes->get('user');
        if ($user) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,'.$user->id;
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(
            response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Failed Validation',
                'errors' => $errors,
                'extra' => [],
            ], 422)
        );
    }
}
