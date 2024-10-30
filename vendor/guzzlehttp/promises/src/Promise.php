<?php
namespace GuzzleHttp\Promise;

/**
 * Promises/A+ implementation that avoids recursion when possible.
 *
 * @link https://promisesaplus.com/
 */
class Promise implements PromiseInterface
{
    private $state = self::PENDING;
    private $result;
    private $cancelFn;
    private $waitFn;
    private $waitList;
    private $handlers = [];

    /**
     * @param callable $waitFn   Fn that when invoked resolves the promise.
     * @param callable $cancelFn Fn that when invoked cancels the promise.
     */
    public function __construct(
        callable $waitFn = null,
        callable $cancelFn = null
    ) {
        $this->waitFn = $waitFn;
        $this->cancelFn = $cancelFn;
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null
    ) {
        if ($this->state === self::PENDING) {
            $p = new Promise(null, [$this, 'cancel']);
            $this->handlers[] = [$p, $onFulfilled, $onRejected];
            $p->waitList = $this->waitList;
            $p->waitList[] = $this;
            return $p;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($this->state === self::FULFILLED) {
            $promise = promise_for($this->result);
            return $onFulfilled ? $promise->then($onFulfilled) : $promise;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $rejection = rejection_for($this->result);
        return $onRejected ? $rejection->then(null, $onRejected) : $rejection;
    }

    public function otherwise(callable $onRejected)
    {
        return $this->then(null, $onRejected);
    }

    public function wait($unwrap = true)
    {
        $this->waitIfPending();

        if ($this->result instanceof PromiseInterface) {
            return $this->result->wait($unwrap);
        }
        if ($unwrap) {
            if ($this->state === self::FULFILLED) {
                return $this->result;
            }
            // 更多精品WP资源尽在喵容：miaoroom.com
            throw exception_for($this->result);
        }
    }

    public function getState()
    {
        return $this->state;
    }

    public function cancel()
    {
        if ($this->state !== self::PENDING) {
            return;
        }

        $this->waitFn = $this->waitList = null;

        if ($this->cancelFn) {
            $fn = $this->cancelFn;
            $this->cancelFn = null;
            try {
                $fn();
            } catch (\Throwable $e) {
                $this->reject($e);
            } catch (\Exception $e) {
                $this->reject($e);
            }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($this->state === self::PENDING) {
            $this->reject(new CancellationException('Promise has been cancelled'));
        }
    }

    public function resolve($value)
    {
        $this->settle(self::FULFILLED, $value);
    }

    public function reject($reason)
    {
        $this->settle(self::REJECTED, $reason);
    }

    private function settle($state, $value)
    {
        if ($this->state !== self::PENDING) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            if ($state === $this->state && $value === $this->result) {
                return;
            }
            throw $this->state === $state
                ? new \LogicException("The promise is already {$state}.")
                : new \LogicException("Cannot change a {$this->state} promise to {$state}");
        }

        if ($value === $this) {
            throw new \LogicException('Cannot fulfill or reject a promise with itself');
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->state = $state;
        $this->result = $value;
        $handlers = $this->handlers;
        $this->handlers = null;
        $this->waitList = $this->waitFn = null;
        $this->cancelFn = null;

        if (!$handlers) {
            return;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if (!is_object($value) || !method_exists($value, 'then')) {
            $id = $state === self::FULFILLED ? 1 : 2;
            // 更多精品WP资源尽在喵容：miaoroom.com
            queue()->add(static function () use ($id, $value, $handlers) {
                foreach ($handlers as $handler) {
                    self::callHandler($id, $value, $handler);
                }
            });
        } elseif ($value instanceof Promise
            && $value->getState() === self::PENDING
        ) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $value->handlers = array_merge($value->handlers, $handlers);
        } else {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $value->then(
                static function ($value) use ($handlers) {
                    foreach ($handlers as $handler) {
                        self::callHandler(1, $value, $handler);
                    }
                },
                static function ($reason) use ($handlers) {
                    foreach ($handlers as $handler) {
                        self::callHandler(2, $reason, $handler);
                    }
                }
            );
        }
    }

    /**
     * Call a stack of handlers using a specific callback index and value.
     *
     * @param int   $index   1 (resolve) or 2 (reject).
     * @param mixed $value   Value to pass to the callback.
     * @param array $handler Array of handler data (promise and callbacks).
     *
     * @return array Returns the next group to resolve.
     */
    private static function callHandler($index, $value, array $handler)
    {
        /** @var PromiseInterface $promise */
        $promise = $handler[0];

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($promise->getState() !== self::PENDING) {
            return;
        }

        try {
            if (isset($handler[$index])) {
                $promise->resolve($handler[$index]($value));
            } elseif ($index === 1) {
                // 更多精品WP资源尽在喵容：miaoroom.com
                $promise->resolve($value);
            } else {
                // 更多精品WP资源尽在喵容：miaoroom.com
                $promise->reject($value);
            }
        } catch (\Throwable $reason) {
            $promise->reject($reason);
        } catch (\Exception $reason) {
            $promise->reject($reason);
        }
    }

    private function waitIfPending()
    {
        if ($this->state !== self::PENDING) {
            return;
        } elseif ($this->waitFn) {
            $this->invokeWaitFn();
        } elseif ($this->waitList) {
            $this->invokeWaitList();
        } else {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $this->reject('Cannot wait on a promise that has '
                . 'no internal wait function. You must provide a wait '
                . 'function when constructing the promise to be able to '
                . 'wait on a promise.');
        }

        queue()->run();

        if ($this->state === self::PENDING) {
            $this->reject('Invoking the wait callback did not resolve the promise');
        }
    }

    private function invokeWaitFn()
    {
        try {
            $wfn = $this->waitFn;
            $this->waitFn = null;
            $wfn(true);
        } catch (\Exception $reason) {
            if ($this->state === self::PENDING) {
                // 更多精品WP资源尽在喵容：miaoroom.com
                // 更多精品WP资源尽在喵容：miaoroom.com
                $this->reject($reason);
            } else {
                // 更多精品WP资源尽在喵容：miaoroom.com
                // 更多精品WP资源尽在喵容：miaoroom.com
                throw $reason;
            }
        }
    }

    private function invokeWaitList()
    {
        $waitList = $this->waitList;
        $this->waitList = null;

        foreach ($waitList as $result) {
            do {
                $result->waitIfPending();
                $result = $result->result;
            } while ($result instanceof Promise);

            if ($result instanceof PromiseInterface) {
                $result->wait(false);
            }
        }
    }
}