<?php

declare(strict_types = 1);

namespace AleksandrNovoselov\Robots\Classes;

use AleksandrNovoselov\Robots\Models\Robots;
use Cms\Classes\Controller as CmsController;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class Actions
{
    public function robots()
    {
        if (filter_var(Robots::get('enabled'), FILTER_VALIDATE_BOOL) !== true) {
            return \App::make(CmsController::class)->run('404');
        }

        return Response::create(
            Collection::make(Robots::get('robots'))
                /** @param array{userAgent: string, action: string, path: string} $robot */
                ->map(function(array $robot) {
                    return "User-agent: {$robot['userAgent']}\n"
                        . "{$robot['action']}: {$robot['path']}\n";
                })->join("\n")
        )->header('Content-Type', 'text/plain');
    }

}
