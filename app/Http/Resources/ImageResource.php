<?php

declare(strict_types=1);

namespace Kami\Cocktail\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Kami\Cocktail\Models\Image
 */
class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'url' => $this->getImageUrl(),
            'thumb_url' => $this->getImageThumbUrl(),
            'copyright' => $this->copyright,
            'sort' => $this->sort,
            'placeholder_hash' => $this->placeholder_hash,
        ];
    }
}
