<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'price', 'package_category_id'];

    public function packageCategory()
    {
        return $this->belongsTo(PackageCategory::class);
    }
}
