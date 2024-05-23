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
        '/api/addProduct',
        '/api/editProduct/*',
        '/api/removeProduct/*',
        '/api/setUserPref',
        '/api/addToCart',
        '/api/editQty',
        '/api/removeFromCart',
        '/api/incrementQuantity',
        '/api/decrementQuantity',
        '/api/postOrder',
        '/api/createPayment',
        '/api/complete',
        '/api/checkPromo',
        '/api/postPromo',
        '/api/deletePromo/*'
    ];
}
