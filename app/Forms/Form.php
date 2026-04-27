<?php

namespace App\Forms;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Form implements FormConfigInterface
{
    abstract public function getFormData(): array;

    public const MODE_CREATE = 'create';

    public const MODE_UPDATE = 'update';

    public function getFormConstraints($mode = self::MODE_CREATE, ?int $itemId = null): array
    {
        $constraints = [];
        foreach ($this->getFormData()['sections'] as $section) {
            foreach ($section['fields'] as $key => $field) {

                if (isset($field['type']) && $field['type'] == 'array_fields') {

                    //Example of array fields validation
                    //'options' => 'required|array|min:1',
                    //'options.*' => 'required|array',
                    //'options.*.slug'  => 'required|string|max:255|distinct',
                    //'options.*.value' => 'required|string|max:255',
                    //'options.*.order' => 'nullable|integer|min:0',

                    $constraints[$key] = $this->getValidationString($field, $mode, $itemId);
                    foreach ($field['fields'] as $subFieldKey => $subField) {
                        $constraints[$key.'.*.'.$subFieldKey] = $this->getValidationString($subField, $mode, $itemId);
                    }
                } else {
                    $constraints[$key] = $this->getValidationString($field, $mode, $itemId);
                }
            } //end foreach
        } //end foreach

        return $constraints;
    }

    public function getValidationString(array $field, $mode, ?int $itemId = null): string
    {

        $validationString = '';
        if ($field['type'] == 'array' || $field['type'] == 'array_fields') {
            $validationString .= 'array|';
        } elseif ($field['type'] == 'number') {
            $validationString .= 'numeric|';
        } elseif ($field['type'] == 'boolean') {
            $validationString .= 'boolean|';
        } elseif ($field['type'] == 'select') {
            $validationString .= 'string|';
        } elseif ($field['type'] == 'text' || $field['type'] == 'string') {
            $validationString .= 'string|';
        } elseif ($field['type'] == 'multiselect') {
            $validationString .= 'array|';
        }

        $validationRules = $field['validation'];

        foreach ($validationRules as $rule => $value) {

            if ($rule == 'required' && $value == true) {
                $validationString .= 'required|';
            } elseif ($rule == 'required' && $value == false) {
                $validationString .= 'nullable|';
            }

            if ($rule == 'sometimes' && $value == true) {
                $validationString .= 'sometimes|';
            }

            if ($rule == 'nullable' && $value == true) {
                $validationString .= 'nullable|';
            }
            //add filter type

            //ADD RULSES CONSTRAINTS

            if ($rule == 'min' && is_numeric($value)) {
                $validationString .= 'min:'.$value.'|';
            } elseif ($rule == 'max' && is_numeric($value)) {
                $validationString .= 'max:'.$value.'|';
            } elseif ($rule == 'in' && is_array(explode(',', $value))) {
                $validationString .= 'in:'.$value.'|';
            } elseif ($rule == 'exists' && is_string($value)) {
                $validationString .= 'exists:'.$value.'|';
            } elseif ($rule == 'unique' && is_string($value)) {
                if ($itemId && is_numeric($itemId) && $mode == self::MODE_UPDATE) {
                    $validationString .= 'unique:'.$value.','.$itemId.',id|';
                } else {
                    $validationString .= 'unique:'.$value.'|';
                }
                // $validationString .= "unique:" . $value . "|";
            }
        }

        return rtrim($validationString, '|');
    }

    public function getDistinctOptions(string $modelClass, string $column): array
    {
        if (! is_subclass_of($modelClass, Model::class)) {
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
     * @return array
     *               [
     *               ['value' => '1', 'label' => 'Option 1'],
     *               ['value' => '2', 'label' => 'Option 2'],
     *               ['value' => '3', 'label' => 'Option 3'],
     *               ]
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
