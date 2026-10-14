<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public $timestamps = false;
    protected $guarded = [];
}
