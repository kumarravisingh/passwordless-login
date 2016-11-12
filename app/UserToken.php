<?php

namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class UserToken extends Model
{
    protected $fillable = ['user_id', 'token'];
    
    
   
    public static function storeToken($request)
    {
        
        $user = User::getUserByEmail($request->get('email'));
        // $user->token->delete();
        
        return self::create([
            'user_id' => $user->id,
            'token'   => str_random(50)
        ]);
    }
    
    
    
    
    protected static function sendMail($request, array $options)
    {
        $user = User::getUserByEmail($request->get('email'));
        
        $url = url('/login/magiclink/' . $user->token->token . '?' . http_build_query($options));
        
        Mail::raw(
            "<a href='{$url}'>{$url}</a>",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Click the magic link to login');
            }
        );
    }
    
    
    public function getRouteKeyName()
    {
        return 'token';
    }
    
    public function isExpired()
	{
	    return $this->created_at->diffInMinutes(Carbon::now()) > 5;
	}
	
	public function belongsToEmail($email)
    {  
        $user = User::where('email', $email)->firstOrFail();
        
        
        if (!$user) {
            //if no record was found in the database
            return false;
        } elseif(!$user->token) {
            //if record was found but no token is associated with it
            return false;
        } else {
            //if the record found has a token and the token value matches what was sent in the email
            return (bool) ($this->token === $user->token->token);
        }
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}

