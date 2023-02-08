<?php

namespace App\Http\Controllers\Api\v1\Link;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use SnappyImage;

class ImageCorsProxyController extends Controller
{

    public function view(Request $request)
    {
        $image_url = $request->query('image_url');

        if ($image_url) {

            // $image_url = urldecode($image_url);

            $type = pathinfo($image_url, PATHINFO_EXTENSION);
            $data = file_get_contents($image_url);
            $base64_image = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $html = "<img src=\"$base64_image\" alt=\"\" style=\"min-width:100%;\" />";

            $snappyImage = SnappyImage::loadHTML($html);

            $filename = time() . '-image.jpg';

            return $snappyImage->stream($filename); // Or $snappyImage->inline($filename);
        } else {
            abort(404);
        }
    }
}
