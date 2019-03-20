## Installation

You can install the package via composer:

``` bash
composer require rjvim/file-manager
```

The package will automatically register itself.

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Betalectic\FileManager\FileManagerServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Betalectic\FileManager\FileManagerServiceProvider"
```

```bash
php artisan migrate
```

## Documentation

* Upload and retrieving the files
* Upload the base 64 image also

php artisan vendor:publish //Delete existing file and run

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
