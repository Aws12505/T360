<?php

namespace App\Services\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant.
     *
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function createTenant(array $data)
    {
        $name = trim($data['name']);
        $words = preg_split('/\s+/', $name);
        $slug = count($words) === 1 ? strtolower($words[0]) : strtolower(implode('', array_map(fn($w) => substr($w, 0, 1), $words)));
        $originalSlug = $slug;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . $count;
            $count++;
        }
        $uniqueCompanyName = $name;
        $companyCount = 1;
        while (Tenant::where('name', $uniqueCompanyName)->exists()) {
            $uniqueCompanyName = $name . ' ' . $companyCount;
            $companyCount++;
        }
        return Tenant::create(['name' => $uniqueCompanyName, 'slug' => $slug]);
    }

    /**
     * Update an existing tenant.
     *
     * @param int $tenantId
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function updateTenant($tenantId, array $data)
    {
        $tenant = Tenant::findOrFail($tenantId);
        // Handle image upload if present
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($tenant->image_path && Storage::disk('public')->exists($tenant->image_path)) {
                Storage::disk('public')->delete($tenant->image_path);
            }
            
            $file = $data['image'];
            $originalExtension = $file->getClientOriginalExtension();
            $filename = 'tenant-' . $tenantId . '-' . time();
            
            // Make sure directories exist
            Storage::disk('public')->makeDirectory('tenant-images');
            Storage::disk('public')->makeDirectory('temp');
            
            // Process based on file type
            if (strtolower($originalExtension) === 'svg') {
                // For SVG files, just store them as is
                $path = $file->storeAs('tenant-images', $filename . '.svg', 'public');
                $tenant->image_path = $path;
            } else {
                // Create image manager with GD driver
                $manager = new ImageManager(new Driver());
                
                // For other image types, optimize and create a simple SVG wrapper
                $img = $manager->read($file);
                
                // Resize to 150x150 while maintaining aspect ratio
                $img->resize(150, 150);
                
                // Save the optimized PNG
                $pngPath = 'tenant-images/' . $filename . '.png';
                $fullPngPath = Storage::disk('public')->path($pngPath);
                
                // Ensure the directory exists
                $pngDir = dirname($fullPngPath);
                if (!is_dir($pngDir)) {
                    mkdir($pngDir, 0755, true);
                }
                
                $img->save($fullPngPath);
                
                // Create a simple SVG that embeds the PNG as base64
                $pngData = base64_encode(file_get_contents($fullPngPath));
                $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" viewBox="0 0 150 150">
  <image width="150" height="150" xlink:href="data:image/png;base64,{$pngData}"/>
</svg>
SVG;
                
                // Save the SVG file
                $svgPath = 'tenant-images/' . $filename . '.svg';
                Storage::disk('public')->put($svgPath, $svgContent);
                
                // Delete the PNG file as we don't need it anymore
                Storage::disk('public')->delete($pngPath);
                
                $path = $svgPath;
                
                // Set the image_path directly on the tenant model instead of in the data array
                $tenant->image_path = $path;
            }
            
            // Remove the image from data as it's not a column in the database
            unset($data['image']);
        }
        
        $tenant->update($data);
        return $tenant;
    }

    /**
     * Delete a tenant.
     *
     * @param int $tenantId
     * @return bool
     */
    public function deleteTenant($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        
        // Delete tenant image if exists
        if ($tenant->image_path && Storage::disk('public')->exists($tenant->image_path)) {
            Storage::disk('public')->delete($tenant->image_path);
        }
        
        // Delete the tenant
        return $tenant->delete();
    }
}