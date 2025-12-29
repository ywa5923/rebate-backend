<?php



namespace Modules\Auth\Forms;

use App\Forms\Form;
use App\Forms\Field;
use InvalidArgumentException;

use Modules\Auth\Models\PlatformUser;
use Modules\Brokers\Models\Broker;
use Modules\Translations\Models\Country;
use Modules\Brokers\Models\Zone;

class UserPermissionForm extends Form
{
    protected $permissionService;
    private $permissionType;
    const BROKER_PERMISSION_TYPE = 'broker';
    const COUNTRY_PERMISSION_TYPE = 'country';
    const ZONE_PERMISSION_TYPE = 'zone';
    const SEO_PERMISSION_TYPE = 'seo';
    const TRANSLATOR_PERMISSION_TYPE = 'translator';

    public function __construct(string $permissionType,$permissionService)
    {
        //parent::__construct();
        $allowedTypes = [
            self::BROKER_PERMISSION_TYPE,
            self::COUNTRY_PERMISSION_TYPE,
            self::ZONE_PERMISSION_TYPE,
            self::SEO_PERMISSION_TYPE,
            self::TRANSLATOR_PERMISSION_TYPE,
        ];
        if (!in_array($permissionType, $allowedTypes, true)) {
            throw new InvalidArgumentException('Invalid permission type: ' . $permissionType);
        }
        $this->permissionType = $permissionType;
        $this->permissionService = $permissionService;
    }

    public function getFormData(): array
    {
        if ($this->permissionType == self::BROKER_PERMISSION_TYPE) {
            $resourceList = $this->permissionService->getOrderedBrokersList();
        } else {
            $resourceList = $this->getOptionsList($this->getResourceClass($this->permissionType), 'name');
        }

        if($this->permissionType == self::SEO_PERMISSION_TYPE || $this->permissionType == self::TRANSLATOR_PERMISSION_TYPE) {
            $resourceIdLabel = 'Select Country';
        } else {
            $resourceIdLabel = 'Select '.ucfirst($this->permissionType);
        }
 
       // $resourceLabel = ucfirst($this->permissionType);
        return [
            'name' => 'User Permission',
            'description' => "User permission form configuration",
            'sections' => [
                'definitions' => [
                    'label' => ucfirst($this->permissionType) . " Permission Type",
                    'fields' => [
                        //'broker_type' => Field::select('Broker Type', $this->getBrokerTypes(),['required'=>true,'exists'=>'broker_types,id']),
                        //'subject_type' => Field::select('Subject Type', $this->getSubjectTypes(),['required'=>true]),
                        // 'subject_id' => Field::text('Subject ID', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'subject_id' => Field::select('Select Platform User', $this->getOptionsList(PlatformUser::class, 'name'), ['required' => true]),
                        //'permission_type' => Field::select('Permission Type', $this->getPermissionTypes(),['required'=>true]),
                        'resource_id' => Field::select($resourceIdLabel, $resourceList, ['required' => true]),
                        //'resource_value' => Field::text('Resource Value', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'action' => Field::select('Action', $this->getActions(), ['required' => true]),
                        'is_active' => Field::select('Is Active', $this->booleanOptions(), ['required' => true]),
                    ]

                ]

            ]
        ];
    }

    private function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No'],
        ];
    }

    private function getSubjectTypes(): array
    {
        return [
            ['value' => 'PlatformUser', 'label' => 'Platform User'],
            ['value' => 'BrokerTeamUser', 'label' => 'Broker Team User'],
        ];
    }

    private function getPermissionTypes(): array
    {
        return [
            ['value' => 'broker', 'label' => 'Broker'],
            ['value' => 'country', 'label' => 'Country'],
            ['value' => 'zone', 'label' => 'Zone'],
            ['value' => 'seo', 'label' => 'SEO'],
            ['value' => 'translator', 'label' => 'Translator'],
        ];
    }
    private function getResourceClass(string $permissionType): ?string
    {
        switch ($permissionType) {
            case 'broker':
                return Broker::class;
            case 'country':
            case 'seo':
            case 'translator':
                return Country::class;
            case 'zone':
                return Zone::class;
            default:
                return null;
        }
    }

    private function getActions(): array
    {
        return [
            ['value' => 'view', 'label' => 'View'],
            ['value' => 'edit', 'label' => 'Edit'],
            ['value' => 'delete', 'label' => 'Delete'],
            ['value' => 'manage', 'label' => 'Manage'],
        ];
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
