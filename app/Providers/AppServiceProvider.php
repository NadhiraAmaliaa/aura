<?php

namespace App\Providers;

use App\Database\Query\Grammars\SqlServer2008Grammar;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Replace the default SQL Server query grammar with a SQL Server 2008
        // compatible version that uses ROW_NUMBER() instead of OFFSET/FETCH.
        // OFFSET ... ROWS FETCH NEXT ... ROWS ONLY was introduced in SQL Server
        // 2012; SQL Server 2008 / 2008 R2 requires the ROW_NUMBER() approach.
        SqlServerConnection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            $conn = new SqlServerConnection($connection, $database, $prefix, $config);
            $conn->setQueryGrammar(new SqlServer2008Grammar($conn));

            return $conn;
        });
        // if (env('DEMO_NOW')) {
        //     Carbon::setTestNow(Carbon::parse(env('DEMO_NOW')));
        // }
    }
}