<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\ChallengeAmount;

class ChallengeAmountRepository
{
    protected ChallengeAmount $model;

    public function __construct(ChallengeAmount $model)
    {
        $this->model = $model;
    }

    public function findById(int $id, ?int $broker_id = null): ?ChallengeAmount
    {
        $query = $this->model->newQuery();
        if (isset($broker_id)) {
            $query->where('id', $id)->whereHas('challengeCategory', function ($query) use ($broker_id) {
                $query->where('broker_id', $broker_id);
            });
        } else {
            $query->where('id', $id);
        }

        return $query->first();
    }

    public function findDefaultAmountById(int $id): ?ChallengeAmount
    {
        return $this->model->newQuery()->whereHas('challengeCategory', function ($query) {
            $query->whereNull('broker_id');
        })->where('id', $id)->first();
    }

    public function cloneAmount(int $default_amount_id_to_clone, int $order, int $broker_category_id, ?string $amount_currency = null): ChallengeAmount
    {
        $defaultAmount = $this->findDefaultAmountById($default_amount_id_to_clone);
        if (! $defaultAmount) {
            throw new \Exception('Amount not found'.$default_amount_id_to_clone);
        }

        return $this->model->create([
            'amount' => $defaultAmount->amount,
            'currency' => $amount_currency,
            'order' => $order,
            'challenge_category_id' => $broker_category_id,
        ]);
    }
}
