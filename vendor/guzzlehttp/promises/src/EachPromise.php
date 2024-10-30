<?php
namespace GuzzleHttp\Promise;

/**
 * Represents a promise that iterates over many promises and invokes
 * side-effect functions in the process.
 */
class EachPromise implements PromisorInterface
{
    private $pending = [];

    /** @var \Iterator */
    private $iterable;

    /** @var callable|int */
    private $concurrency;

    /** @var callable */
    private $onFulfilled;

    /** @var callable */
    private $onRejected;

    /** @var Promise */
    private $aggregate;

    /** @var bool */
    private $mutex;

    /**
     * Configuration hash can include the following key value pairs:
     *
     * - fulfilled: (callable) Invoked when a promise fulfills. The function
     *   is invoked with three arguments: the fulfillment value, the index
     *   position from the iterable list of the promise, and the aggregate
     *   promise that manages all of the promises. The aggregate promise may
     *   be resolved from within the callback to short-circuit the promise.
     * - rejected: (callable) Invoked when a promise is rejected. The
     *   function is invoked with three arguments: the rejection reason, the
     *   index position from the iterable list of the promise, and the
     *   aggregate promise that manages all of the promises. The aggregate
     *   promise may be resolved from within the callback to short-circuit
     *   the promise.
     * - concurrency: (integer) Pass this configuration option to limit the
     *   allowed number of outstanding concurrently executing promises,
     *   creating a capped pool of promises. There is no limit by default.
     *
     * @param mixed    $iterable Promises or values to iterate.
     * @param array    $config   Configuration options
     */
    public function __construct($iterable, array $config = [])
    {
        $this->iterable = iter_for($iterable);

        if (isset($config['concurrency'])) {
            $this->concurrency = $config['concurrency'];
        }

        if (isset($config['fulfilled'])) {
            $this->onFulfilled = $config['fulfilled'];
        }

        if (isset($config['rejected'])) {
            $this->onRejected = $config['rejected'];
        }
    }

    public function promise()
    {
        if ($this->aggregate) {
            return $this->aggregate;
        }

        try {
            $this->createPromise();
            $this->iterable->rewind();
            if (!$this->checkIfFinished()) {
                $this->refillPending();
            }
        } catch (\Throwable $e) {
            $this->aggregate->reject($e);
        } catch (\Exception $e) {
            $this->aggregate->reject($e);
        }

        return $this->aggregate;
    }

    private function createPromise()
    {
        $this->mutex = false;
        $this->aggregate = new Promise(function () {
            reset($this->pending);
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            while ($promise = current($this->pending)) {
                next($this->pending);
                $promise->wait();
                if ($this->aggregate->getState() !== PromiseInterface::PENDING) {
                    return;
                }
            }
        });

        // 更多精品WP资源尽在喵容：miaoroom.com
        $clearFn = function () {
            $this->iterable = $this->concurrency = $this->pending = null;
            $this->onFulfilled = $this->onRejected = null;
        };

        $this->aggregate->then($clearFn, $clearFn);
    }

    private function refillPending()
    {
        if (!$this->concurrency) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            while ($this->addPending() && $this->advanceIterator());
            return;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        $concurrency = is_callable($this->concurrency)
            ? call_user_func($this->concurrency, count($this->pending))
            : $this->concurrency;
        $concurrency = max($concurrency - count($this->pending), 0);
        // 更多精品WP资源尽在喵容：miaoroom.com
        if (!$concurrency) {
            return;
        }
        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->addPending();
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        while (--$concurrency
            && $this->advanceIterator()
            && $this->addPending());
    }

    private function addPending()
    {
        if (!$this->iterable || !$this->iterable->valid()) {
            return false;
        }

        $promise = promise_for($this->iterable->current());
        $key = $this->iterable->key();

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->pending[] = null;
        end($this->pending);
        $idx = key($this->pending);

        $this->pending[$idx] = $promise->then(
            function ($value) use ($idx, $key) {
                if ($this->onFulfilled) {
                    call_user_func(
                        $this->onFulfilled, $value, $key, $this->aggregate
                    );
                }
                $this->step($idx);
            },
            function ($reason) use ($idx, $key) {
                if ($this->onRejected) {
                    call_user_func(
                        $this->onRejected, $reason, $key, $this->aggregate
                    );
                }
                $this->step($idx);
            }
        );

        return true;
    }

    private function advanceIterator()
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($this->mutex) {
            return false;
        }

        $this->mutex = true;

        try {
            $this->iterable->next();
            $this->mutex = false;
            return true;
        } catch (\Throwable $e) {
            $this->aggregate->reject($e);
            $this->mutex = false;
            return false;
        } catch (\Exception $e) {
            $this->aggregate->reject($e);
            $this->mutex = false;
            return false;
        }
    }

    private function step($idx)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($this->aggregate->getState() !== PromiseInterface::PENDING) {
            return;
        }

        unset($this->pending[$idx]);

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($this->advanceIterator() && !$this->checkIfFinished()) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $this->refillPending();
        }
    }

    private function checkIfFinished()
    {
        if (!$this->pending && !$this->iterable->valid()) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $this->aggregate->resolve(null);
            return true;
        }

        return false;
    }
}
