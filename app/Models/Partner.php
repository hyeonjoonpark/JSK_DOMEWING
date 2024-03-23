<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Partner extends Authenticatable
{
    protected $table = 'partners';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'business_number', 'business_name', 'business_image', 'business_address', 'is_active'
    ];

    protected $hidden = [
        'password', 'api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 모델 이벤트를 사용하여 Partner 인스턴스가 생성될 때 token과 api_token을 자동으로 생성
    protected static function booted()
    {
        static::creating(function ($partner) {
            $partner->token = (string) Str::uuid();
            $partner->api_token = Str::uuid();
        });
    }

    // 비밀번호 해싱을 모델 내에서 자동으로 처리
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
