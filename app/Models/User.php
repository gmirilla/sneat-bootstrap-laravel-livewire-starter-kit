<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\ClaimNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;



class User extends Authenticatable implements CanResetPassword

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password', 'account_status', 'role', 'firstname', 'lastname', 'telno', 'state', 'address', 'gender', 'dob', 'apitoken', 'parentid'
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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }


    /**
     * Set the user's API token
     */
    public function setApitoken()
    {
        $this->apitoken = $this->createToken('API Token')->plainTextToken;
        $this->save();
        return $this->apitoken;
    }

    public function revokeApitoken() 
    {
         $this->tokens()->delete(); 
         $this->apitoken = null; 
         $this->save(); 
    }  

    public function getagentdetails() 
    { 
        return agentsdetailsModel::where('uid', $this->id)->first();
    }

    //Check if User is a Sub Agent and return details if true
    public function subagentchecker()
    {
        $isSubagent = agentsdetailsModel::where('uid', $this->id)->where('issubagent', true)->exists();
        return $isSubagent;
    }

    //Get all Sub Agents under and Agent and their Details
    public function getsubagentdetails()
    {
        return agentsdetailsModel::where('puid', $this->id)->first();
    }

    public function claimNotifications()
    {
        return $this->hasMany(ClaimNotification::class);
    }

    



}
