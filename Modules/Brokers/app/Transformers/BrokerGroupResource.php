<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $brokers = $this->brokers
        //     ->map(function ($broker) {
        //         $tradingName = $broker->dynamicOptionsValues->first()?->value;

        //         return [
        //             'label' => $tradingName,
        //             'value' => $broker->id,
        //         ];
        //     })
        //     ->sortBy(fn (array $broker): string => mb_strtolower($broker['label'] ?? ''))
        //     ->values()
        //     ->toArray();
        $brokers = $this->brokers
            ->map(function ($broker): ?string {
                $tradingNameOption = $broker->dynamicOptionsValues
                    ->firstWhere('option_slug', 'trading_name');

                return $tradingNameOption?->value;
            })
            ->filter()
            ->sortBy(fn (string $broker): string => mb_strtolower($broker))
            ->values()
            ->implode(';');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'brokers' => $brokers,
        ];
    }
}
