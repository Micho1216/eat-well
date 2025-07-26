<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorPreviewController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = $request->query('vendorId');
        if (!$vendorId) {
            return response()->json(['status' => 'error', 'message' => 'vendorId is required'], 400);
        }

        $previews = VendorPreview::where('vendorId', $vendorId)->get(['vendorPreviewId', 'previewPicturePath']);
        return response()->json([
            'status' => 'success',
            'previews' => $previews,
        ]);
    }

    public function destroy($id)
    {
        $preview = VendorPreview::findOrFail($id);

        // Hapus file dari storage
        $filePath = public_path($preview->previewPicturePath);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus database
        $preview->delete();

        return response()->json(['status' => 'success']);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'vendorId' => 'required|exists:vendors,vendorId',
        ]);

        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('asset/catering-preview');

        // Pastikan folder ada
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Simpan file ke folder public/asset/catering-preview
        $file->move($destinationPath, $fileName);

        $relativePath = 'asset/catering-preview/' . $fileName;

        $vendorPreview = VendorPreview::create([
            'vendorId' => $request->vendorId,
            'previewPicturePath' => $relativePath,
        ]);

        return response()->json([
            'status' => 'success',
            'preview' => [
                'id' => $vendorPreview->vendorPreviewId,
                'previewPicturePath' => $vendorPreview->previewPicturePath,
                'image_url' => asset($vendorPreview->previewPicturePath),
            ],
        ]);
    }


    public function update($id, Request $request)
    {
        $preview = VendorPreview::findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hapus file lama kalau ada
        if ($preview->previewPicturePath && file_exists(public_path($preview->previewPicturePath))) {
            unlink(public_path($preview->previewPicturePath));
        }

        // Upload file baru
        $file = $request->file('image');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('asset/catering-preview');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);
        $relativePath = 'asset/catering-preview/' . $fileName;

        // Update path di database
        $preview->previewPicturePath = $relativePath;
        $preview->save();

        return response()->json([
            'status' => 'success',
            'preview' => [
                'id' => $preview->vendorPreviewId,
                'previewPicturePath' => $relativePath,
                'image_url' => asset($relativePath),
            ]
        ]);
    }

    public function showVendorDetail($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        return view('manageCateringPackage', compact('vendor'));
    }
}
