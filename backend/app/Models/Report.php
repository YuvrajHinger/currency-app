<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $fillable = [
        'user_id',
        'currency',
        'range',
        'interval',
        'status'
    ];

    public function data()
    {
        return $this->hasMany(ReportData::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
