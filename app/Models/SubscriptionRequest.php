<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'plan', 'status', 'notes',
        'approved_at', 'expires_at', 'renewal_notified_at', 'session_token',
        'pesapal_merchant_ref', 'pesapal_tracking_id',
    ];

    protected function casts(): array
    {
        return [
            'approved_at'         => 'datetime',
            'expires_at'          => 'datetime',
            'renewal_notified_at' => 'datetime',
        ];
    }

    public static array $plans = [
        'weekly'     => ['label' => 'Weekly',   'price' => '$10/week',     'days' => 7,  'amount_usd' => 10.00, 'amount_ugx' => 37000],
        'monthly'    => ['label' => 'Monthly',  'price' => '$20/month',    'days' => 30, 'amount_usd' => 20.00, 'amount_ugx' => 74000],
        'two_months' => ['label' => '2 Months', 'price' => '$30/2 months', 'days' => 60, 'amount_usd' => 30.00, 'amount_ugx' => 112000],
    ];

    public function getPlanLabelAttribute(): string
    {
        return static::$plans[$this->plan]['label'] ?? $this->plan;
    }

    public function getPlanPriceAttribute(): string
    {
        return static::$plans[$this->plan]['price'] ?? '';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-700 border border-green-300',
            'rejected' => 'bg-red-100 text-red-700 border border-red-300',
            'expired'  => 'bg-gray-100 text-gray-500 border border-gray-300',
            default    => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
        };
    }
}
