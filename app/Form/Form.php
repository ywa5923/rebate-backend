<?php

namespace App\Form;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Form
{
    abstract public function getFormData();

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
