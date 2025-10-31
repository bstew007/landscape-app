<?php
namespace App\Services\Enhancements;

interface EnhancementCalculatorInterface
{
    public function calculate(array $input): array;
}
