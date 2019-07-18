<?php

/*
 * This file is part of the godruoyi/dingo-api-helper.
 *
 * (c) Godruoyi <godruoyi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\DingoApiHelper\Support;

use Closure;
use Dingo\Api\Http\Response as DingoResponse;
use Dingo\Api\Transformer\Factory as TransformerFactory;
use ErrorException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DingoApiResponse
{
    /**
     * Transformer factory instance.
     *
     * @var \Dingo\Api\Transformer\Factory
     */
    protected $transformer;

    /**
     * The Transformer converter.
     *
     * @var \Godruoyi\DingoApiHelper\Support\Converter
     */
    protected $transformerConverter;

    /**
     * Create a new DingoResponse factory instance.
     *
     * @param \Dingo\Api\Transformer\Factory $transformer
     */
    public function __construct(TransformerFactory $transformer, Converter $transformerConverter)
    {
        $this->transformer = $transformer;
        $this->transformerConverter = $transformerConverter;
    }

    /**
     * Respond with a created response.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function created($content = null): DingoResponse
    {
        $response = new DingoResponse($content);
        $response->setStatusCode(201);

        return $response;
    }

    /**
     * disable dingo api pre-load.
     *
     * @return mixed
     */
    public function disableEagerLoading()
    {
        $adapter = $this->transformer->getAdapter()->disableEagerLoading();
        $this->transformer->setAdapter($adapter);

        return $this;
    }

    /**
     * Response with 200 response.
     *
     * @param mixed $content
     *
     * @return \Dingo\Api\Http\Response
     */
    public function success($content): DingoResponse
    {
        return new DingoResponse($content);
    }

    /**
     * Response with 200 response.
     *
     * @param mixed $content
     *
     * @return \Dingo\Api\Http\Response
     */
    public function array($content): DingoResponse
    {
        return new DingoResponse($content);
    }

    /**
     * Respond with an accepted response.
     *
     * @param mixed $content
     *
     * @return \Dingo\Api\Http\Response
     */
    public function accepted($content = null): DingoResponse
    {
        $response = new DingoResponse($content);
        $response->setStatusCode(202);

        return $response;
    }

    /**
     * Respond with a no content response.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function noContent(): DingoResponse
    {
        $response = new DingoResponse(null);

        return $response->setStatusCode(204);
    }

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param object                         $transformer
     * @param array|\Closure                 $parameters
     * @param \Closure|null                  $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function collection(Collection $collection, $transformer = null, $parameters = [], Closure $after = null)
    {
        $transformer = $this->transformerConverter->convert($collection, $transformer);

        if ($collection->isEmpty()) {
            $class = get_class($collection);
        } else {
            $class = get_class($collection->first());
        }

        if ($parameters instanceof \Closure) {
            $after = $parameters;
            $parameters = [];
        }

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new DingoResponse($collection, 200, [], $binding);
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object   $item
     * @param object   $transformer
     * @param array    $parameters
     * @param \Closure $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function item($item, $transformer = null, $parameters = [], Closure $after = null)
    {
        $transformer = $this->transformerConverter->convert($item, $transformer);
        $class = get_class($item);

        if ($parameters instanceof \Closure) {
            $after = $parameters;
            $parameters = [];
        }

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new DingoResponse($item, 200, [], $binding);
    }

    /**
     * Bind a paginator to a transformer and start building a response.
     *
     * @param \Illuminate\Contracts\Pagination\Paginator $paginator
     * @param object                                     $transformer
     * @param array                                      $parameters
     * @param \Closure                                   $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function paginator(Paginator $paginator, $transformer = null, array $parameters = [], Closure $after = null)
    {
        if ($paginator->isEmpty()) {
            $class = get_class($paginator);
        } else {
            $class = get_class($paginator->first());
        }

        $parameters['key'] = 'data';

        $transformer = $this->transformerConverter->convert($paginator, $transformer);

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new DingoResponse($paginator, 200, [], $binding);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int    $statusCode
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message, $statusCode, int $errorCode = null): JsonResponse
    {
        $errorCode = is_null($errorCode) ? ($statusCode * 100) : $errorCode;

        return response()->json([
            'error_code' => $errorCode,
            'message' => $message,
            // 'status_code' => $statusCode,
        ], $statusCode);
    }

    /**
     * Return a 404 not found error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorNotFound($message = 'Not Found', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 404, $errorCode);
    }

    /**
     * Return a 400 bad request error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorBadRequest($message = 'Bad Request', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 400, $errorCode);
    }

    /**
     * Return a 403 forbidden error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorForbidden($message = 'Forbidden', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 403, $errorCode);
    }

    /**
     * Return a 500 internal server error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorInternal($message = 'Internal Error', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 500, $errorCode);
    }

    /**
     * Return a 401 unauthorized error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorUnauthorized($message = 'Unauthorized', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 401, $errorCode);
    }

    /**
     * Return a 405 method not allowed error.
     *
     * @param string $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorMethodNotAllowed($message = 'Method Not Allowed', int $errorCode = null): JsonResponse
    {
        return $this->error($message, 405, $errorCode);
    }

    /**
     * Call magic methods beginning with "with".
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \ErrorException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return call_user_func_array([$this, Str::camel(substr($method, 4))], $parameters);

        // Because PHP won't let us name the method "array" we'll simply watch for it
            // in here and return the new binding. Gross. This is now DEPRECATED and
            // should not be used. Just return an array or a new DingoResponse instance.
        } elseif ('array' == $method) {
            return new DingoResponse($parameters[0]);
        }

        throw new ErrorException('Undefined method '.get_class($this).'::'.$method);
    }
}
