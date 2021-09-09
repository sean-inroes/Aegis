<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public static function create()
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/create");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function balance($address)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/balance?address=$address");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function tokenBalance($address)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/cone/balanceOf?address=$address");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function gasPrice()
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/gasPrice");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function tokenGasPrice($from, $to, $value)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/cone/gasPrice?fromAdr=$from&toAdr=$to&value=$value");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function transfer($from, $to, $pk, $value, $gasPrice = 50)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/transfer?fromAdr=$from&toAdr=$to&privateKey=$pk&value=$value&gasPrice=$gasPrice");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function tokenTransfer($from, $to, $pk, $value, $gasPrice = 100)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/cone/transfer?fromAdr=$from&toAdr=$to&privateKey=$pk&value=$value&gasPrice=$gasPrice");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function decimals()
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/cone/decimals");

        $response = $request->getBody();

        return json_decode($response);
    }

    public static function isAddress($address)
    {
        $client = new Client();
        $request = $client->request('post', env('API_SERVER')."/isAddress?address=$address");

        $response = $request->getBody();

        return json_decode($response);
    }
}
