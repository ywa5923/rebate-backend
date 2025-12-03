<?php

namespace App\Form;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Form
{
    abstract public function getFormData();
    public const MODE_CREATE = 'create';
    public const MODE_UPDATE = 'update';

    public function getFormConstraints($mode = self::MODE_CREATE): array
    {
        $constraints = [];
        foreach ($this->getFormData()['sections'] as $section) {
            foreach ($section['fields'] as $key => $field) {
                $validationRules = $field['validation'];
                $validationString = "";
                if ($field['type'] == 'array') {
                    $validationString .= "array|";
                } else if ($field['type'] == 'number') {
                    $validationString .= "numeric|";
                } else if ($field['type'] == 'boolean') {
                    $validationString .= "boolean|";
                } else if ($field['type'] == 'select') {
                    $validationString .= "string|";
                }
                else if ($field['type'] == 'text' || $field['type'] == 'string') {
                    $validationString .= "string|";
                }
                
                foreach ($validationRules as $rule => $value) {
                    
                    if ($rule == 'required' && $value == true) {
                        $validationString .= "required|";
                    } 
                    if ($rule == 'nullable' && $value == true) {
                        $validationString .= "nullable|";
                    }
                    //add filter type
                    

                    //ADD RULSES CONSTRAINTS

                    if ($rule == 'min' && $value != null) {
                        $validationString .= "min:" . $value . "|";
                    } else if ($rule == 'max' && $value != null) {
                        $validationString .= "max:" . $value . "|";
                    } else if ($rule == 'in' && $value != null) {
                        $validationString .= "in:" . $value . "|";
                    } else if ($rule == 'exists' && $value != null) {
                        $validationString .= "exists:" . $value . "|";
                    } else if ($rule == 'unique' && $value != null) {
                        $validationString .= "unique:" . $value . "|";
                    }
                }
                $constraints[$key] = rtrim($validationString, "|");
            }
        }
        return $constraints;
    }

    public function getDistinctOptions(string $modelClass, string $column): array
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a model class-string. Got [%s].',
                $modelClass
            ));
        }

        /** @var class-string<Model> $modelClass */
        $hasEmptyValues = $modelClass::query()
            ->whereNull($column)
            ->orWhere($column, '=', '')
            ->exists();

        $options = $modelClass::query()
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->map(function ($value) {
                return [
                    'value' => $value,
                    'label' => ucfirst(str_replace('_', ' ', $value)),
                ];
            })
            ->values()
            ->toArray();

        if ($hasEmptyValues) {
            array_unshift($options, [
                'value' => '__EMPTY__',
                'label' => 'Empty',
            ]);
        }

        return $options;
    }
}
