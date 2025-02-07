<?php
namespace GuzzleHttp\Promise\Tests;

use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise as P;
use PHPUnit\Framework\TestCase;

/**
 * @covers GuzzleHttp\Promise\EachPromise
 */
class EachPromiseTest extends TestCase
{
    public function testReturnsSameInstance()
    {
        $each = new EachPromise([], ['concurrency' => 100]);
        $this->assertSame($each->promise(), $each->promise());
    }

    public function testResolvesInCaseOfAnEmptyList()
    {
        $promises = [];
        $each = new EachPromise($promises);
        $p = $each->promise();
        $this->assertNull($p->wait());
        $this->assertEquals(PromiseInterface::FULFILLED, $p->getState());
    }

    public function testInvokesAllPromises()
    {
        $promises = [new Promise(), new Promise(), new Promise()];
        $called = [];
        $each = new EachPromise($promises, [
            'fulfilled' => function ($value) use (&$called) {
                $called[] = $value;
            }
        ]);
        $p = $each->promise();
        $promises[0]->resolve('a');
        $promises[1]->resolve('c');
        $promises[2]->resolve('b');
        P\queue()->run();
        $this->assertEquals(['a', 'c', 'b'], $called);
        $this->assertEquals(PromiseInterface::FULFILLED, $p->getState());
    }

    public function testIsWaitable()
    {
        $a = $this->createSelfResolvingPromise('a');
        $b = $this->createSelfResolvingPromise('b');
        $called = [];
        $each = new EachPromise([$a, $b], [
            'fulfilled' => function ($value) use (&$called) { $called[] = $value; }
        ]);
        $p = $each->promise();
        $this->assertNull($p->wait());
        $this->assertEquals(PromiseInterface::FULFILLED, $p->getState());
        $this->assertEquals(['a', 'b'], $called);
    }

    public function testCanResolveBeforeConsumingAll()
    {
        $called = 0;
        $a = $this->createSelfResolvingPromise('a');
        $b = new Promise(function () { $this->fail(); });
        $each = new EachPromise([$a, $b], [
            'fulfilled' => function ($value, $idx, Promise $aggregate) use (&$called) {
                $this->assertSame($idx, 0);
                $this->assertEquals('a', $value);
                $aggregate->resolve(null);
                $called++;
            },
            'rejected' => function (\Exception $reason) {
                $this->fail($reason->getMessage());
            }
        ]);
        $p = $each->promise();
        $p->wait();
        $this->assertNull($p->wait());
        $this->assertEquals(1, $called);
        $this->assertEquals(PromiseInterface::FULFILLED, $a->getState());
        $this->assertEquals(PromiseInterface::PENDING, $b->getState());
        // 更多精品WP资源尽在喵容：miaoroom.com
        $b->resolve('foo');
        $this->assertEquals(1, $called);
    }

    public function testLimitsPendingPromises()
    {
        $pending = [new Promise(), new Promise(), new Promise(), new Promise()];
        $promises = new \ArrayIterator($pending);
        $each = new EachPromise($promises, ['concurrency' => 2]);
        $p = $each->promise();
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        $pending[0]->resolve('a');
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        $this->assertTrue($promises->valid());
        $pending[1]->resolve('b');
        P\queue()->run();
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        $this->assertTrue($promises->valid());
        $promises[2]->resolve('c');
        P\queue()->run();
        $this->assertCount(1, $this->readAttribute($each, 'pending'));
        $this->assertEquals(PromiseInterface::PENDING, $p->getState());
        $promises[3]->resolve('d');
        P\queue()->run();
        $this->assertNull($this->readAttribute($each, 'pending'));
        $this->assertEquals(PromiseInterface::FULFILLED, $p->getState());
        $this->assertFalse($promises->valid());
    }

