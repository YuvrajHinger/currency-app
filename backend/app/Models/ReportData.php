<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportData extends Model
{
    use HasFactory;
    
    protected $fillable = ['report_id', 'date', 'rate'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

}
