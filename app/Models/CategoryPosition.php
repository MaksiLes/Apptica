<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class CategoryPosition extends Model
{
    use HasFactory;

    protected $table = 'category_positions';
    public $timestamps = false;
}
