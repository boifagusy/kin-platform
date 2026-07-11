run_tests()   { php artisan test "$@"; }
run_build()   { echo "No build step for Laravel"; }
run_dev()     { php artisan serve; }
run_migrate() { php artisan migrate; }
run_seed()    { php artisan db:seed; }
