<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugImageUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->is('products*')) {
            Log::info('=== PRODUCT UPLOAD DEBUG ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Has file image: ' . ($request->hasFile('image') ? 'YES' : 'NO'));
            Log::info('Image URL: ' . ($request->filled('image_url') ? $request->image_url : 'EMPTY'));
            
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                Log::info('File name: ' . $file->getClientOriginalName());
                Log::info('File size: ' . $file->getSize());
                Log::info('File mime: ' . $file->getMimeType());
                Log::info('File is valid: ' . ($file->isValid() ? 'YES' : 'NO'));
            }
            
            Log::info('All request data: ' . json_encode($request->all()));
        }

        return $next($request);
    }
}
