<?php

namespace App\Observers;

use App\Models\VendorReview;

class VendorReviewObserver
{
    /**
     * Handle the VendorReview "created" event.
     */
    public function created(VendorReview $vendorReview): void
    {
        $vendor = $vendorReview->vendor;

        if ($vendor) {
            $averageRating = $vendor->vendorReviews()->avg('rating') ?? 0;

            $vendor->rating = round($averageRating, 1);
            $vendor->save();
        }
    }

    /**
     * Handle the VendorReview "updated" event.
     */
    public function updated(VendorReview $vendorReview): void
    {
        if ($vendorReview->isDirty('rating')) {
        $vendor = $vendorReview->vendor;

        if ($vendor) {
            $averageRating = $vendor->vendorReviews()->avg('rating') ?? 0;
            $vendor->rating = round($averageRating, 1);
            $vendor->save();
        }
    }
    }

    /**
     * Handle the VendorReview "deleted" event.
     */
    public function deleted(VendorReview $vendorReview): void
    {
        $vendor = $vendorReview->vendor;

        if ($vendor) {
            $averageRating = $vendor->vendorReviews()->avg('rating') ?? 0;
            $vendor->rating = round($averageRating, 1);
            $vendor->save();
        }
    }

    /**
     * Handle the VendorReview "restored" event.
     */
    public function restored(VendorReview $vendorReview): void
    {
        //
    }

    /**
     * Handle the VendorReview "force deleted" event.
     */
    public function forceDeleted(VendorReview $vendorReview): void
    {
        //
    }
}
