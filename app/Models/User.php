<?php

namespace App\Models;
use App\Models\Role;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
//use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function treePlantings()
    {
        return $this->hasMany(TreePlanting::class);
    }

    public function plantingLocations()
    {
        return $this->hasMany(PlantingLocation::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function picture()
    {
        return $this->belongsTo(Picture::class);
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }


}