    public function testDynamicallyLimitsPendingPromises()
    {
        $calls = [];
        $pendingFn = function ($count) use (&$calls) {
            $calls[] = $count;
            return 2;
        };
        $pending = [new Promise(), new Promise(), new Promise(), new Promise()];
        $promises = new \ArrayIterator($pending);
        $each = new EachPromise($promises, ['concurrency' => $pendingFn]);
        $p = $each->promise();
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        $pending[0]->resolve('a');
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        $this->assertTrue($promises->valid());
        $pending[1]->resolve('b');
        $this->assertCount(2, $this->readAttribute($each, 'pending'));
        P\queue()->run();
        $this->assertTrue($promises->valid());
        $promises[2]->resolve('c');
        P\queue()->run();
        $this->assertCount(1, $this->readAttribute($each, 'pending'));
        $this->assertEquals(PromiseInterface::PENDING, $p->getState());
        $promises[3]->resolve('d');
        P\queue()->run();
        $this->assertNull($this->readAttribute($each, 'pending'));
        $this->assertEquals(PromiseInterface::FULFILLED, $p->getState());
        $this->assertEquals([0, 1, 1, 1], $calls);
        $this->assertFalse($promises->valid());
    }

    public function testClearsReferencesWhenResolved()
    {
        $called = false;
        $a = new Promise(function () use (&$a, &$called) {
            $a->resolve('a');
            $called = true;
        });
        $each = new EachPromise([$a], [
            'concurrency'       => function () { return 1; },
            'fulfilled' => function () {},
            'rejected'  => function () {}
        ]);
        $each->promise()->wait();
        $this->assertNull($this->readAttribute($each, 'onFulfilled'));
        $this->assertNull($this->readAttribute($each, 'onRejected'));
        $this->assertNull($this->readAttribute($each, 'iterable'));
        $this->assertNull($this->readAttribute($each, 'pending'));
        $this->assertNull($this->readAttribute($each, 'concurrency'));
        $this->assertTrue($called);
    }

    public function testCanBeCancelled()
    {
        $called = false;
        $a = new FulfilledPromise('a');
        $b = new Promise(function () use (&$called) { $called = true; });
        $each = new EachPromise([$a, $b], [
            'fulfilled' => function ($value, $idx, Promise $aggregate) {
                $aggregate->cancel();
            },
            'rejected' => function ($reason) use (&$called) {
                $called = true;
            },
        ]);
        $p = $each->promise();
        $p->wait(false);
        $this->assertEquals(PromiseInterface::FULFILLED, $a->getState());
        $this->assertEquals(PromiseInterface::PENDING, $b->getState());
        $this->assertEquals(PromiseInterface::REJECTED, $p->getState());
        $this->assertFalse($called);
    }

