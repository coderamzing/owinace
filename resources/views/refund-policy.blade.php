@extends('layouts.frontend')

@section('title', 'Refund Policy - ' . config('app.name', 'Owinace'))

@section('content')
<div class="w-full py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Refund Policy</h1>
            <p class="text-xl text-gray-600">
                Please read our refund policy carefully
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-l-4 border-indigo-500 p-4 rounded-lg mb-8">
                <p class="text-sm font-semibold text-gray-700 mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Last Updated: {{ date('F d, Y') }}
                </p>
            </div>

            <!-- Important Notice -->
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 p-6 rounded-lg mb-8">
                <h3 class="text-red-600 text-xl font-bold mb-3 mt-0"><i class="fas fa-exclamation-triangle mr-2"></i>Important Notice</h3>
                <p class="text-red-900 font-medium mb-0">
                    All credit purchases on {{ config('app.name', 'Owinace') }} are final and non-refundable. 
                    Please review your purchase carefully before completing the transaction.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">1. General Refund Policy</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    At {{ config('app.name', 'Owinace') }}, we maintain a strict no-refund policy for all credit purchases. 
                    Once credits are added to your account, they cannot be refunded, exchanged, or transferred under any circumstances.
                </p>
                <p class="text-gray-600 leading-relaxed mb-4">
                    By purchasing credits on our platform, you acknowledge and agree to this policy. We encourage all users to 
                    carefully review their credit requirements before making a purchase.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">2. Non-Refundable Credits</h2>
                <p class="text-gray-600 leading-relaxed mb-4">The following applies to all credit purchases:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li class="leading-relaxed"><strong>No Refunds:</strong> Credits purchased on {{ config('app.name', 'Owinace') }} are non-refundable under any circumstances</li>
                    <li class="leading-relaxed"><strong>No Exchanges:</strong> Credits cannot be exchanged for cash, other services, or alternative payment methods</li>
                    <li class="leading-relaxed"><strong>No Transfers:</strong> Credits are tied to your account and cannot be transferred to other users or accounts</li>
                    <li class="leading-relaxed"><strong>No Chargebacks:</strong> Initiating a chargeback or payment dispute will result in immediate account suspension</li>
                    <li class="leading-relaxed"><strong>Expired Credits:</strong> Credits that expire due to inactivity or terms of service are not eligible for refund</li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">3. Payment Processing</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    All credit purchases are processed immediately upon successful payment confirmation. Once the transaction is 
                    completed and credits are added to your account, the purchase is considered final.
                </p>
                <p class="text-gray-600 leading-relaxed mb-4">
                    In rare cases where duplicate charges occur due to technical errors, please contact our support team 
                    immediately. We will investigate such cases and issue appropriate refunds only for verified duplicate transactions.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">4. Account Termination</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    If your account is suspended or terminated for any reason, including but not limited to violation of our 
                    Terms of Service, any remaining credits in your account will be forfeited. No refunds will be provided 
                    for unused credits upon account termination.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">5. Technical Issues</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    While we strive to maintain a reliable platform, we are not responsible for refunds due to:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li class="leading-relaxed">Internet connectivity issues on your end</li>
                    <li class="leading-relaxed">User error or misuse of the platform</li>
                    <li class="leading-relaxed">Incompatibility with your device or browser</li>
                    <li class="leading-relaxed">Third-party service disruptions</li>
                    <li class="leading-relaxed">Scheduled or emergency maintenance periods</li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">6. Exceptional Circumstances</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    The only situations where refunds may be considered are:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li class="leading-relaxed"><strong>Duplicate Charges:</strong> If you were accidentally charged multiple times for the same transaction</li>
                    <li class="leading-relaxed"><strong>Unauthorized Transactions:</strong> If your payment method was used without your authorization (subject to verification and fraud investigation)</li>
                    <li class="leading-relaxed"><strong>System Errors:</strong> If credits were not delivered to your account due to a verified technical error on our end</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mb-4">
                    All refund requests under exceptional circumstances must be submitted within 48 hours of the transaction 
                    and will be reviewed on a case-by-case basis. The decision of {{ config('app.name', 'Owinace') }} management 
                    regarding refunds is final.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">7. How to Report Issues</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    If you believe you have experienced one of the exceptional circumstances listed above, please contact our 
                    support team immediately with the following information:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li class="leading-relaxed">Your account email address</li>
                    <li class="leading-relaxed">Transaction ID or payment reference number</li>
                    <li class="leading-relaxed">Date and time of the transaction</li>
                    <li class="leading-relaxed">Amount charged</li>
                    <li class="leading-relaxed">Detailed description of the issue</li>
                    <li class="leading-relaxed">Supporting documentation or screenshots</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mb-4">
                    <strong>Contact Email:</strong> support@owinace.com<br>
                    <strong>Response Time:</strong> We aim to respond to all inquiries within 24-48 business hours
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">8. Credit Usage and Expiration</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    Credits are meant to be used within a reasonable timeframe. Please check your account regularly and use 
                    your credits before they expire. We may implement expiration policies for unused credits, and expired 
                    credits are not eligible for refunds.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">9. Changes to This Policy</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    We reserve the right to modify this refund policy at any time. Any changes will be posted on this page 
                    with an updated "Last Updated" date. Your continued use of {{ config('app.name', 'Owinace') }} after 
                    any changes constitutes acceptance of the new policy.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">10. Acceptance of Policy</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    By purchasing credits on {{ config('app.name', 'Owinace') }}, you acknowledge that you have read, 
                    understood, and agree to be bound by this refund policy. If you do not agree with this policy, 
                    please do not make any purchases on our platform.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-4">11. Contact Information</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    If you have any questions about this refund policy, please contact us:
                </p>
                <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                    <li class="leading-relaxed"><strong>Email:</strong> support@owinace.com</li>
                    <li class="leading-relaxed"><strong>Support Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM (IST)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

