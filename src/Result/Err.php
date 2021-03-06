<?php
/**
 * Err
 *
 * Documentation and API borrowed from Rust: https://doc.rust-lang.org/std/result/enum.Result.html
 * @author Oskar Thornblad
 */

declare(strict_types=1);

namespace Prewk\Result;

use Closure;
use Exception;
use Prewk\Option;
use Prewk\Option\{Some, None};
use Prewk\Result;

/**
 * Err
 *
 * @template T
 * The Ok value
 *
 * @template E
 * The Err value
 *
 * @template-extends Result<T, E>
 */
class Err extends Result
{
    /**
     * @var mixed
     * @psalm-var E
     */
    private $err;

    /**
     * @var array
     */
    private $pass;

    /**
     * Err constructor.
     *
     * @param mixed $err
     * @psalm-param E $err
     * @param array ...$pass
     */
    public function __construct($err, ...$pass)
    {
        $this->err = $err;
        $this->pass = $pass;
    }

    /**
     * Returns true if the result is Ok.
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return false;
    }

    /**
     * Returns true if the result is Err.
     *
     * @return bool
     */
    public function isErr(): bool
    {
        return true;
    }

    /**
     * Maps a Result by applying a function to a contained Ok value, leaving an Err value untouched.
     *
     * @template U
     *
     * @param Closure $mapper
     * @psalm-param Closure(T=,mixed...):U $mapper
     * @return Result
     * @psalm-return Result<U,E>
     */
    public function map(Closure $mapper): Result
    {
        return $this;
    }

    /**
     * Maps a Result by applying a function to a contained Err value, leaving an Ok value untouched.
     *
     * @template F
     *
     * @param Closure $mapper
     * @psalm-param Closure(E=,mixed...):F $mapper
     * @return Result
     * @psalm-return Result<T,F>
     */
    public function mapErr(Closure $mapper): Result
    {
        return new self($mapper($this->err, ...$this->pass));
    }

    /**
     * Returns an iterator over the possibly contained value.
     * The iterator yields one value if the result is Ok, otherwise none.
     *
     * @return array
     * @psalm-return array<int, T>
     */
    public function iter(): array
    {
        return [];
    }

    /**
     * Returns res if the result is Ok, otherwise returns the Err value of self.
     *
     * @template U
     *
     * @param Result $res
     * @psalm-param Result<U,E> $res
     * @return Result
     * @psalm-return Result<U,E>
     */
    public function and(Result $res): Result
    {
        return $this;
    }

    /**
     * Calls op if the result is Ok, otherwise returns the Err value of self.
     *
     * @template U
     *
     * @param Closure $op
     * @psalm-param Closure(T=,mixed...):Result<U,E> $op
     * @return Result
     * @psalm-return Result<U,E>
     */
    public function andThen(Closure $op): Result
    {
        return $this;
    }

    /**
     * Returns res if the result is Err, otherwise returns the Ok value of self.
     *
     * @template F
     *
     * @param Result $res
     * @psalm-param Result<T,F> $res
     * @return Result
     * @psalm-return Result<T,F>
     */
    public function or(Result $res): Result
    {
        return $res;
    }

    /**
     * Calls op if the result is Err, otherwise returns the Ok value of self.
     *
     * @template F
     *
     * @param Closure $op
     * @psalm-param Closure(E=,mixed...):Result<T,F> $op
     * @return Result
     * @psalm-return Result<T,F>
     *
     * @throws ResultException on invalid op return type
     * @psalm-assert !Closure(T=):Result $op
     *
     * @psalm-suppress DocblockTypeContradiction We cannot be completely sure, that in argument valid callable
     */
    public function orElse(Closure $op): Result
    {
        $result = $op($this->err, ...$this->pass);

        if (!($result instanceof Result)) {
            throw new ResultException("Op must return a Result");
        }

        return $result;
    }

    /**
     * Unwraps a result, yielding the content of an Ok. Else, it returns optb.
     *
     * @param mixed $optb
     * @psalm-param T $optb
     * @return mixed
     * @psalm-return T
     */
    public function unwrapOr($optb)
    {
        return $optb;
    }

    /**
     * Unwraps a result, yielding the content of an Ok. If the value is an Err then it calls op with its value.
     *
     * @param Closure $op
     * @psalm-param Closure(E=,mixed...):T $op
     * @return mixed
     * @psalm-return T
     */
    public function unwrapOrElse(Closure $op)
    {
        return $op($this->err, ...$this->pass);
    }

    /**
     * Unwraps a result, yielding the content of an Ok.
     *
     * @return void
     * @psalm-return never-return
     * @throws Exception if the value is an Err.
     */
    public function unwrap()
    {
        if ($this->err instanceof Exception) {
            throw $this->err;
        } else {
            throw new ResultException("Unwrapped an Err");
        }
    }

    /**
     * Unwraps a result, yielding the content of an Ok.
     *
     * @template X as Exception
     *
     * @param Exception $msg
     * @psalm-param X&Exception $msg
     * @return void
     * @psalm-return never-return
     * @throws Exception the message if the value is an Err.
     */
    public function expect(Exception $msg)
    {
        throw $msg;
    }

    /**
     * Unwraps a result, yielding the content of an Err.
     *
     * @return mixed
     * @psalm-return E
     */
    public function unwrapErr()
    {
        return $this->err;
    }

    /**
     * Applies values inside the given Results to the function in this Result.
     *
     * @param Result ...$inArgs Results to apply the function to.
     * @return Result
     * @psalm-return Result<mixed,E>
     */
    public function apply(Result ...$inArgs): Result
    {
        return $this;
    }

    /**
     * Converts from Result<T, E> to Option<T>, and discarding the error, if any
     *
     * @return Option
     * @psalm-return Option<T>
     */
    public function ok(): Option
    {
        return new None;
    }

    /**
     * Converts from Result<T, E> to Option<E>, and discarding the value, if any
     *
     * @return Option
     * @psalm-return Option<E>
     */
    public function err(): Option
    {
        return new Some($this->err);
    }

    /**
     * The attached pass-through args will be unpacked into extra args into chained closures
     *
     * @param mixed ...$args
     * @return Result
     * @psalm-return Result<T,E>
     */
    public function with(...$args): Result
    {
        $this->pass = $args;

        return $this;
    }
}
