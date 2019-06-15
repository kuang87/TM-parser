<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public $incrementing = false;

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class);
    }
}
