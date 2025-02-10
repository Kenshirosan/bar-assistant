<?php

declare(strict_types=1);

namespace Kami\Cocktail\External\Model;

use Kami\Cocktail\External\SupportsCSV;
use Kami\Cocktail\Models\ComplexIngredient;
use Kami\Cocktail\External\SupportsDataPack;
use Kami\Cocktail\Models\Image as ImageModel;
use Kami\Cocktail\Models\Ingredient as IngredientModel;
use Kami\Cocktail\Models\IngredientPrice as IngredientPriceModel;

readonly class Ingredient implements SupportsDataPack, SupportsCSV
{
    /**
     * @param array<Image> $images
     * @param array<IngredientBasic> $ingredientParts
     * @param array<IngredientPrice> $prices
     */
    private function __construct(
        public string $id,
        public string $name,
        public ?string $parentId = null,
        public float $strength = 0.0,
        public ?string $description = null,
        public ?string $origin = null,
        public ?string $color = null,
        public ?string $category = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public array $images = [],
        public array $ingredientParts = [],
        public array $prices = [],
        public ?string $calculatorId = null,
    ) {
    }

    public static function fromModel(IngredientModel $model, bool $useFileURI = false): self
    {
        $images = $model->images->map(function (ImageModel $image) use ($useFileURI) {
            return Image::fromModel($image, $useFileURI);
        })->toArray();

        $ingredientParts = $model->ingredientParts->map(function (ComplexIngredient $part) {
            return IngredientBasic::fromModel($part->ingredient);
        })->toArray();

        $ingredientPrices = $model->prices->map(function (IngredientPriceModel $price) {
            return IngredientPrice::fromModel($price);
        })->toArray();

        return new self(
            $model->getExternalId(),
            $model->name,
            $model->parent_ingredient_id ? $model->parentIngredient->getExternalId() : null,
            $model->strength,
            $model->description,
            $model->origin,
            $model->color,
            $model->category?->name ?? null,
            $model->created_at->toAtomString(),
            $model->updated_at?->toAtomString(),
            $images,
            $ingredientParts,
            $ingredientPrices,
            $model->calculator?->getExternalId(),
        );
    }

    public static function fromDataPackArray(array $sourceArray): self
    {
        $images = [];
        foreach ($sourceArray['images'] ?? [] as $sourceImage) {
            $images[] = Image::fromDataPackArray($sourceImage);
        }

        $ingredientParts = [];
        foreach ($sourceArray['ingredient_parts'] ?? [] as $ingredient) {
            $ingredientParts[] = IngredientBasic::fromDataPackArray($ingredient);
        }

        return new self(
            $sourceArray['_id'],
            $sourceArray['name'],
            $sourceArray['_parent_id'] ?? null,
            $sourceArray['strength'] ?? 0.0,
            $sourceArray['description'] ?? null,
            $sourceArray['origin'] ?? null,
            $sourceArray['color'] ?? null,
            $sourceArray['category'] ?? null,
            $sourceArray['created_at'] ?? null,
            $sourceArray['updated_at'] ?? null,
            $images,
            $ingredientParts,
            [],
            $sourceArray['calculator_id'] ?? null,
        );
    }

    public function toDataPackArray(): array
    {
        return [
            '_id' => $this->id,
            '_parent_id' => $this->parentId,
            'name' => $this->name,
            'strength' => $this->strength,
            'description' => $this->description,
            'origin' => $this->origin,
            'color' => $this->color,
            'category' => $this->category,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'images' => array_map(fn ($model) => $model->toDataPackArray(), $this->images),
            'ingredient_parts' => array_map(fn ($model) => $model->toDataPackArray(), $this->ingredientParts),
            'prices' => array_map(fn ($model) => $model->toDataPackArray(), $this->prices),
            'calculator_id' => $this->calculatorId,
        ];
    }

    public static function fromCSV(array $sourceArray): self
    {
        $sourceArray = array_change_key_case($sourceArray, CASE_LOWER);

        $images = [];
        $ingredientParts = [];

        return new self(
            'CSV',
            $sourceArray['name'],
            null,
            isset($sourceArray['strength']) ? floatval($sourceArray['strength']) : 0.0,
            blank($sourceArray['description']) ? null : $sourceArray['description'],
            blank($sourceArray['origin']) ? null : $sourceArray['origin'],
            blank($sourceArray['color']) ? null : $sourceArray['color'],
            blank($sourceArray['category']) ? null : $sourceArray['category'],
            null,
            null,
            $images,
            $ingredientParts,
        );
    }
}
