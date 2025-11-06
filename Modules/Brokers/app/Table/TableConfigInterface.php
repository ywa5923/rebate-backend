<?php
namespace Modules\Brokers\Table;

interface TableConfigInterface
{
    public function columns(): array;
    public function filters(): array;
}
?>