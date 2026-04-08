<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportData extends Model
{
    protected $fillable = ['report_id', 'date', 'rate'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

}
