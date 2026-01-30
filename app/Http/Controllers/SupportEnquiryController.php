<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportEnquiryRequest;
use App\Models\SupportEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportEnquiryController extends Controller
{
    /**
     * Store a newly created support enquiry.
     */
    public function store(StoreSupportEnquiryRequest $request): RedirectResponse
    {
        SupportEnquiry::create($request->validated());

        return redirect()->route('contact')
            ->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
