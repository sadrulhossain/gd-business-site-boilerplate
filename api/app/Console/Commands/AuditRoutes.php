<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Lists the API routes that sit behind `auth:api` but have no permission check: neither a `can:` middleware nor a
 * policy/Gate call in the controller method. Routes in config('access.route_audit_allow') are intentionally open to
 * every signed-in user. Exits 1 when anything else is found, so it can gate CI.
 */
class AuditRoutes extends Command
{
    protected $signature = 'security:routes {--all : Also list the routes that are covered}';

    protected $description = 'List authenticated routes without a policy or permission check';

    /** Routes any signed-in user may call (their own account). Everything else needs an explicit check. */
    private const ALLOWED = [
        'GET api/v1/me',
        'PATCH api/v1/me',
        'GET api/v1/me/menus',
        'POST api/v1/logout',
    ];

    public function handle(): int
    {
        $bad = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/') || ! in_array('auth:api', $route->gatherMiddleware(), true)) {
                continue;
            }

            $method = $route->methods()[0];
            $label = $method.' '.$route->uri();
            $why = $this->coveredBy($route);

            if ($why === null && ! in_array($label, self::ALLOWED, true)) {
                $bad[] = $label.'  '.$route->getActionName();
            } elseif ($this->option('all')) {
                $this->line(sprintf('ok  %-45s %s', $label, $why ?? 'allowed: own account'));
            }
        }

        foreach ($bad as $line) {
            $this->error('NO CHECK  '.$line);
        }
        $this->info(count($bad) === 0 ? 'every authenticated route has a permission or policy check' : count($bad).' route(s) without a check');

        return $bad === [] ? self::SUCCESS : self::FAILURE;
    }

    private function coveredBy(LaravelRoute $route): ?string
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'can:')) {
                return $middleware;
            }
        }

        $action = $route->getActionName();
        [$class, $name] = str_contains($action, '@') ? explode('@', $action) : [$action, '__invoke'];
        if (! class_exists($class) || ! method_exists($class, $name)) {
            return null;
        }

        $reflection = new ReflectionMethod($class, $name);
        $source = $this->source($reflection);

        // Form requests authorise too.
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType() instanceof ReflectionNamedType ? $parameter->getType()->getName() : null;
            if ($type !== null && is_subclass_of($type, FormRequest::class) && method_exists($type, 'authorize')) {
                $source .= $this->source(new ReflectionMethod($type, 'authorize'));
            }
        }

        return preg_match('/Gate::(authorize|allows|denies|check)\(|->authorize\(|->can\(|hasPermissionTo\(|\$request->user\(\)->can/', $source) === 1
            ? 'policy/gate in '.class_basename($class).'::'.$name
            : null;
    }

    private function source(ReflectionMethod $method): string
    {
        $lines = file($method->getFileName()) ?: [];

        return implode('', array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
    }
}