    public function testDoesNotBlowStackWithFulfilledPromises()
    {
        $pending = [];
        for ($i = 0; $i < 100; $i++) {
            $pending[] = new FulfilledPromise($i);
        }
        $values = [];
        $each = new EachPromise($pending, [
            'fulfilled' => function ($value) use (&$values) {
                $values[] = $value;
            }
        ]);
        $called = false;
        $each->promise()->then(function () use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
        P\queue()->run();
        $this->assertTrue($called);
        $this->assertEquals(range(0, 99), $values);
    }

    public function testDoesNotBlowStackWithRejectedPromises()
    {
        $pending = [];
        for ($i = 0; $i < 100; $i++) {
            $pending[] = new RejectedPromise($i);
        }
        $values = [];
        $each = new EachPromise($pending, [
            'rejected' => function ($value) use (&$values) {
                $values[] = $value;
            }
        ]);
        $called = false;
        $each->promise()->then(
            function () use (&$called) { $called = true; },
            function () { $this->fail('Should not have rejected.'); }
        );
        $this->assertFalse($called);
        P\queue()->run();
        $this->assertTrue($called);
        $this->assertEquals(range(0, 99), $values);
    }

    public function testReturnsPromiseForWhatever()
    {
        $called = [];
        $arr = ['a', 'b'];
        $each = new EachPromise($arr, [
            'fulfilled' => function ($v) use (&$called) { $called[] = $v; }
        ]);
        $p = $each->promise();
        $this->assertNull($p->wait());
        $this->assertEquals(['a', 'b'], $called);
    }

    public function testRejectsAggregateWhenNextThrows()
    {
        $iter = function () {
            yield 'a';
            throw new \Exception('Failure');
        };
        $each = new EachPromise($iter());
        $p = $each->promise();
        $e = null;
        $received = null;
        $p->then(null, function ($reason) use (&$e) { $e = $reason; });
        P\queue()->run();
        $this->assertInstanceOf(\Exception::class, $e);
        $this->assertEquals('Failure', $e->getMessage());
    }

    public function testDoesNotCallNextOnIteratorUntilNeededWhenWaiting()
    {
        $results = [];
        $values = [10];
        $remaining = 9;
        $iter = function () use (&$values) {
            while ($value = array_pop($values)) {
                yield $value;
            }
        };
        $each = new EachPromise($iter(), [
            'concurrency' => 1,
            'fulfilled' => function ($r) use (&$results, &$values, &$remaining) {
                $results[] = $r;
                if ($remaining > 0) {
                    $values[] = $remaining--;
                }
            }
        ]);
        $each->promise()->wait();
        $this->assertEquals(range(10, 1), $results);
    }

    public function testDoesNotCallNextOnIteratorUntilNeededWhenAsync()
    {
        $firstPromise = new Promise();
        $pending = [$firstPromise];
        $values = [$firstPromise];
        $results = [];
        $remaining = 9;
        $iter = function () use (&$values) {
            while ($value = array_pop($values)) {
                yield $value;
            }
        };
        $each = new EachPromise($iter(), [
            'concurrency' => 1,
            'fulfilled' => function ($r) use (&$results, &$values, &$remaining, &$pending) {
                $results[] = $r;
                if ($remaining-- > 0) {
                    $pending[] = $values[] = new Promise();
                }
            }
        ]);
        $i = 0;
        $each->promise();
        while ($promise = array_pop($pending)) {
            $promise->resolve($i++);
            P\queue()->run();
        }
        $this->assertEquals(range(0, 9), $results);
    }

    private function createSelfResolvingPromise($value)
    {
        $p = new Promise(function () use (&$p, $value) {
            $p->resolve($value);
        });

        return $p;
    }

    public function testMutexPreventsGeneratorRecursion()
    {
        $results = $promises = [];
        for ($i = 0; $i < 20; $i++) {
            $p = $this->createSelfResolvingPromise($i);
            $pending[] = $p;
            $promises[] = $p;
        }

        $iter = function () use (&$promises, &$pending) {
            foreach ($promises as $promise) {
                // 更多精品WP资源尽在喵容：miaoroom.com
                // 更多精品WP资源尽在喵容：miaoroom.com
                // 更多精品WP资源尽在喵容：miaoroom.com
                // 更多精品WP资源尽在喵容：miaoroom.com
                if ($p = array_pop($pending)) {
                    $p->wait();
                }
                yield $promise;
            }
        };

        $each = new EachPromise($iter(), [
            'concurrency' => 5,
            'fulfilled' => function ($r) use (&$results, &$pending) {
                $results[] = $r;
            }
        ]);

        $each->promise()->wait();
        $this->assertCount(20, $results);
    }

    public function testIteratorWithSameKey()
    {
        $iter = function () {
            yield 'foo' => $this->createSelfResolvingPromise(1);
            yield 'foo' => $this->createSelfResolvingPromise(2);
            yield 1 => $this->createSelfResolvingPromise(3);
            yield 1 => $this->createSelfResolvingPromise(4);
        };
        $called = 0;
        $each = new EachPromise($iter(), [
            'fulfilled' => function ($value, $idx, Promise $aggregate) use (&$called) {
                $called++;
                if ($value < 3) {
                    $this->assertSame('foo', $idx);
                } else {
                    $this->assertSame(1, $idx);
                }
            },
        ]);
        $each->promise()->wait();
        $this->assertSame(4, $called);
    }
}
