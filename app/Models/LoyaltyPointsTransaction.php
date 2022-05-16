<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LoyaltyPointsTransaction
 *
 * @property int $id
 * @property int $account_id
 * @property float $points_amount
 * @property float|null $payment_amount
 * @property string|null $payment_id
 * @property int|null $payment_time
 * @property string $description
 * @property int|null $points_rule
 * @property int $canceled
 * @property string|null $cancellation_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereCanceled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction wherePaymentTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction wherePointsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction wherePointsRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoyaltyPointsTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class LoyaltyPointsTransaction extends Model
{
    protected $table = 'loyalty_points_transaction';

    protected $fillable = [
        'account_id',
        'points_rule',
        'points_amount',
        'description',
        'payment_id',
        'payment_amount',
        'payment_time',
    ];

    public static function performPaymentLoyaltyPoints($account_id, $points_rule, $description, $payment_id, $payment_amount, $payment_time)
    {
        $points_amount = 0;

        if ($pointsRule = LoyaltyPointsRule::where('points_rule', '=', $points_rule)->first()) {
            $points_amount = match ($pointsRule->accrual_type) {
                LoyaltyPointsRule::ACCRUAL_TYPE_RELATIVE_RATE => ($payment_amount / 100) * $pointsRule->accrual_value,
                LoyaltyPointsRule::ACCRUAL_TYPE_ABSOLUTE_POINTS_AMOUNT => $pointsRule->accrual_value
            };
        }

        return LoyaltyPointsTransaction::create([
            'account_id' => $account_id,
            'points_rule' => $pointsRule?->id,
            'points_amount' => $points_amount,
            'description' => $description,
            'payment_id' => $payment_id,
            'payment_amount' => $payment_amount,
            'payment_time' => $payment_time,
        ]);
    }

    public static function withdrawLoyaltyPoints($account_id, $points_amount, $description) {
        return LoyaltyPointsTransaction::create([
            'account_id' => $account_id,
            'points_rule' => 'withdraw',
            'points_amount' => -$points_amount,
            'description' => $description,
        ]);
    }
}
