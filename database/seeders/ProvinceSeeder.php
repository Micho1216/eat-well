<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'provinsi';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        // $provinces = retry(3, fn() => HTTP::get($url.$hierarchy. $apiKey));

        try{
            $provinces = HTTP::retry(3, 1000,function (int $attempt, Exception $exception){
                echo('Attempt'.$attempt.': '.$exception);
                return true;
            })->get($url.$hierarchy.$apiKey);

            $provinces = $provinces->json('value');
            $provincesCount = count($provinces);
            # Because seeding provinces, cities, districts, villages took so long, i made a progress bar

            $output = new ConsoleOutput();
            $insertProgress = new ProgressBar($output, $provincesCount);
            $insertProgress->setFormat("Inserting provinces to database\n %current%/%max% [%bar%] %percent:3s%%  %message%\n");
            $insertProgress->start();            
            $startTime = microtime(true);

            $i = 0;
            foreach($provinces as $province)
            {
                DB::table('provinces')->insert([
                    'id' => $province['id'],
                    'name' => $province['name']
                ]);
                $elapsedTime = microtime(true) - $startTime;
                $remainingTime = ($elapsedTime/($i + 1)) * ($provincesCount - $i -1);
                $insertProgress->setMessage(sprintf('Elapsed: %.2f sec, Remaining: %.2f sec', $elapsedTime, $remainingTime));
                $insertProgress->advance();
                $i += 1;
            }
            $insertProgress->finish();
        } catch(Exception $e){
            echo($e);
        }
    }
}
