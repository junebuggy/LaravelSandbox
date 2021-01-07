<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Image;

use Illuminate\Support\Facades\Storage;

use Image as ImageManager;

class ImageController extends Controller
{
	
	public function showForm()
    {
        return view('imageUpload');
    }

	public function getImages()
    {
        return view('images')->with('images', auth()->user()->images);
    }
	
	public function uploadImage(StoreImage $request)
    {
		if ($request->hasFile('image_file')) {
            //  Let's do everything here
            if ($request->file('image_file')->isValid()) {
                //
                $validated = $request->validate([
                    'image_name' => 'required|string|max:40',
                    'image_file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);
				
				$image = $request->file('image_file');
				
				$thumbnailImage = ImageManager::make($image->getRealPath());
				
				$thumbnailImage->resize(150, 150, function($constraint){
					$constraint->aspectRatio();
				});
				
				$path = Storage::disk('s3')->put('images/thumbnails', $thumbnailImage);
				
				$this->storeImagesToDatabase($request->image_name, "Thumbnail", $path);
				
				$smallImage = ImageManager::make($image->getRealPath());
				
				$smallImage->resize(250, 250, function($constraint){
					$constraint->aspectRatio();
				});
				
				$path = Storage::disk('s3')->put('images/smalls', $smallImage);
				
				$this->storeImagesToDatabase($request->image_name, "Small", $path);
				
				$fullImage = ImageManager::make($image->getRealPath());
				
				//$fullImage->resize(500, 500, function($constraint){
				//	$constraint->aspectRatio();
				//});
				
				$path = Storage::disk('s3')->put('images/originals', $fullImage);
				
				$this->storeImagesToDatabase($request->image_name, "Original", $path);
				
				//$extension = $request->image_file->extension();
				
                //$request->image_file->storeAs('/public', $validated['image_name'].".".$extension);
				
				
				
                return \Redirect::back();
            }
			abort(500, 'file is not Valid');
        }
        abort(500, 'Could not find file');
    }
	
	private function storeImagesToDatabase($imageName, $imageType, $imagePath) {
		$image = new Image();
		
		$image->name = $imageName;
		$image->path = $imagePath;
		$image->type = $imageType;
		$image->save();
	}
}
