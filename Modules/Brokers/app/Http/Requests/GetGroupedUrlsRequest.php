<?php

namespace Modules\Brokers\Http\Requests;

use App\Utilities\ModelHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GetGroupedUrlsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'broker_id' => $this->route('broker_id'),
            'entity_type' => $this->route('entity_type'),
            'entity_id' => $this->route('entity_id'),
            'language_code' => $this->query('language_code') ?? 'en',
            'zone_code' => $this->query('zone_code'),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'broker_id' => ['required', 'integer'],
            'entity_type' => ['required', 'string'],
            'entity_id' => ['required', 'regex:/^(all|\d+)$/'],
            'language_code' => ['required', 'string'],
            'zone_code' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<int, callable(\Illuminate\Validation\Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $entityType = $this->input('entity_type');
                if (! is_string($entityType) || $entityType === '') {
                    return;
                }

                $modelClass = ModelHelper::getModelClassFromSlug($entityType);
                if (! class_exists($modelClass)) {
                    $validator->errors()->add(
                        'entity_type',
                        'Entity type must be a valid model class'
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'entity_id.regex' => 'Entity ID must be a number or "all"',
        ];
    }
}
