<?php

namespace App\Http\Requests;

use App\Tables\TableConfigInterface;
use App\Forms\FormConfigInterface;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    abstract protected function tableConfigClass(): ?string;
    abstract protected function formConfigClass(): ?string;
    private ?TableConfigInterface $cachedTableConfig = null;
    private ?FormConfigInterface $cachedFormConfig = null;

    public function  getTableConfig(): ?TableConfigInterface
    {
        if ($this->tableConfigClass() === null) {
            return null;
        }
        $config = app($this->tableConfigClass());

        if (! $config instanceof TableConfigInterface) {
            throw new \LogicException(
                $this->tableConfigClass() . ' must implement TableConfigInterface'
            );
        }
        return $this->cachedTableConfig ??= $config;
    }

    public function getFormConfig(): ?FormConfigInterface
    {
        $config = app($this->formConfigClass());

        if (! $config instanceof FormConfigInterface) {
            throw new \LogicException(
                $this->formConfigClass().' must implement FormConfigInterface'
            );
        }
        return $this->cachedFormConfig ??= $config;
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
        if($tableConfig === null) {
            return [];
        }
        $filtersConstraints = $tableConfig?->getFiltersConstraints() ?? [];

       
        foreach ($filtersConstraints as $key=>$value) {
            if ($this->has($key) && $this->filled($key)) {
                $filters[$key] = $this->input($key);
            }
        }
      
        return $filters;
    }
    /**
     * Get the order by parameter from the request.
     *
     * @param string $default
     * @return string
     */
    public function getOrderBy(string $default = 'id'): string
    {
        return $this->input('order_by', $default);
    }

    /**
     * Get the order direction parameter from the request.
     *
     * @param string $default
     * @return string
     */
    public function getOrderDirection(string $default = 'asc'): string
    {
        return strtolower($this->input('order_direction', $default));
    }

    /**
     * Get the per page parameter from the request.
     *
     * @param int $default
     * @return int
     */
    public function getPerPage(int $default = 15): int
    {
        return (int) $this->input('per_page', $default);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403)
        );
    }
}
