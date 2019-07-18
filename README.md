<h1 align="center"> Dingo api response helper. </h1>

<p align="center">
    <a href="https://github.styleci.io/repos/197521808"><img src="https://github.styleci.io/repos/197521808/shield?branch=master" alt="StyleCI"></a>
    <a href="https://packagist.org/packages/godruoyi/dingo-api-helper"><img src="https://poser.pugx.org/godruoyi/dingo-api-helper/v/stable" alt="dingo-api-helper"></a>
    <a href="https://packagist.org/packages/godruoyi/dingo-api-helper"><img src="https://poser.pugx.org/godruoyi/dingo-api-helper/downloads" alt="dingo-api-helper"></a>
    <a href="https://packagist.org/packages/godruoyi/dingo-api-helper"><img src="https://poser.pugx.org/godruoyi/dingo-api-helper/license" alt="dingo-api-helper"></a>
</p>

## 说明

默认情况下，Dingo/api 提供的 response 需要配置一大堆信息，包括

1、Adapter 的配置

```php
$app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {
    $fractal = new League\Fractal\Manager;

    $fractal->setSerializer(new League\Fractal\Serializer\ArraySerializer);

    return new Dingo\Api\Transformer\Adapter\Fractal($fractal);
});
```

并且默认的 ArraySerializer 返回格式比较奇怪，如返回资源集合时，ArraySerializer 的返回格式如下：

```json
{
    "data": [
        {
            "id": 1,
            "name": "lianbo"
        }
    ]
}
```

> 在最外层包含一层无用 data 

2、Exception 的转换

当 Laravel 抛出一个 Model not found 等异常时，默认情况 dingo/api 会返回如下格式的响应：

```json
{
    "message": "No transformer of ""found.",
    "status_code": 500
}
```

该响应的 http status 为 500，这显然不是我们想要的 404 响应。这时你必须的手动转换异常。

```php
app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('404 Not Found!');
});
```

3、无法自定义错误码

从上面可以看到，dingo/api 返回的错误响应无法自定义错误码，错误信息里只含有标准的 http 状态码，这显示是不够用的。

> 虽然他也提供了 errorFormat 配置，但该配置的 code 错误码实际调用的是 $exception->getCode()，在实际工作中并没什么用。

综上，我们开发了一个新的 Package 来解决以上问题，并提供了如

* 异常转换
* 自定义错误码
* 标准的 restful 支持
* 适时的禁用预加载

等功能，希望您的喜欢 ❤️❤️❤️

## 安装

```shell
$ composer require godruoyi/dingo-api-helper -vvv
```

## 使用

Laravel 版本小于 5.5 时，需要手动注册 Provider。

```php
Godruoyi\DingoApiHelper\ServiceProvider::class,
```

配置成功后，即可在你的 controller 使用:

```php
use Dingo\Api\Routing\Helpers;

class ExampleController extends Controller
{
    use Helpers;

    public function __invoke()
    {
        $users = User::all();

        return $this->response->collection($users);
        // return $this->response->collection($users, new OtherTransformer);
    }
}
```

默认的 Transformer 文件在 App/Transformers 下，你可通过修改方法的第二个参数指定 transformer 的位置。

```php
public function __invoke(Request $request)
{
    $user = User::find($request->id);

    return $this->response->item($user, new \Other\UserDetailTransformer);
}
```

## 可用的方法列表

成功响应方法列表

```php
// 响应一个 Arrayable 的数据
return $this->response->created($content = null);
return $this->response->success($content = null);
return $this->response->array($content = null);
return $this->response->accepted($content = null);

// 无内容响应
return $this->response->noContent();

// 返回一个集合
return $this->response->collection($collection);
// 返回单个详情
return $this->response->item($item);
// 返回分页数据
return $this->response->paginator($paginator);
```

错误响应方法列表

```php
// 一个自定义消息和状态码的普通错误。
return $this->response->error('This is an error.', 404, $errorCode = null);

// 一个没有找到资源的错误，第一个参数可以传递自定义消息。
return $this->response->errorNotFound($message = 'Not Found', $errorCode = null);

// 一个 bad request 错误，第一个参数可以传递自定义消息。
return $this->response->errorBadRequest($message = 'Bad Request', $errorCode = null);

// 一个服务器拒绝错误，第一个参数可以传递自定义消息。
return $this->response->errorForbidden($message = 'Forbidden', $errorCode = null);

// 一个内部错误，第一个参数可以传递自定义消息。
return $this->response->errorInternal($message = 'Internal error', $errorCode = null);

// 一个未认证错误，第一个参数可以传递自定义消息。
return $this->response->errorUnauthorized($message = 'Unauthorized', $errorCode = null);
```

> 更多详情请参考 https://learnku.com/docs/dingo-api/2.0.0/Responses/1446

## 返回格式

返回格式遵循 Restful api 设计规范，具体如下。

### Success Response

1、获取单个资源详情（item）

```json
{
    "id": 1,
    "username": "godruoyi",
    "age": 88,
}
```

2、获取资源集合（collection）

```json
[
    {
        "id": 1,
        "username": "godruoyi",
        "age": 88,
    },
    {
        "id": 2,
        "username": "foo",
        "age": 88,
    }
]
```

3、额外的媒体信息（paginator）

```json
{
    "data": [
        {
            "id": 1,
            "avatar": "https://lorempixel.com/640/480/?32556",
            "nickname": "fwest",
            "last_logined_time": "2018-05-29 04:56:43",
            "has_registed": true
        },
        {
            "id": 2,
            "avatar": "https://lorempixel.com/640/480/?86144",
            "nickname": "zschowalter",
            "last_logined_time": "2018-06-16 15:18:34",
            "has_registed": true
        }
    ],
    "meta": {
        "pagination": {
            "total": 101,
            "count": 2,
            "per_page": 2,
            "current_page": 1,
            "total_pages": 51,
            "links": {
                "next": "http://api.example.com?page=2"
            }
        }
    }
}
```

### Failure Response

```json
{"error_code":40301,"message":"权限不足"}
```

## 禁用预加载

有时候你可能想在 Transformer 中定义一些不存在的 Relation include，如：

```php
namespace App\Transformers;

class UserTransformer extends TransformerAbstract
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['notExistsRelacation'];

    public function transform(User $user, $a)
    {
    }
}
```

由于 [fractal](https://fractal.thephpleague.com/) 默认开启了预加载机制，你可能会得到一个如下的错误：

```
production.ERROR: Call to undefined relationship [notExistsRelacation] on model [App\Models\User]
    Illuminate\\Database\\Eloquent\\RelationNotFoundException(code: 0): 
        Call to undefined relationship [notExistsRelacation] on model [App\Models\User]. 
        at vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php(548): 
```

可通过禁用 Model 的预加载解决

```php
class ExampleController extends Controller
{
    use Helpers;

    public function __invoke()
    {
        $users = User::all();

        return $this->response->disableEagerLoading()->collection($users);
    }
}
```

## 参考

* https://github.com/godruoyi/restful-api-specification
* https://github.com/yikeio/yike
* https://learnku.com/docs/dingo-api/2.0.0/