<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Brokers\Models\EvaluationOption;
class BrokerEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $previous_evaluation_option_value = EvaluationOption::find($this->previous_evaluation_option_id)->option_value ?? null;
       
        $public_is_getter = EvaluationOption::find($this->public_evaluation_option_id)->is_getter ?? null;
        
        return [
            'id' => $this->id,
            'broker_id' => $this->broker_id,
            'zone_id' => $this->zone_id,
            'evaluation_rule_id' => $this->evaluation_rule_id,
            'evaluation_option_id' => $this->evaluation_option_id,
            'public_evaluation_option_id' => $this->public_evaluation_option_id ?? null,
            'details' => $this->details,
            'public_details' => $this->public_details ?? null,
            'previous_evaluation_option_id' => $this->previous_evaluation_option_id ?? null,
            'previous_evaluation_option_value' => $previous_evaluation_option_value,
            'previous_details' => $this->previous_details ?? null,
            'evaluation_rule_slug' => $this->whenLoaded('evaluationRule', function () {
                return $this->evaluationRule->slug ?? null;
                // return [
                //     'id' => $this->evaluationRule->id,
                //     'slug' => $this->evaluationRule->slug ?? null,
                //     'label' => $this->evaluationRule->label ?? null,
                // ];
            }),
            'evaluation_option_value' => $this->whenLoaded('evaluationOption', function () {
                return $this->evaluationOption->option_value ?? null;
            }),
            'is_getter' => $this->whenLoaded('evaluationOption', function () {
                return $this->evaluationOption->is_getter ;
            }),
            'is_getter_for_admin'=>$public_is_getter ?? false,
            // 'evaluation_option' => $this->whenLoaded('evaluationOption', function () {
            //     return [
            //         'id' => $this->evaluationOption->id,
            //         'option_value' => $this->evaluationOption->option_value,
            //         'option_label' => $this->evaluationOption->option_label,
            //         'is_getter' => $this->evaluationOption->is_getter,
            //         'description' => $this->evaluationOption->description,
            //     ];
            // }),
            'is_updated_entry' => $this->is_updated_entry,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

