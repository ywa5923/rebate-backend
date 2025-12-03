<?php

namespace App\Form;

class Field
{
    public static function text(string $label, array $rules = []): array
    {
        return [
            'type' => 'text',
            'label' => $label,
            'placeholder' => "Enter $label",
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function select(string $label,array $options, array $rules = []): array
    {
        return [
            'type' => 'select',
            'label' => $label,
            'options' => $options,
           
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function checkbox(string $label, array $rules = []): array
    {
        return [
            'type' => 'checkbox',
            'label' => $label,
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function textarea(string $label, array $rules = []): array
    {
        return [
            'type' => 'textarea',
            'label' => $label,
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function number(string $label, array $rules = []): array
    {
        return [
            'type' => 'number',
            'label' => $label,
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function array_fields(string $label,array $fields): array
    {
        return [
            'type' => 'array_fields',
            'label' => $label,
            'fields' => $fields,
            
        ];
    }

    public static function date(string $label, array $rules = []): array
    {
        return [
            'type' => 'date',
            'label' => $label,
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }

    public static function time(string $label, array $rules = []): array
    {
        return [
            'type' => 'time',
            'label' => $label,
            'required' => in_array('required', array_keys($rules)) && $rules['required'] == true,
            'validation' => $rules,
        ];
    }
}
