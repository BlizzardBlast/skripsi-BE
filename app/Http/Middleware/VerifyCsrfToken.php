<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/api/sign-in',
        '/api/sign-up',
        '/api/sign-out',
        '/api/postUpdateUserData/*',
        '/api/setUserPref',
        '/api/createPayment',
        '/api/complete',
        '/api/addToCart',
        '/api/editQty',
        '/api/removeFromCart',
        '/api/incrementQuantity',
        '/api/decrementQuantity',
        '/api/postOrder',
    ];
}