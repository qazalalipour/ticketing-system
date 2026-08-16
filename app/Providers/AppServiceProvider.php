<?php

namespace App\Providers;

use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Repositories\TicketRepository;
use App\Services\External\Contracts\TicketSenderInterface;
use App\Services\External\FakeTicketSender;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(TicketSenderInterface::class, FakeTicketSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
