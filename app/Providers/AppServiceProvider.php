<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use App\Models\SubUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\HrmsAnnouncement;
use App\Observers\HrmsAnnouncementObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {

        \View::composer('*', function ($view) {

            $data = Notification::whereIn('read_status', [1, 0])->orderBy('id', 'DESC')->get();
            $count = Notification::where('read_status', 0)->count();
            $view->with('data', ['data' => $data, 'count' => $count]);
        });

        Paginator::useBootstrap();
        Schema::defaultStringLength(191);

        //send mail to all user for announcement
        HrmsAnnouncement::observe(HrmsAnnouncementObserver::class);

    }
}
