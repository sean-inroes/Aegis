<?php

namespace App\Console;

use App\Models\AosSetting;
use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call('\App\Http\Controllers\Deposit@ethereum')->everyMinute()->description("CHECK ETHEREUM TX");
        $schedule->call('\App\Http\Controllers\Deposit@aos')->everyMinute()->description("CHECK AOS TX");
        $schedule->call(function () {
            $client = new Client();
            $aossetting = AosSetting::first();

            $request = $client->request('GET', 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest', [
                'headers' => [
                    'X-CMC_PRO_API_KEY' => '11ecf9e8-9b05-43a7-a1f7-69276be7afa9'
                ],
                'query' => [
                    'symbol' => 'AOS'
                ]
            ]);

            $response = json_decode($request->getBody());

            $price = 0;
            if($response->status->error_code == 0)
            {
                $price = $response->data->AOS->quote->USD->price;
            }

            $aossetting->price = $price;
            $aossetting->save();
        })->dailyAt('01:00')->description("CHECK AOS PRICE");

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
