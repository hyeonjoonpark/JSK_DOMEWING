<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerClass extends Model
{
    use HasFactory;
    protected $table = 'partner_classes';
    protected $fillable = [
        'name'
    ];
    public $timestamps = false;
}
