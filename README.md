## Test JMT Rizal Zaelani

aplikasi klinik:
cara installasi dan menjalankannya

-   "setup database nya di [.env]"
-   composer install / update.
-   composer dump_autoload
-   php artisan key:generate
-   php artisan migrate
-   php artisan optimize
-   php artisan serve

-   [auth](menggunakan sanctum).
-   [apidocs](menggunakan scribe). untuk mengaktifkannya [php artisan scribe:generate]

## untuk mengimport seed super admin silahkan pakai [php artisan db:seed --class=SuperAdminSeeder]

-   akses apidocs nya [localhost:8000/docs]
-   repo frontend nya ada di [https://github.com/zaelanirizal02/fe-jmtest]
