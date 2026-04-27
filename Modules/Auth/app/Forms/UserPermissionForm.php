<?php

namespace Modules\Auth\Forms;

use App\Forms\Field;
use App\Forms\Form;
use Modules\Auth\Enums\AuthAction;
use Modules\Auth\Enums\AuthPermission;
use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Services\UserPermissionService;
use Modules\Brokers\Models\Broker;

class UserPermissionForm extends Form
{
    public function __construct(private AuthPermission $permissionType,
        private UserPermissionService $permissionService)
    {
    }

    public function getFormData(): array
    {
        if ($this->permissionType == AuthPermission::BROKER) {
            $resourceList = $this->permissionService->getOrderedBrokersList();
        } else {
            $resourceList = $this->getOptionsList($this->permissionType->resourceModel(), 'name');
        }

        if ($this->permissionType == AuthPermission::SEO || $this->permissionType == AuthPermission::TRANSLATOR) {
            $resourceIdLabel = 'Select Country';
        } else {
            $resourceIdLabel = 'Select '.$this->permissionTypeLabel();
        }

        return [
            'name' => 'User Permission',
            'description' => 'User permission form configuration',
            'sections' => [
                'definitions' => [
                    'label' => $this->permissionTypeLabel().' Permission Type',
                    'fields' => [

                        'subject_id' => Field::select('Select Platform User', $this->getOptionsList(PlatformUser::class, 'name'), ['required' => true]),
                        //'permission_type' => Field::select('Permission Type', $this->getPermissionTypes(),['required'=>true]),
                        'resource_id' => Field::select($resourceIdLabel, $resourceList, ['required' => true]),
                        //resource_value is not required
                        //resource value is obtained in the service layer
                        'action' => Field::select('Action', $this->getActions(), ['required' => true]),
                        'is_active' => Field::select('Is Active', $this->booleanOptions(), ['required' => true]),
                    ],

                ],

            ],
        ];
    }

    private function permissionTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->permissionType->value));
    }

    private function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No'],
        ];
    }

    private function getActions(): array
    {
        $actions = AuthAction::cases();

        return array_map(function ($action) {
            return ['value' => $action->value, 'label' => ucfirst($action->value.' Action')];
        }, $actions);

    }

    private function getBrokersList(): array
    {
        $brokers = Broker::query()
            ->join('option_values as ov', function ($j) {
                $j->on('ov.broker_id', 'brokers.id')->where('ov.option_slug', 'trading_name');
            })
            ->orderBy('ov.value')
            ->get(['brokers.id', 'ov.value as trading_name'])
            ->map(fn ($b) => ['value' => $b->id, 'label' => $b->trading_name])
            ->values()
            ->all();

        // $brokers = Broker::with(['dynamicOptionsValues' => fn($q) =>
        // $q->where('option_slug', 'trading_name')->latest('id')->limit(1)])
        //     ->get()->map(function ($b) {
        //         $name = optional($b->dynamicOptionsValues->first())->value ?? "Broker #{$b->id}";
        //         return ['value' => $b->id, 'label' => $name];
        //     })->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        return $brokers;
    }
}
