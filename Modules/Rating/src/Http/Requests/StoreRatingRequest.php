<?php

namespace Modules\Rating\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $whoCanReview = fn_get_setting('rating.who_can_review', 'both');
        
        if ($whoCanReview === 'logged_in' && !Auth::check()) {
            return false;
        }

        if ($whoCanReview === 'guest' && Auth::check()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'ratable_type' => 'required|string',
            'ratable_id' => 'required|integer',
            'rating' => 'required|integer|min:' . fn_get_setting('rating.min_rating', 1) . '|max:' . fn_get_setting('rating.max_rating', 5),
        ];

        // Check if review text is required
        if (fn_get_setting('rating.require_review_text', false)) {
            $rules['review'] = 'required|string|max:1000';
        } else {
            $rules['review'] = 'nullable|string|max:1000';
        }

        // Check who can review
        $whoCanReview = fn_get_setting('rating.who_can_review', 'both');
        
        if ($whoCanReview !== 'logged_in' && !Auth::check()) {
            // Guest reviewer fields
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ratable_type.required' => 'The item type is required.',
            'ratable_id.required' => 'The item ID is required.',
            'rating.required' => 'Please provide a rating.',
            'rating.min' => 'Rating must be at least :min.',
            'rating.max' => 'Rating cannot be more than :max.',
            'review.required' => 'Please write a review.',
            'guest_name.required' => 'Please provide your name.',
            'guest_email.required' => 'Please provide your email address.',
            'guest_email.email' => 'Please provide a valid email address.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        $whoCanReview = fn_get_setting('rating.who_can_review', 'both');
        
        if ($whoCanReview === 'logged_in') {
            abort(403, 'Only logged in users can submit reviews.');
        }

        if ($whoCanReview === 'guest') {
            abort(403, 'Only guests can submit reviews.');
        }

        abort(403, 'You are not authorized to submit reviews.');
    }
}
