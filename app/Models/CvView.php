<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvView extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}