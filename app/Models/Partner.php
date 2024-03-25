<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Authenticatable
{
    protected $table = 'partners';
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'business_number', 'business_name', 'business_image', 'business_address'
    ];
    protected $hidden = [
        'password', 'api_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected static function booted()
    {
        static::creating(function ($partner) {
            $partner->token = (string) Str::uuid();
            $partner->api_token = Str::uuid();
        });
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    public function partnerClass()
    {
        return $this->belongsTo(PartnerClass::class, 'partner_class_id', 'id');
    }
}
