<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use PHPOpenSourceSaver\JWTAuth\Http\Parser\Parser;
use PHPOpenSourceSaver\JWTAuth\JWT;
use PHPOpenSourceSaver\JWTAuth\Manager;
use PHPOpenSourceSaver\JWTAuth\Providers\JWT\Provider;

class JWTAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $parser = new Parser($app['request']);
            
            $jwt = new JWT(
                $app['tymon.jwt.manager'],
                $parser
            );

            return new JWTGuard(
                $jwt,
                $app['auth']->createUserProvider($config['provider']),
                $app['request'],
                $app['events']
            );
        });
    }
}
