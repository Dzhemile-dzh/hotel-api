<?php

namespace App\Providers;

use App\Services\PmsApiService;
use App\Services\BookingSyncService;
use App\Repositories\BookingRepository;
use App\Repositories\GuestRepository;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTypeRepository;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(PmsApiService::class, function ($app) {
            return new PmsApiService();
        });

        $this->app->singleton(BookingSyncService::class, function ($app) {
            return new BookingSyncService($app->make(PmsApiService::class));
        });

        // Register repositories as singletons
        $this->app->singleton(BookingRepository::class, function ($app) {
            return new BookingRepository(new Booking());
        });

        $this->app->singleton(GuestRepository::class, function ($app) {
            return new GuestRepository(new Guest());
        });

        $this->app->singleton(RoomRepository::class, function ($app) {
            return new RoomRepository(new Room());
        });

        $this->app->singleton(RoomTypeRepository::class, function ($app) {
            return new RoomTypeRepository(new RoomType());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
