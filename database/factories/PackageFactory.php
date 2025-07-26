<?php

namespace Database\Factories;

use App\Models\CuisineType;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoryId' => PackageCategory::inRandomOrder()->first()->categoryId,
            'vendorId' => Vendor::inRandomOrder()->first()->vendorId,
            'name' => fake()->words(rand(1, 3), true),
            // 'menuPDFPath' => fake()->filePath('public', 'pdfs', false),
            // 'imgPath' => fake()->filePath('public', 'images', false),
            // 'menuPDFPath' => 'menus/' . fake()->slug() . '.pdf',
            'menuPDFPath' => fake()->randomElement(['vegetarian-package-menu.pdf', 'meal_package_1.pdf', 'meal_package_2.pdf', 'meal_package_3.pdf']),
            // 'imgPath' => 'images/' . fake()->slug() . '.jpg',
            'imgPath' => fake()->randomElement(['logo-packages.png', 'meat logo.png', 'other logo.png', 'vegetarian logo.png']),
            'averageCalories' => fake()->randomFloat(2, 100, 1000), // Contoh: 150.00
            'breakfastPrice' => fake()->randomElement([fake()->randomFloat(2, 100000, 1000000), null]),
            'lunchPrice' => fake()->randomElement([fake()->randomFloat(2, 100000, 1000000), null]),
            'dinnerPrice' => fake()->randomElement([fake()->randomFloat(2, 100000, 1000000), null]),
        ];
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Package $package) {
    //         // Check the values that were initially generated
    //         $breakfastPrice = $package->breakfastPrice;
    //         $lunchPrice = $package->lunchPrice;
    //         $dinnerPrice = $package->dinnerPrice;

    //         // If all three prices are null, set one of them to a random value
    //         if (is_null($breakfastPrice) && is_null($lunchPrice) && is_null($dinnerPrice)) {
    //             $options = ['breakfastPrice', 'lunchPrice', 'dinnerPrice'];
    //             $chosenPrice = $this->faker->randomElement($options);

    //             $package->{$chosenPrice} = $this->faker->randomFloat(2, 100000, 1000000);
    //         }


    //         // Ambil semua ID cuisine yang sudah ada (dari CuisineTypeSeeder)
    //         $cuisineIds = CuisineType::pluck('cuisineId')->toArray();

    //         // Fallback jika tidak ada cuisine (hanya untuk mencegah error di development)
    //         if (empty($cuisineIds)) {
    //              // Ini akan membuat 1 cuisine jika tidak ada,
    //              // untuk memungkinkan factory Package tetap berjalan.
    //              $cuisineIds = [CuisineType::factory()->create()->cuisineId];
    //         }

    //         // Pilih jumlah cuisine yang akan dikaitkan (1 hingga 3)
    //         $numCuisinesToAttach = $this->faker->numberBetween(1, min(3, count($cuisineIds)));

    //         // Ambil cuisine IDs secara acak tanpa duplikasi
    //         $randomCuisineIds = collect($cuisineIds)
    //             ->shuffle()
    //             ->take($numCuisinesToAttach)
    //             ->toArray();

    //         // Attach cuisine(s) to the package using sync()
    //         $package->cuisineTypes()->sync($randomCuisineIds);
    //     });
    // }


     public function configure()
    {
        // Chain kedua callback
        return $this->afterMaking(function (Package $package) {
            // Periksa nilai yang awalnya dihasilkan dari metode definition()
            $breakfastPrice = $package->breakfastPrice;
            $lunchPrice = $package->lunchPrice;
            $dinnerPrice = $package->dinnerPrice;

            // Jika ketiga harga adalah null, atur salah satunya ke nilai acak
            if (is_null($breakfastPrice) && is_null($lunchPrice) && is_null($dinnerPrice)) {
                $options = ['breakfastPrice', 'lunchPrice', 'dinnerPrice'];
                $chosenPrice = $this->faker->randomElement($options);

                // Pastikan kita langsung menetapkan ke objek package
                $package->{$chosenPrice} = $this->faker->randomFloat(2, 100000, 1000000);
            }
        })->afterCreating(function (Package $package) { // Chain afterCreating di sini
            // Ambil semua ID cuisine yang sudah ada (dari CuisineTypeSeeder)
            $cuisineIds = CuisineType::pluck('cuisineId')->toArray();

            // Fallback jika tidak ada cuisine (hanya untuk mencegah error di development)
            if (empty($cuisineIds)) {
                $cuisineIds = [CuisineType::factory()->create()->cuisineId];
            }

            // Pilih jumlah cuisine yang akan dikaitkan (1 hingga 3)
            $numCuisinesToAttach = $this->faker->numberBetween(1, min(3, count($cuisineIds)));

            // Ambil cuisine IDs secara acak tanpa duplikasi
            $randomCuisineIds = collect($cuisineIds)
                ->shuffle()
                ->take($numCuisinesToAttach)
                ->toArray();

            // Attach cuisine(s) to the package using sync()
            $package->cuisineTypes()->sync($randomCuisineIds);
        });
    }
}
