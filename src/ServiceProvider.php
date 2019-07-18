<?php

namespace Godruoyi\DingoApiHelper;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->reRegisterResponseFactory();
        $this->reSetTransformerSerializer();

        $this->customDingoApiExceptionResponses();
    }

    /**
     * Register the response factory.
     *
     * @return void
     */
    protected function reRegisterResponseFactory()
    {
        $this->app->singleton('api.http.response', function ($app) {
            return $app->make(\Godruoyi\DingoApiHelper\Support\DingoApiResponse::class);
        });
    }

    /**
     * Custom Dingo Api Exception Responses
     *
     * @return void
     */
    protected function customDingoApiExceptionResponses()
    {
        app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('404 Not Found!');
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Validation\ValidationException $e) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($e->validator->errors()->first());
        });

        app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Auth\AuthenticationException $e) {
            throw new \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('JWTAuth', $e->getMessage(), $e);
        });
    }

    /**
     * Reset transformer transfomer Serializer
     *
     * @return void
     */
    protected function reSetTransformerSerializer()
    {
        $this->app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {
            $fractal = new \League\Fractal\Manager;

            // $fractal->setSerializer(new \League\Fractal\Serializer\JsonApiSerializer);
            // $fractal->setSerializer(new \League\Fractal\Serializer\ArraySerializer);
            $fractal->setSerializer(new \Godruoyi\DingoApiHelper\Support\ArraySerializer);
            // $fractal->setSerializer(new \League\Fractal\Serializer\DataArraySerializer);

            return new \Dingo\Api\Transformer\Adapter\Fractal($fractal);
        });
    }
}