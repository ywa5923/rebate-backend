<?php

namespace Modules\Brokers\Forms;

use App\Forms\Form;
use App\Forms\Field;
use Modules\Brokers\Repositories\EvaluationRuleRepository;

class EvaluationForm extends Form
{
    public function __construct(protected EvaluationRuleRepository $evaluationRuleRepository) {}
    
    public function getFormData(): array
    {

       
        return [
            'name' => 'Evaluation Rules',
            'description' => 'Form configuration',
            'sections' => [
                'definitions' => [
                    'label' => 'Evaluation Rules Definitions',
                    'fields' => $assoc = array_merge(...$this->getFields() ?: [[]])
                    // result: ['copy-trading#1' => [...], 'scalping#2' => [...], ...]
                ]
            ]
        ];
    }

    private function getFields(): array
    {
        $evaluationRules = $this->evaluationRuleRepository->getAll();
        $rules = $evaluationRules
            ->map(fn($r) => [
                'id' => $r->id,
                'slug' => $r->slug,
                'label' => $r->label,
                'evaluation_options' => $r->evaluationOptions->map(fn($o) => [
                    'id' => $o->id,
                    'slug' => $o->option_value,
                    'label' => $o->option_label,
                    'is_getter' => $o->is_getter,
                    'description' => $o->description
                ])->values()->all()
            ])->values()->all();


        $fields = array_map(function ($r) {
            $evOp = array_map(function ($o) {
                return [
                    'value' => $o['id'],
                    'label' => $o['label'],
                    'is_getter' => $o['is_getter'],
                    'description' => $o['description']
                ];
            }, $r['evaluation_options']);
            return [
                $r['slug'] . '#' . $r['id'] => Field::select($r['label'], $evOp, ['required' => true, 'exists' => 'evaluation_options,id']),
            ];
        }, $rules);
        return $fields;
    }
}
