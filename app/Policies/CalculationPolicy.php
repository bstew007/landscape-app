<?php

namespace App\Policies;

use App\Models\Calculation;
use App\Models\User;

class CalculationPolicy
{
    public function view(User $user, Calculation $calculation): bool
    {
        if (! $calculation->is_template) return false;
        if ($calculation->template_scope === 'global' || $calculation->is_global) return true;
        if ($calculation->created_by && $calculation->created_by === $user->id) return true;
        // Extend with client/property scoping if needed
        return false;
    }

    public function update(User $user, Calculation $calculation): bool
    {
        if (! $calculation->is_template) return false;
        return $calculation->created_by && $calculation->created_by === $user->id || $user->can('manage-catalogs');
    }

    public function delete(User $user, Calculation $calculation): bool
    {
        return $this->update($user, $calculation);
    }

    public function duplicate(User $user, Calculation $calculation): bool
    {
        return $this->view($user, $calculation);
    }
}
