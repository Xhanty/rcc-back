<?php

namespace App\Http\Middleware;

use App\Filament\Pages\ForcePasswordChange;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Filament::auth()->check()) {
            /** @var \App\Models\User $user */
            $user = Filament::auth()->user();

            if ($user && $user->must_change_password) {
                $routeName = $request->route()?->getName() ?? '';

                // Allow the password change page itself, logout routes, and livewire requests
                if (
                    ! $request->is('*/force-password-change*') &&
                    ! str($routeName)->contains('force-password-change') &&
                    ! str($routeName)->contains('logout') &&
                    ! $request->is('*/logout') &&
                    ! $request->routeIs('livewire.*') &&
                    ! $request->is('livewire/*')
                ) {
                    return redirect()->to(ForcePasswordChange::getUrl());
                }
            }
        }

        return $next($request);
    }
}
