<?php

namespace Modules\Brokers\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\Repositories\BrokerRepository;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Repositories\BrokerOptionRepository;

class BrokerOptionService
{
    public function __construct(protected BrokerOptionRepository $brokerOptionRepository)
    {
    }

    public function getAllBrokerOptions(array $filters=[],string $orderBy='id',string $orderDirection='asc',int $perPage=15):LengthAwarePaginator
    {
       
        return $this->brokerOptionRepository->getAllBrokerOptions($filters, $orderBy, $orderDirection, $perPage);
    }

    public function createBrokerOption(array $data): BrokerOption
    {
        // Convert category_name to option_category_id
        if (isset($data['category_name'])) {
            $data['option_category_id'] = $data['category_name'];
            unset($data['category_name']);
        }
        
        // Convert dropdown_list_attached to dropdown_category_id
        if (isset($data['dropdown_list_attached'])) {
            $data['dropdown_category_id'] = $data['dropdown_list_attached'];
            unset($data['dropdown_list_attached']);
        }
        
        // Set default_language if not provided
        if (!isset($data['default_language'])) {
            $data['default_language'] = 'en'; // Default to English
        }
        
        // meta_data conversion is handled automatically by the model's setMetaDataAttribute mutator
        return $this->brokerOptionRepository->create($data);
    }

    public function updateBrokerOption(array $data, $id): ?BrokerOption
    {
        // Convert category_name to option_category_id
        if (isset($data['category_name'])) {
            $data['option_category_id'] = $data['category_name'];
            unset($data['category_name']);
        }
        
        // Convert dropdown_list_attached to dropdown_category_id
        if (isset($data['dropdown_list_attached'])) {
            $data['dropdown_category_id'] = $data['dropdown_list_attached'];
            unset($data['dropdown_list_attached']);
        }
       
     
        // meta_data conversion is handled automatically by the model's setMetaDataAttribute mutator
        return $this->brokerOptionRepository->update($data, $id);
    }

    public function deleteBrokerOption(int $id): bool
    {
        return $this->brokerOptionRepository->delete($id);
    }

    public function getBrokerOptionById($id): ?BrokerOption
    {
        return $this->brokerOptionRepository->findWith($id, ['category', 'dropdownCategory']);
    }
}
