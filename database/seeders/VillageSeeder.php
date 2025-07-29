<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Exception;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'kelurahan';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        $districts = District::all();
        $districtsCount = count($districts);
        
        $output = new ConsoleOutput();
        $insertProgress = new ProgressBar($output, $districtsCount);
        $insertProgress->setFormat("Inserting villages\n%percent:3s%% [%bar%]   %message%\n");
        $insertProgress->start();
        $startTime = microtime(true);

        $i = 0;
        foreach($districts as $district)
        {
            try {
                $districtUrl = '&id_kecamatan='.substr($district->id, 0, 2).'.'.substr($district->id, 2, 2).'.'.substr($district->id, 4, 2);
                $villages = retry(3, fn() => HTTP::get($url.$hierarchy.$apiKey.$districtUrl));
                $villages = $villages->json('value');
        
                foreach($villages as $village)
                {
                    DB::table('villages')->insert([
                        'id' => (int) Str::replace('.', '', $village['id']),
                        'name' => $village['name'], 
                        'district_id' => (int) Str::replace('.', '', $village['id_kecamatan'])
                    ]);
                }
            } catch (Exception $e) {
                echo($e);
            }
            $elapsedTime = microtime(true) - $startTime;
            $remainingTime = ($elapsedTime/($i + 1)) * ($districtsCount - $i -1);
            $insertProgress->setMessage(sprintf('Elapsed: %.2f sec, Remaining: %.2f sec', $elapsedTime, $remainingTime));
            $insertProgress->advance();
            $i += 1;
        }
        $insertProgress->finish();
    }
}
