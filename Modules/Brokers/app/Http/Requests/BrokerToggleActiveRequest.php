<?php

namespace Modules\Brokers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Brokers\Models\Broker;

class BrokerToggleActiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $broker = $this->route('broker');
        $user = $this->user();
        if (!$user) return false;
        if ($user->tokenCan('*')) return true;

       
        $countryId = $broker->country_id;
        $zoneId = $countryId ? $broker->country->zone_id : null;

        if($countryId){
            if($user->tokenCan("country:manage:{$countryId}") || $user->tokenCan("country:edit:{$countryId}")){
                return true;
            }
        }
        if($zoneId){
            if($user->tokenCan("zone:manage:{$zoneId}") ||  $user->tokenCan("zone:edit:{$zoneId}")){
                return true;
            }
        }
    
        return false;
    }

    protected function prepareForValidation(): void
    {
        // $this->merge([
        //     'id' => (int)$this->route('id'), // bring path {id} into input
        // ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           // 'id' => 'required|integer|exists:brokers,id',
        ];
    }
}
