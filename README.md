# Laravel Auth API

[![][app-version]][app-releases]

| Branch      |              Build               |               Coverage               |              Style               |
| ----------- | :------------------------------: | :----------------------------------: | :------------------------------: |
| [main][app] | [![][app-build-main]][app-build] | [![][app-main-codecov]][app-codecov] | [![][app-style-main]][app-style] |

<!-- Links -->
[app]: https://github.com/lcharette/Laravel-Auth-API
[app-build]: https://github.com/lcharette/Laravel-Auth-API/actions?query=workflow%3ABuild
[app-build-main]: https://github.com/lcharette/Laravel-Auth-API/workflows/Build/badge.svg?branch=main
[app-version]: https://img.shields.io/github/release/lcharette/Laravel-Auth-API.svg
[app-main-codecov]: https://codecov.io/gh/lcharette/Laravel-Auth-API/branch/main/graph/badge.svg?token=3ZHQD39KK6
[app-releases]: https://github.com/lcharette/Laravel-Auth-API/releases
[app-codecov]: https://codecov.io/gh/lcharette/Laravel-Auth-API
[app-style-main]: https://github.com/lcharette/Laravel-Auth-API/workflows/Style%20CI/badge.svg?branch=main
[app-style]: https://github.com/lcharette/Laravel-Auth-API/actions?query=workflow%3A%22Style+CI%22


Basic reusable auth API routes for Laravel based SPA application. No UI is provided with this package, except the one require for email validation and password recovery. This is meant to be the starting point for your Vue (or similar) based frontend.

**This is still a work in progress and might not be used for production yet**

## Usage

### Setup

```bash
composer require lcharette/laravel-auth-api
```

Next, you'll need to update your User model so it implemen `Tymon\JWTAuth\Contracts\JWTSubject`. The `Lcharette\AuthApi\Auth\isJWTSubject` trait can be used to add the required methods. For example : 

```php
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasFactory, isJWTSubject;
   
    ...
}
```

### Available Routes 

Once installed, this packages adds the following routes to you Laravel app : 

| Method | Route           | Description                                            | Required Data                                       |
| :----: | --------------- | ------------------------------------------------------ | --------------------------------------------------- |
|  POST  | `/api/register` | Post to this route to register a new user.             | `username`, `email`, `password`, `password_confirm` |
|  POST  | `/api/login`    | Perform login and return auth token.                   | `email`, `password`                                 |
|  POST  | `/api/logout`   | Perform logout action and invalidade current token.    |                                                     |
|  POST  | `/api/refresh`  | Refresh current token and returns new one.             |                                                     |
|  GET   | `/api/user`     | Return the current user information inside `data` key. |                                                     |

All routes will return a `200` status with json string if successfull. Any error will returns as a `400` error code with the error detail inside the json response. A `403` (forbidden) status code will be returned if the route is accessed without a valid token (except for login and register routes).

### Limiting Other Routes

If you want to limit routes from your app to only "logged in" user, that is users that provides a valid token, you can add the `Lcharette\AuthApi\Http\Middleware\RequireAuth` middleware to any route or group of route. For example, this will make `/list` returns a `403` error if a valid token is not passed with the request : 

```php
Route::middleware([RequireAuth::class, 'api'])->group(function () {
    Route::get('/lists', [ListController::class, 'index']);
});
```

Alternatively, the `Lcharette\AuthApi\Http\Middleware\RequireGuest` middleware can be used if the route required the user **not** to be logged in.

### Posting Token from Axios (Vue.js)

Token can be retreived from the login response and set as default header for future axios request. Just be sure to removed the token on logout or 401 error.

```js
axios.post("/api/login", { email, password })
    .then(resp => {
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + resp.data.access_token
    })
    .catch(err => {
        localStorage.removeItem("access_token");
    });
```

## TODO
 - Revoke token
 - Update profile
 - Update password
 - Two Factor
 - Password reset
 - Email confirmation
 - Custom user Trait / Interface
 - Add more customisabilisation 

## License

This package is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
