<?php

namespace Tests\Concerns;

use App\Models\AdminAllowlist;

trait ActsAsAdmin
{
    protected string $adminEmail = 'admin@arquanzia.test';

    protected function seedAdmin(?string $role = 'admin'): AdminAllowlist
    {
        return AdminAllowlist::create([
            'email' => $this->adminEmail,
            'role' => $role,
            'created_by_email' => $this->adminEmail,
        ]);
    }

    protected function actingAsAdmin(): static
    {
        return $this->withSession([
            'admin_email' => $this->adminEmail,
            'admin_role' => 'admin',
        ]);
    }
}
