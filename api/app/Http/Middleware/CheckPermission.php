<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        $user = $request->user();

        // Якщо middleware викликано без параметра — дозволяємо
        if (! $permission) {
            return $next($request);
        }

        // Розбити параметр на масив (підтримуємо '|' або ',')
        $names = preg_split('/[|,]/', $permission);
        $names = array_filter(array_map('trim', $names));

        // Нема користувача => 403
        if (! $user) {
            return $this->forbiddenResponse($request);
        }

        // Якщо лише один пермішн — використовуємо існуючий метод моделі (ефективно)
        if (count($names) === 1) {
            if (! $user->hasPermission($names[0])) {
                return $this->forbiddenResponse($request);
            }

            return $next($request);
        }

        // Багато пермішнів — перевіряємо чи є хоча б один через ролі
        $hasAnyPermission = $user->roles()
            ->whereHas('permissions', function ($query) use ($names) {
                $query->whereIn('name', $names);
            })
            ->exists();

        if (! $hasAnyPermission) {
            return $this->forbiddenResponse($request);
        }

        return $next($request);
    }

    protected function forbiddenResponse(Request $request)
    {
        // Якщо очікують JSON — повертаємо JSON
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        // Інакше — стандартний abort (виведе 403 сторінку)
        abort(Response::HTTP_FORBIDDEN);
    }
}
