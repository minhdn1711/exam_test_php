<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Booking search screen (search form + result list)
     */
    public function search(Request $request): View
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_contact' => ['nullable', 'string', 'max:255'],
            'checkin_date' => ['nullable', 'date'],
            'checkout_date' => ['nullable', 'date'],
        ], [
            'max' => ':attributeは:max文字以内で入力してください。',
            'date' => ':attributeには正しい日付を入力してください。',
        ], [
            'customer_name' => '顧客名',
            'customer_contact' => '顧客連絡先',
            'checkin_date' => 'チェックイン日',
            'checkout_date' => 'チェックアウト日',
        ]);

        $bookingList = null;

        // the list is shown only after the search button is pressed (empty conditions = all bookings)
        if ($request->has('search')) {
            $bookingList = Booking::query()
                ->with('hotel')
                ->when($validated['customer_name'] ?? null, function ($query, $name) {
                    $query->where('customer_name', 'like', '%' . addcslashes($name, '\\%_') . '%');
                })
                ->when($validated['customer_contact'] ?? null, function ($query, $contact) {
                    $query->where('customer_contact', 'like', '%' . addcslashes($contact, '\\%_') . '%');
                })
                ->when($validated['checkin_date'] ?? null, function ($query, $date) {
                    $query->whereDate('checkin_time', $date);
                })
                ->when($validated['checkout_date'] ?? null, function ($query, $date) {
                    $query->whereDate('checkout_time', $date);
                })
                ->orderByDesc('checkin_time')
                ->orderBy('booking_id')
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.booking.search', compact('bookingList'));
    }
}
