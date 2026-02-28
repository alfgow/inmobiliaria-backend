<?php

namespace App\Livewire;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Services\InmuebleImageService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class PropertyGalleryManager extends Component
{
    use WithFileUploads;

    public Inmueble $inmueble;
    public string $watermarkPreviewUrl = '';

    /**
     * @var \Illuminate\Http\UploadedFile[]
     */
    public $photos = [];

    public $images = [];

    protected $rules = [
        'photos.*' => 'image|max:10240', // Configurable max size, currently 10MB
    ];

    public function mount(Inmueble $inmueble, string $watermarkPreviewUrl = '')
    {
        $this->inmueble = $inmueble;
        $this->watermarkPreviewUrl = $watermarkPreviewUrl;
        $this->loadImages();
    }

    public function loadImages()
    {
        // Must convert to array to avoid Livewire model hydration issues when dragging/dropping
        $this->images = $this->inmueble->images()->orderBy('orden')->get()->toArray();
    }

    public function updatedPhotos()
    {
        $this->validate();

        if (count($this->photos) > 0) {
            try {
                $imageService = app(InmuebleImageService::class);
                $imageService->storeImages($this->inmueble, $this->photos);
            } catch (\Exception $e) {
                Log::error('Error uploading photos from Livewire: ' . $e->getMessage());
            }
            
            $this->photos = [];
            $this->loadImages();
            $this->dispatch('gallery-updated'); // trigger front-end events
        }
    }

    public function deleteImage($imageId)
    {
        $image = $this->inmueble->images()->find($imageId);
        if ($image) {
            try {
                $imageService = app(InmuebleImageService::class);
                $imageService->deleteImage($image);
            } catch (\Exception $e) {
                Log::error('Error deleting image from Livewire: ' . $e->getMessage());
            }
            $this->loadImages();
            $this->dispatch('gallery-updated');
        }
    }

    public function updateImageOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            $this->inmueble->images()->where('id', $id)->update(['orden' => $index + 1]);
        }
        $this->loadImages();
        $this->dispatch('gallery-updated');
    }

    public function render()
    {
        return view('livewire.property-gallery-manager');
    }
}
