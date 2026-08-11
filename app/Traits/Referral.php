<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Referral
{
    public function donatersPercent()
    {
        $myDonaters = User::query()
            ->where('invite_referral_code', $this->referral_code)
            ->pluck('id');

        $myDonatersPayments = User::query()
            ->whereIn('users.id', $myDonaters)
            ->selectRaw('users.*, (select sum(amount) from payments where user_id=users.id) as paymentsAmount')
            ->when(\request('asdasd'), fn(Builder $builder) => $builder->dd())
            ->get();

        $donatersDependencies = [
            [],
            ['donatersCount' => 5, 'donatePercent' => 0.25, 'widthOffset' => 8],
            ['donatersCount' => 10, 'donatePercent' => 0.5, 'widthOffset' => 25],
            ['donatersCount' => 50, 'donatePercent' => 0.75, 'widthOffset' => 48],
            ['donatersCount' => 100, 'donatePercent' => 1, 'widthOffset' => 73],
            ['donatersCount' => 200, 'donatePercent' => 1.5, 'widthOffset' => 100],
        ];

        $currentPosition = 0;
        foreach ($donatersDependencies as $k => $level) {
            if ($level) {
                if (1 === $k && $myDonaters->count() < $level['donatersCount']) {
                    break;
                }
                if ($myDonaters->count() === $level['donatersCount']) {
                    $currentPosition = $k;
                    break;
                }
                if (isset($donatersDependencies[$k + 1]) && $myDonaters->count() > $level['donatersCount'] && $myDonaters->count() < $donatersDependencies[$k + 1]['donatersCount']) {
                    $currentPosition = $k;
                    break;
                }
            }
        }
        if (!$currentPosition) {
            $donatersPercent = 0;
        } else {
            $donatersPercent = $donatersDependencies[$currentPosition]['donatePercent'];
        }

        return $donatersPercent;

    }
}
