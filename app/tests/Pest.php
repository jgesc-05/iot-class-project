<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

pest()->extend(Tests\TestCase::class)->in('Feature');

/*
| Aislamiento entre tests
|
| En vez de RefreshDatabase (que dropea tablas en lote y TimescaleDB
| rechaza por la hypertable), usamos transacciones: cada test corre
| en una transacción que se revierte al final.
|
| Requiere que la base de tests ya tenga las migraciones aplicadas
| antes de correr pest. Eso se hace una sola vez con:
|   docker compose exec app php artisan migrate --env=testing
*/
pest()->use(DatabaseTransactions::class)->in('Feature/Api');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
