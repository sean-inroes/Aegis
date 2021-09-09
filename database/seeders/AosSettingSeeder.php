<?php

namespace Database\Seeders;

use App\Models\AosSetting;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class AosSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new Client();

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

        AosSetting::create([
            'last_blocks' => null,
            'price' => $price
        ]);
    }
}
