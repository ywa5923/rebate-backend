<?php

namespace App\Forms;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Form implements FormConfigInterface
{
    abstract public function getFormData(): array;
    public const MODE_CREATE = 'create';
    public const MODE_UPDATE = 'update';

    public function getFormConstraints($mode = self::MODE_CREATE): array
    {
        $constraints = [];
        foreach ($this->getFormData()['sections'] as $section) {
            foreach ($section['fields'] as $key => $field) {

                if ($field['type'] == 'array_fields') {

                    //Example of array fields validation
                    //'options' => 'required|array|min:1',
                    //'options.*' => 'required|array',
                    //'options.*.slug'  => 'required|string|max:255|distinct',
                    //'options.*.value' => 'required|string|max:255',
                    //'options.*.order' => 'nullable|integer|min:0',

                    $constraints[$key] = $this->getValidationString($field);
                    foreach ($field['fields'] as $subFieldKey => $subField) {
                        $constraints[$key . '.*.' . $subFieldKey] = $this->getValidationString($subField);
                    }
                } else {
                    $constraints[$key] = $this->getValidationString($field);
                }
            } //end foreach
        } //end foreach
        return $constraints;
    }

    public function getValidationString(array $field): string
    {

        $validationString = "";
        if ($field['type'] == 'array' || $field['type'] == 'array_fields') {
            $validationString .= "array|";
        } else if ($field['type'] == 'number') {
            $validationString .= "numeric|";
        } else if ($field['type'] == 'boolean') {
            $validationString .= "boolean|";
        } else if ($field['type'] == 'select') {
            $validationString .= "string|";
        } else if ($field['type'] == 'text' || $field['type'] == 'string') {
            $validationString .= "string|";
        }

        $validationRules = $field['validation'];

        foreach ($validationRules as $rule => $value) {

            if ($rule == 'required' && $value == true) {
                $validationString .= "required|";
            }else if($rule == 'required' && $value == false) {
                $validationString .= "nullable|";
            }

            if($rule == 'sometimes' && $value == true) {
                $validationString .= "sometimes|";
            }
            
            if ($rule == 'nullable' && $value == true) {
                $validationString .= "nullable|";
            }
            //add filter type


            //ADD RULSES CONSTRAINTS

            if ($rule == 'min' && is_numeric($value)) {
                $validationString .= "min:" . $value . "|";
            } else if ($rule == 'max' && is_numeric($value)) {
                $validationString .= "max:" . $value . "|";
            } else if ($rule == 'in' && is_array(explode(',', $value))) {
                $validationString .= "in:" . $value . "|";
            } else if ($rule == 'exists' && is_string($value)) {
                $validationString .= "exists:" . $value . "|";
            } else if ($rule == 'unique' && is_string($value)) {
                $validationString .= "unique:" . $value . "|";
            }
        }
        return rtrim($validationString, "|");
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

    //get options list for a model used in a dropdown list
    /**
     * @param string $modelClass
     * @param string $column
     * @return array
     * [
     *     ['value' => '1', 'label' => 'Option 1'],
     *     ['value' => '2', 'label' => 'Option 2'],
     *     ['value' => '3', 'label' => 'Option 3'],
     * ]
     */
    public function getOptionsList(string $modelClass, string $column): array
    {
        return $modelClass::all()
            ->map(function ($item) use ($column) {
                return ['value' => $item->id, 'label' => $item->$column];
            })
            ->values()
            ->all();
    }
}
