Installation Instructions:

Pre-requisites:
1) Composer
2) PHP 7.3
3) MariaDB 10.4.10

Step 1: create a db "stock_csv" and import the sql from "./database/stock_csv.sql"

Step 2: Go to config, update database login information "./config/db-config.conf"

Step 3: run command "composer install"

Step 4: Go to the URL "http://localhost/stock-analysis/"

Step 5: Import the CSV sample from "data/sample_stock_price_sorted.csv"


Run Tests:
Example 1: run the command "./vendor/bin/phpunit --bootstrap vendor/autoload.php UnitTestFiles/Test/ImportCsvTest.php"

Test Result:
PHPUnit 7.5.20 by Sebastian Bergmann and contributors.

.....................                                             21 / 21 (100%)

Time: 92 ms, Memory: 4.00 MB

OK (21 tests, 63 assertions)