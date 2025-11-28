<?php

namespace App\Policies;

use App\Models\DriverHandover;
use App\Models\User;

class DriverHandoverPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, DriverHandover $handover): bool
    {
        return $user !== null;
    }

    public function create(?User $user): bool
    {
        return $user !== null;
    }

    public function update(?User $user, DriverHandover $handover): bool
    {
        return $user !== null;
    }

    public function delete(?User $user, DriverHandover $handover): bool
    {
        return $user !== null;
    }
}

