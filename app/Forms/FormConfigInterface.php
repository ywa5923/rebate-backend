<?php

namespace App\Forms;

interface FormConfigInterface
{
    public function getFormData(): array;
    public function getFormConstraints(): array;
}
