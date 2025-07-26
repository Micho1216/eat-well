<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorPreview;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class VendorPreviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     private array $imagePool = [
        'food preview 1.jpeg',
        'food preview 2.jpg',
        'food preview 3.jpeg',
        'food preview 4.jpg',
        'food preview 5.jpeg',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan ada setidaknya beberapa Vendor di database
        //    Jika VendorSeeder Anda tidak membuat cukup, atau Anda menjalankan seeder ini sendirian.
        if (Vendor::count() === 0) {
            $this->command->info('No vendors found. Creating 5 dummy vendors for preview seeding.');
            Vendor::factory()->count(5)->create(); // Buat 5 vendor dummy jika belum ada
        }

        // 2. Ambil semua vendor yang ada di database
        $vendors = Vendor::all();

        // 3. Iterasi setiap vendor untuk membuat preview mereka
        $vendors->each(function ($vendor) {
            // Tentukan jumlah preview yang akan dibuat untuk vendor ini (misalnya, antara 3 hingga 5)
            $numberOfPreviews = rand(3, 5); 
            $this->command->info("Creating {$numberOfPreviews} previews for Vendor ID: {$vendor->vendorId} ({$vendor->name})");

            // --- Logika untuk menjamin keunikan gambar per vendor, lalu daur ulang jika perlu ---

            // A. Acak (shuffle) daftar semua gambar yang tersedia.
            //    Ini membuat urutan gambar yang berbeda untuk setiap vendor, yang membantu keunikan per vendor.
            $shuffledImages = Arr::shuffle($this->imagePool); 

            // B. Tentukan berapa banyak gambar unik yang bisa diambil dari pool.
            //    `min($numberOfPreviews, count($shuffledImages))` memastikan kita tidak mencoba mengambil lebih dari jumlah gambar yang tersedia.
            $uniqueImagesCount = min($numberOfPreviews, count($shuffledImages));

            // C. Buat preview menggunakan gambar-gambar yang unik ini terlebih dahulu.
            //    Ini menjamin bahwa gambar-gambar yang dibuat DALAM SET VENDOR INI adalah BERBEDA SATU SAMA LAIN.
            for ($i = 0; $i < $uniqueImagesCount; $i++) {
                VendorPreview::factory()->create([
                    'vendorId' => $vendor->vendorId,
                    'previewPicturePath' => $shuffledImages[$i], // Gunakan gambar unik yang sudah diacak
                ]);
            }

            // D. Jika masih perlu membuat lebih banyak preview daripada gambar unik yang tersedia ($numberOfPreviews > $uniqueImagesCount),
            //    lanjutkan dengan membuat sisa preview, mengambil gambar secara acak dari pool (gambar akan berulang).
            for ($i = $uniqueImagesCount; $i < $numberOfPreviews; $i++) {
                VendorPreview::factory()->create([
                    'vendorId' => $vendor->vendorId,
                    'previewPicturePath' => Arr::random($this->imagePool), // Ambil gambar acak dari pool (bisa berulang)
                ]);
            }
        });

        $this->command->info('Vendor Previews seeded successfully!');
    }
}
