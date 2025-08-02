<?php

namespace App\Observers;

use App\Models\PackageCategory;

class PackageCategoryObserver
{
    /**
     * Handle the PackageCategory "created" event.
     */
    public function created(PackageCategory $packageCategory): void
    {
        //
    }

    /**
     * Handle the PackageCategory "updated" event.
     */
    public function updated(PackageCategory $packageCategory): void
    {
        //
    }

    /**
     * Handle the PackageCategory "deleted" event.
     */
    public function deleted(PackageCategory $packageCategory): void
    {
        if ($packageCategory->isForceDeleting()) {
            return;
        }

        $packageCategory->categoryName = $packageCategory->categoryName . '_' . now()->format('Ymd_His');
        $packageCategory->saveQuietly();
    }

    /**
     * Handle the PackageCategory "restored" event.
     */
    public function restored(PackageCategory $packageCategory): void
    {
        //
    }

    /**
     * Handle the PackageCategory "force deleted" event.
     */
    public function forceDeleted(PackageCategory $packageCategory): void
    {
        //
    }
}
