<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CompanyAddresse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProcessUserLatLongChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $chunk;
    public $tempDBname;
   // public $created_at;

    public function __construct($chunk, $tempDBname)
    {
        $this->chunk = $chunk;
        $this->tempDBname = $tempDBname;
       // $this->created_at = $created_at;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tempDBname = $this->tempDBname;
        //$created_at = $this->created_at;
        $GoggleAPIKey = env('GOOGLE_API_KEY');
        $this->connectDB($tempDBname);
        try {
            $companylocation = CompanyAddresse::get()->first();
            $Complat = $companylocation?->latitude ?? null;
            $Complong = $companylocation?->longitude ?? null;

                    foreach ($this->chunk as $SubUserAddresses) {
                        try {

                            $latLong = $this->getLatLongFromAddress($SubUserAddresses['address'],$GoggleAPIKey,$SubUserAddresses['sub_user_id']);

                            if (($latLong['lat'] && $latLong['lng']) && ($Complat && $Complong)) {
                                $officeDistance  = $this->calculateDistance($Complat,$Complong,$latLong['lat'],$latLong['lng']);
                            }else {
                                $officeDistance = null;
                            }
                            DB::update(
                                'UPDATE sub_user_addresses SET latitude = ?, longitude = ?, updated_at = ? WHERE id = ?',
                                [$latLong['lat'], $latLong['lng'], now(), $SubUserAddresses['id']]
                            );

                            DB::update(
                                'UPDATE users SET latitude = ?, longitude = ?, updated_at = ?, office_distance = ? WHERE id = ?',
                                [$latLong['lat'], $latLong['lng'], now(), $officeDistance, $SubUserAddresses['sub_user_id']]
                            );


                        } catch (\Throwable $e) {
                            info('Failed to process address for sub_user_id: '. ' - ' . $e->getMessage());
                        }
                    }

        } catch (\Throwable $e) {
             info('Job failed during chunking: ' . $e->getMessage() .
                                    ' in file ' . $e->getFile() .
                                    ' on line ' . $e->getLine());
        }
    }




    function getLatLongFromAddress($address,$GoggleAPIKey,$userID){
        $address_url = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address_url}&key={$GoggleAPIKey}";
        $resp_json = file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if ($resp['status'] == 'OK') {
            $lat = $resp['results'][0]['geometry']['location']['lat'];
            $lng = $resp['results'][0]['geometry']['location']['lng'];
            return ['lat' => $lat, 'lng' => $lng];
        }else {
            preg_match('/([\w\s\/]+),\s*([\w\s]+),\s*([a-zA-Z\s]+)\s*(\d{6})/', $address, $matches);
            if (count($matches) >= 5) {
                $cityPin = $matches[3] . ' ' . $matches[4]; // example: Amravati 376189
            } else {
                $parts = explode(',', $address);
                $lastTwo = array_slice($parts, -2);
                $cityPin = implode(' ', $lastTwo); // fallback to last parts
            }

            $fallbackAddress = urlencode($cityPin);
            $fallbackUrl = "https://maps.googleapis.com/maps/api/geocode/json?address={$fallbackAddress}&key={$GoggleAPIKey}&region=in";

            $fallbackResponse = file_get_contents($fallbackUrl);
            $fallbackResp = json_decode($fallbackResponse, true);

            if ($fallbackResp['status'] === 'OK') {
                return [
                    'lat' => $fallbackResp['results'][0]['geometry']['location']['lat'],
                    'lng' => $fallbackResp['results'][0]['geometry']['location']['lng']
                ];
            } else {
                // StoreWrongAddressFromExcelSheet::create([
                //     'user_id' => $userID,
                //     'address' => $address,
                // ]);

                DB::insert('INSERT INTO store_wrong_address_from_excel_sheets (user_id, address, created_at, updated_at) VALUES (?, ?, ?, ?)', [
                    $userID,
                    $address,
                    now(),
                    now(),
                ]);

                return ['lat' => null, 'lng' => null];
            }
        }

    }


    function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1) * cos($lat2) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2); // in kilometers
    }


    public function connectDB($db_name){
        $default = [
            "driver" => env("DB_CONNECTION", "mysql"),
            "host" => env("DB_HOST"),
            "port" => env("DB_PORT"),
            "database" => $db_name,
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => false,
            "engine" => null,
        ];

        Config::set("database.connections.$db_name", $default);
        DB::purge($db_name);
        DB::reconnect($db_name);
        DB::setDefaultConnection($db_name);
        Config::set("client_id", 1);
        Config::set("client_connected", true);
    }



}
