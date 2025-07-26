<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorPreview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorPreview>
 */
class VendorPreviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    protected $model = VendorPreview::class;

    /**
     * Define the pool of static image paths.
     * These are the images your previews will draw from.
     * This array is 'static' because it's shared across all instances of the factory.
     */
    private static array $imagePool = [
        'food preview 1.jpeg',
        'food preview 2.jpg',
        'food preview 3.jpeg',
        'food preview 4.jpg',
        'food preview 5.jpeg',
    ];

    /**
     * Define the model's default state.
     * This method is called when VendorPreview::factory() is invoked.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendorId = Vendor::inRandomOrder()->first()->vendorId ?? Vendor::factory()->create()->vendorId;

        return [
            'vendorId' => $vendorId,
            'previewPicturePath' => $this->faker->randomElement(self::$imagePool),
        ];
    }

    /**
     * Define a state for a specific vendor.
     * Use this when you want to associate the preview with a known vendor ID.
     * Example: VendorPreview::factory()->forVendor($someVendorId)->create()
     */
    public function forVendor(int $vendorId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'vendorId' => $vendorId,
        ]);
    }

    /**
     * Define a state for a random existing vendor.
     * Use this when you want the factory to pick a random vendor,
     * without iterating through them in the seeder.
     * Example: VendorPreview::factory()->forRandomVendor()->create()
     */
    public function forRandomVendor(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'vendorId' => Vendor::inRandomOrder()->first()->vendorId ?? Vendor::factory()->create()->vendorId,
        ]);
    }

    /**
     * Define a state to attach a specific image path.
     * Useful if you want to explicitly assign image paths from the seeder.
     * Example: VendorPreview::factory()->withImagePath('path/to/specific.jpg')->create()
     */
    public function withImagePath(string $path): Factory
    {
        return $this->state(fn (array $attributes) => [
            'previewPicturePath' => $path,
        ]);
    }
}
