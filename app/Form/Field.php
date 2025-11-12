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
            'required' => in_array('required', $rules),
            'validation' => $rules,
        ];
    }

    public static function select(string $label, array $options, array $rules = []): array
    {
        return [
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'required' => in_array('required', $rules),
            'validation' => $rules,
        ];
    }
}
