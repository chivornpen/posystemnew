<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */protected $table='users';
    protected $fillable = [
        'name','nameDisplay','sex','contactNum', 'email', 'password','brand_id','zone_id','position_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    public function purchaseorders()
    {
        return $this->hasMany(PurchaseOder::class);
    }
    public function requestpros()
    {
        return $this->hasMany(Requestpro::class);
    }
    public function purchaseordersds()
    {
        return $this->hasMany(Purchaseodersd::class);
    }
    public function subimports(){
        return $this->hasMany(Subimport::class,'imported_by');
    }
    public function returnpro()
    {
        return $this->hasOne(Returnpro::class);
    }
    public function returnreqpro()
    {
        return $this->hasOne(Returnreqpro::class);
    }
}
