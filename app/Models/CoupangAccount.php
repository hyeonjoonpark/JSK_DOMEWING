<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoupangAccount extends Model
{
    use HasFactory;
    protected $table = "coupang_accounts";
    protected $fillable = [
        'code', 'access_key', 'secret_key', 'expired_at'
    ];
    protected $hidden = [
        'code', 'access_key', 'secret_key'
    ];
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }
}
