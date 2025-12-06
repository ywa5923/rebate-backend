<?php



namespace Modules\Brokers\Forms;

use App\Forms\Form;
use App\Forms\Field;

use Modules\Brokers\Models\Zone;

class ZoneForm extends Form
{
    public function getFormData(): array
    {
        return [
            'name' => 'Broker Option',
            'description' => 'Broker option form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Country Definitions',
                    'fields' => [
                        'name' => Field::text('Name', ['required'=>true, 'min'=>3, 'max'=>100]),
                        'zone_code' => Field::text('Zone Code', ['required'=>true, 'min'=>2, 'max'=>10]),
                        'description' => Field::text('Description', ['required'=>false, 'min'=>0, 'max'=>255])
                    ]
                    
                    ]
                
            ]
        ];
    }

   

}
