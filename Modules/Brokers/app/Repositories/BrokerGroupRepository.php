<?php

namespace Modules\Brokers\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerGroup;

class BrokerGroupRepository implements RepositoryInterface
{
    use RepositoryTrait;

    protected BrokerGroup $model;

    public function __construct(BrokerGroup $model)
    {
        $this->model = $model;
    }

    public function getPaginated(int $perPage = 15, string $orderBy = 'id', string $orderDirection = 'asc', array $filters = []): LengthAwarePaginator
    {
        // Varianta echivalenta cu dot notation:
        // ->with(['brokers.dynamicOptionsValues' => fn ($q) => $q->where('option_slug', 'trading_name')])
        $query = $this->model->query()->with([
            'brokers' => function ($brokerQuery): void {
                $brokerQuery->with([
                    'dynamicOptionsValues' => function ($optionValueQuery): void {
                        $optionValueQuery->where('option_slug', 'trading_name');
                    },
                ]);
            },
        ]);

        // Apply filters
        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (! empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }

        if (! empty($filters['broker'])) {
            $query->whereHas('brokers', function ($brokerQuery) use ($filters): void {
                $brokerQuery->whereHas('dynamicOptionsValues', function ($optionValueQuery) use ($filters): void {
                    $optionValueQuery
                        ->where('option_slug', 'trading_name')
                        ->where('value', 'like', "%{$filters['broker']}%");
                });
            });
        }
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        // Apply ordering
        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($perPage);
    }

    public function createBrokerGroup(array $data): BrokerGroup
    {
        return DB::transaction(function () use ($data): BrokerGroup {
            $brokerGroup = $this->model->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => (bool) $data['is_active'],
            ]);

            $brokerIds = collect($data['brokers'] ?? [])->map(fn ($id) => (int) $id)->values()->all();
            $brokerGroup->brokers()->sync($brokerIds);

            return $brokerGroup->load([
                'brokers' => function ($brokerQuery): void {
                    $brokerQuery->with([
                        'dynamicOptionsValues' => function ($optionValueQuery): void {
                            $optionValueQuery->where('option_slug', 'trading_name');
                        },
                    ]);
                },
            ]);
        });
    }

    public function updateBrokerGroup(int $id, array $data): BrokerGroup
    {
        return DB::transaction(function () use ($id, $data): BrokerGroup {
            $brokerGroup = $this->model->findOrFail($id);
            $brokerGroup->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => (bool) $data['is_active'],
            ]);

            $brokerIds = collect($data['brokers'] ?? [])->map(fn ($brokerId) => (int) $brokerId)->values()->all();
            $brokerGroup->brokers()->sync($brokerIds);

            return $brokerGroup->load([
                'brokers' => function ($brokerQuery): void {
                    $brokerQuery->with([
                        'dynamicOptionsValues' => function ($optionValueQuery): void {
                            $optionValueQuery->where('option_slug', 'trading_name');
                        },
                    ]);
                },
            ]);
        });
    }

    public function getBrokerGroupById(int $id): BrokerGroup
    {
        return $this->model->query()
            ->with([
                'brokers' => function ($brokerQuery): void {
                    $brokerQuery->with([
                        'dynamicOptionsValues' => function ($optionValueQuery): void {
                            $optionValueQuery->where('option_slug', 'trading_name');
                        },
                    ]);
                },
            ])
            ->findOrFail($id);
    }

    public function deleteBrokerGroup(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $brokerGroup = $this->model->findOrFail($id);
            $brokerGroup->brokers()->detach();

            return (bool) $brokerGroup->delete();
        });
    }

    public function searchBrokersByTradingName(string $tradingName): Collection
    {
        return Broker::query()
            ->whereHas('dynamicOptionsValues', function ($optionValueQuery) use ($tradingName): void {
                $optionValueQuery
                    ->where('option_slug', 'trading_name')
                    ->where('value', 'like', "%{$tradingName}%");
            })
            ->with([
                'dynamicOptionsValues' => function ($optionValueQuery): void {
                    $optionValueQuery->where('option_slug', 'trading_name');
                },
            ])
            ->get()
            ->map(function (Broker $broker): array {
                return [
                    'value' => $broker->id,
                    'label' => $broker->dynamicOptionsValues->first()?->value,
                ];
            })
            ->values();
    }
}
