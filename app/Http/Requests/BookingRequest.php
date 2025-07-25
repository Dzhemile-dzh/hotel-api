<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
        $bookingId = $this->route('booking')?->id;
        
        return [
            'external_id' => $this->isMethod('POST') 
                ? 'required|string|unique:bookings,external_id'
                : 'sometimes|string|unique:bookings,external_id,' . $bookingId,
            'room_id' => 'required|exists:rooms,id',
            'room_type_id' => 'required|exists:room_types,id',
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:arrival_date',
            'status' => 'required|string|in:confirmed,pending,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
            'guest_ids' => 'sometimes|array',
            'guest_ids.*' => 'exists:guests,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'external_id.unique' => 'A booking with this external ID already exists.',
            'departure_date.after_or_equal' => 'Departure date must be after or equal to arrival date.',
            'status.in' => 'Status must be one of: confirmed, pending, cancelled, completed.',
        ];
    }
} 