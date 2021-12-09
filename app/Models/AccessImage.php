<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'access_to',
        'access_by',
        'link',
    ];
}
