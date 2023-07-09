<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFund extends Model
{
    use HasFactory;
    protected $table = 'admin_funds';
    protected $guarded = [];
}
