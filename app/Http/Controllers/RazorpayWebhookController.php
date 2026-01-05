<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use App\Models\TierOrder;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        if (!$webhookSecret) {
            Log::error('Razorpay webhook secret not configured');
            return response('Webhook secret not configured', 400);
        }

        $body = $request->getContent();
        $receivedSig = $request->header('X-Razorpay-Signature');

        // Verify signature
        $expectedSig = hash_hmac('sha256', $body, $webhookSecret);

        if ($receivedSig !== $expectedSig) {
            Log::error('Razorpay webhook signature verification failed', [
                'received' => $receivedSig,
                'expected' => $expectedSig,
            ]);
            return response('Invalid signature', 400);
        }

        $payload = json_decode($body, true);
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $payment = $payload['payload']['payment']['entity'];
            $notes = $payment['notes'] ?? [];
            $type = $notes['type'] ?? null;

            if ($type === 'TIER') {
                return $this->handleTierPayment($payment, $notes);
            }
        }

        return response('OK', 200);
    }

    private function handleTierPayment($payment, $notes)
    {
        try {
            DB::beginTransaction();

            $tierId = (int)($notes['tier_id'] ?? 0);
            $amount = (float)($payment['amount'] ?? 0) / 100;
            $paymentId = $payment['id'] ?? null;

            $tier = Tier::find($tierId);
            if (!$tier) {
                throw new \Exception("Tier not found: {$tierId}");
            }

            $workspaceId = null;
            $userId = null;

            // Check if user is logged in (existing user)
            if (isset($notes['user_id']) && isset($notes['workspace_id'])) {
                $userId = (int)$notes['user_id'];
                $workspaceId = (int)$notes['workspace_id'];
            } else {
                // Create new user and workspace
                $firstName = $notes['first_name'] ?? '';
                $lastName = $notes['last_name'] ?? '';
                $email = $notes['email'] ?? '';
                $phone = $notes['phone'] ?? '';
                $workspaceName = $notes['workspace_name'] ?? 'My Workspace';

                // Check if user already exists
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // Create new user
                    $user = User::create([
                        'name' => "{$firstName} {$lastName}",
                        'email' => $email,
                        'password' => Hash::make(bin2hex(random_bytes(16))), // Random password
                        'type' => 'admin',
                        'email_verified_at' => now(), // Skip email verification
                    ]);

                    // Create workspace
                    $workspace = Workspace::create([
                        'name' => $workspaceName,
                        'owner_id' => $user->id,
                    ]);

                    // Set user's workspace
                    $user->update([
                        'workspace_id' => $workspace->id,
                    ]);

                    // Assign Admin role
                    $role = Role::where('name', 'Admin')
                        ->where('workspace_id', $workspace->id)
                        ->first();

                    if ($role) {
                        $user->roles()->attach($role->id, ['workspace_id' => $workspace->id]);
                    }

                    $workspaceId = $workspace->id;
                    $userId = $user->id;
                } else {
                    // User exists, find or create their workspace
                    if (!$user->workspace_id) {
                        // Create workspace for existing user
                        $workspace = Workspace::create([
                            'name' => $workspaceName,
                            'owner_id' => $user->id,
                        ]);
                        $user->update(['workspace_id' => $workspace->id]);
                        $workspaceId = $workspace->id;
                    } else {
                        $workspaceId = $user->workspace_id;
                    }
                    $userId = $user->id;
                }
            }

            // Create TierOrder
            $tierOrder = TierOrder::create([
                'workspace_id' => $workspaceId,
                'tier_id' => $tierId,
                'amount_paid' => $amount,
                'transaction_id' => $paymentId,
                'email' => $notes['email'] ?? $user->email ?? '',
                'first_name' => $notes['first_name'] ?? explode(' ', $user->name ?? '')[0] ?? '',
                'last_name' => $notes['last_name'] ?? explode(' ', $user->name ?? '')[1] ?? '',
                'status' => 'complete',
            ]);

            // Update workspace tier
            $workspace = Workspace::find($workspaceId);
            if ($workspace) {
                $workspace->update([
                    'tier_id' => $tierId,
                    'start_at' => now(),
                    'expire_at' => now()->addYears(3), // 3 years
                ]);
            }

            DB::commit();

            Log::info('Tier payment processed successfully', [
                'tier_order_id' => $tierOrder->id,
                'workspace_id' => $workspaceId,
                'tier_id' => $tierId,
            ]);

            return response('OK', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing tier payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response('Error processing payment', 500);
        }
    }
}

