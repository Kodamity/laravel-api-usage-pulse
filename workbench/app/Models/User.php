<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 18.02.2025 22:18
 */

namespace Workbench\App\Models;

class User extends \Illuminate\Foundation\Auth\User
{
    protected static $unguarded = true;
}
