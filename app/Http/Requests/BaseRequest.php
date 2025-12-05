<?php

namespace App\Http\Requests;
use App\Tables\TableConfigInterface;
use App\Forms\FormConfigInterface;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    abstract protected function tableConfigClass(): ?string;
    abstract protected function formConfigClass(): ?string;
    private ?TableConfigInterface $cachedTableConfig = null;
    private ?FormConfigInterface $cachedFormConfig = null;

    public function  getTableConfig(): ?TableConfigInterface
    {
        if($this->tableConfigClass() === null) {
            return null;
        }
        return $this->cachedTableConfig ??= app($this->tableConfigClass());
    }

    public function getFormConfig(): ?FormConfigInterface
    {
        if($this->formConfigClass() === null) {
            return null;
        }
        return $this->cachedFormConfig ??= app($this->formConfigClass());
    }

      /**
     * Get the filters array from the request.
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = [];
        $tableConfig = $this->getTableConfig();
        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];
       
        foreach ($filtersConstraints as $key) {
            if ($this->has($key) && $this->filled($key)) {
                $filters[$key] = $this->input($key);
            }
        }
       
        return $filters;
    }
}
