<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RazorpayCheckout extends Page
{
    protected static ?string $slug = 'razorpay-checkout';
    
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.razorpay-checkout';

    public ?string $orderId = null;
    public ?int $amount = null;
    public string $currency = 'USD';
    public ?string $keyId = null;
    public ?int $credits = null;
    public $user;

    public function getTitle(): string
    {
        return 'Complete Payment';
    }

    public function mount(): void
    {
        // Pull everything from the query string so we don't have to pass
        // complex objects from the controller into the Livewire page.
        $this->orderId = request()->query('order_id');
        $this->amount = (int) request()->query('amount', 0);
        $this->currency = (string) request()->query('currency', 'USD');
        $this->keyId = request()->query('key_id');
        $this->credits = request()->query('credits');
        $this->user = auth()->user();
    }

    protected function getViewData(): array
    {
        return [
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'keyId' => $this->keyId,
            'credits' => $this->credits,
            'user' => $this->user,
        ];
    }
}

