# Activity Manager
- This module will record your system errors.

## How to use
- Your try catch code will be modify like this
```
try {
    <!-- comments -->
} catch (\Throwable $e) {
    <!-- for json response use return else remove return -->
    return ApiExceptionHandler::handle($e);
}
```


## Guide to setup
- composer require spatie/laravel-activitylog
- php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
- php artisan migrate
- php artisan install:broadcasting --reverb
