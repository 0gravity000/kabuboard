<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\MinitlyStocksCheck' => [
            'App\Listeners\UpdateStocksInfo',
        ],
        'App\Events\DailyStocksCheck' => [
            'App\Listeners\StoreDailyStocksInfo',
        ],
        'App\Events\DailyDBStoreCheck' => [
            'App\Listeners\DeleteExtraRecodes',
        ],
        'App\Events\DailyCheckAllMeigara' => [
            'App\Listeners\StoreAllMeigaraToDB',
        ],
        'App\Events\DailySignalVolume' => [
            'App\Listeners\StoreDailySignalVolumeToDB',
        ],
        'App\Events\DailySignalAkasanpei' => [
            'App\Listeners\StoreDailySignalAkasanpeiToDB',
        ],

        /*
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        */
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
