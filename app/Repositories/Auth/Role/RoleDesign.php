<?php

namespace App\Repositories\Auth\Role;

use LaravelEasyRepository\Repository;

interface RoleDesign extends Repository{
    public function permission();
    public function store($param);
}
