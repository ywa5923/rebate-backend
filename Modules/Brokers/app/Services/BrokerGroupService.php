<?php

namespace Modules\Brokers\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Brokers\Models\BrokerGroup;
use Modules\Brokers\Repositories\BrokerGroupRepository;

class BrokerGroupService
{
    public function __construct(
        private BrokerGroupRepository $brokerGroupRepository,
    ) {
    }

    public function getBrokerGroupList(int $perPage = 15, string $orderBy = 'id', string $orderDirection = 'asc', array $filters = []): LengthAwarePaginator
    {
        return $this->brokerGroupRepository->getPaginated($perPage, $orderBy, $orderDirection, $filters);
    }

    public function createBrokerGroup(array $data): BrokerGroup
    {
        $data['brokers'] = $this->normalizeBrokerIds($data['brokers'] ?? []);

        return $this->brokerGroupRepository->createBrokerGroup($data);
    }

    public function updateBrokerGroup(int $id, array $data): BrokerGroup
    {
        $data['brokers'] = $this->normalizeBrokerIds($data['brokers'] ?? []);

        return $this->brokerGroupRepository->updateBrokerGroup($id, $data);
    }

    public function getBrokerGroupById(int $id): BrokerGroup
    {
        return $this->brokerGroupRepository->getBrokerGroupById($id);
    }

    public function getBrokerGroupDetails(int $id): array
    {
        $brokerGroup = $this->getBrokerGroupById($id);

        $brokers = $brokerGroup->brokers
            ->map(function ($broker): ?array {
                $tradingName = $broker->dynamicOptionsValues->firstWhere('option_slug', 'trading_name')?->value;
                if (empty($tradingName)) {
                    return null;
                }

                return [
                    'label' => $tradingName,
                    'value' => $broker->id,
                ];
            })
            ->filter()
            ->sortBy(fn (array $broker): string => mb_strtolower($broker['label']))
            ->values()
            ->all();

        return [
            'id' => $brokerGroup->id,
            'name' => $brokerGroup->name,
            'description' => $brokerGroup->description,
            'is_active' => $brokerGroup->is_active,
            'brokers' => $brokers,
        ];
    }

    public function deleteBrokerGroup(int $id): bool
    {
        return $this->brokerGroupRepository->deleteBrokerGroup($id);
    }

    public function searchBrokersByTradingName(string $tradingName): Collection
    {
        return $this->brokerGroupRepository->searchBrokersByTradingName($tradingName);
    }

    private function normalizeBrokerIds(array $brokers): array
    {
        return collect($brokers)
            ->map(function ($broker) {
                if (is_array($broker)) {
                    return $broker['value'] ?? null;
                }

                return $broker;
            })
            ->filter(fn ($brokerId) => $brokerId !== null && $brokerId !== '')
            ->map(fn ($brokerId) => (int) $brokerId)
            ->values()
            ->all();
    }
}
