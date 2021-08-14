<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Analytics extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'logs';
    protected $guarded = ['id'];
}
