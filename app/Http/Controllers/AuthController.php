<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

    public function loginViaPassword(Request $request)
    {
        $response = Http::asForm()->post('http://users-app.test/oauth/token', [
            'grant_type' => 'password',
            'client_id' => '3',
            'client_secret' => 'RNhbdjH99hovDNHPir4udivhZT68N7NnpLxkltYy',
            'redirect_uri' => 'http://client-app.test/auth/callback',
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
        ]);

        $request->session()->put($response->json());
        return redirect('/oauth/login');
    }

    //
    public function getOAuthToken(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => '1',
            'redirect_uri' => 'http://client-app.test/auth/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect('http://users-app.test/oauth/authorize?'.$query);
    }

    public function getCallback(Request $request)
    {
        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $response = Http::asForm()->post('http://users-app.test/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => '1',
            'client_secret' => 'yX2uVpKE3S13jZ98cXpCD0jzaX5ZWXpMCen63QJt',
            'redirect_uri' => 'http://client-app.test/auth/callback',
            'code' => $request->code,
        ]);


        $request->session()->put($response->json());
        return redirect('/oauth/login');
    }

    public function login(Request $request)
    {
        $access_token = $request->session()->get('access_token');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ])->get('http://users-app.test/api/user');

        $userArray = $response->json();

        try
        {
            $email = $userArray['email'];
        }
        catch (Exception $e)
        {
            return redirect('/oauth/request')->withError('Failed to get info');
        }

        $user = User::where('email', $email)->first();
        if(!$user)
        {
            $user = User::create([
                'name' => $userArray['name'],
                'password' => Hash::make(Str::random(30)),
                'email' => $email,
                'email_verified_at' => $userArray['email_verified_at'],
            ]);
        }

        Auth::login($user);
        return redirect('/');
    }

    public function logout(Request $request)
    {
        $access_token = $request->session()->get('access_token');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ])->post('http://users-app.test/api/logout');


        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
