<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'tier_id' => 'required|exists:tiers,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'workspace_name' => 'required|string|max:255',
        ]);

        $tier = Tier::findOrFail($request->tier_id);

        if (!$tier->is_active) {
            return redirect()->route('pricing')
                ->with('error', 'This plan is not available.');
        }

        $amount = $tier->price;
        $amountPaise = (int)($amount * 100);

        // Initialize Razorpay
        $razorpayKeyId = config('services.razorpay.key_id');
        $razorpayKeySecret = config('services.razorpay.key_secret');
        
        if (!$razorpayKeyId || !$razorpayKeySecret) {
            return redirect()->route('pricing')
                ->with('error', 'Payment gateway is not configured.');
        }

        $api = new Api($razorpayKeyId, $razorpayKeySecret);

        // Prepare notes
        $notes = [
            'type' => 'TIER',
            'tier_id' => (string)$tier->id,
        ];

        if (Auth::check()) {
            $user = Auth::user();
            $notes['user_id'] = (string)$user->id;
            $notes['workspace_id'] = (string)$user->workspace_id;
            // Use form input values (editable fields)
            $notes['workspace_name'] = $request->workspace_name;
            $notes['email'] = $request->email;
            $notes['phone'] = $request->phone;
            $notes['first_name'] = $request->first_name;
            $notes['last_name'] = $request->last_name;
        } else {
            // Store guest data in notes
            $notes['first_name'] = $request->first_name;
            $notes['last_name'] = $request->last_name;
            $notes['email'] = $request->email;
            $notes['phone'] = $request->phone;
            $notes['workspace_name'] = $request->workspace_name;
        }

        try {
            $order = $api->order->create([
                'amount' => $amountPaise,
                'currency' => 'USD',
                'notes' => $notes,
            ]);

            return view('razorpay.checkout', [
                'order' => $order,
                'key_id' => $razorpayKeyId,
                'callback_url' => route('razorpay.success'),
                'tier' => $tier,
                'user' => Auth::user(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pricing')
                ->with('error', 'Failed to create payment order: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        return view('razorpay.success', [
            'payment_id' => $request->get('payment_id'),
        ]);
    }

    public function cancel()
    {
        return view('razorpay.cancel');
    }
}

