<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'announced_pu_results';
}
