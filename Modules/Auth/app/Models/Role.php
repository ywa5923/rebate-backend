<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    public function users():BelongsToMany
    {
        return $this
            ->belongsToMany(User::class)
            ->withTimestamps();
    }
}
