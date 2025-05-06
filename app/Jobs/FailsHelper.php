<?php

declare(strict_types=1);

namespace App\Jobs;

use Sentry\State\Scope;
use Throwable;

use function Sentry\captureException;
use function Sentry\withScope;

/**
 * @mixin \BackedEnum
 *
 * @codeCoverageIgnore
 */
trait FailsHelper
{
    public function failed(Throwable $error): void
    {
        withScope(function (Scope $scope) use ($error) {
            $context = $this->context();

            if (method_exists($error, 'context')) {
                $context = array_merge($context, $error->context());
            }

            $scope->setContext(self::class, $context);

            foreach ($context as $key => $val) {
                $scope->setTag($key, $val);
            }

            captureException($error);
        });
    }

    /**
     * @codeCoverageIgnore
     */
    private function context(): array
    {
        return [];
    }
}
