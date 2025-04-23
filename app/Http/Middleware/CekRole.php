<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CekRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next, $role)
     {
         // Cek apakah user sudah login
         if (!Auth::check()) {
             return redirect()->route(route: 'login');
         }
 
         // Cek apakah user memiliki role yang sesuai
         $user = Auth::user();
         if ($user->role !== $role) {
             return redirect()->route('error404'); // Redirect ke halaman 404 atau halaman lain
         }
 
         return $next($request);
     }

    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (\Auth::user()->role != 'admin') {
    //         // 
    //         return "ra iso login";
    //     }
    //     return $next($request);
    // }
}
