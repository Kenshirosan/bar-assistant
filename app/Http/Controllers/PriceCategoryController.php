<?php

declare(strict_types=1);

namespace Kami\Cocktail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OAT;
use Illuminate\Http\JsonResponse;
use Kami\Cocktail\OpenAPI as BAO;
use Kami\Cocktail\Models\PriceCategory;
use Illuminate\Http\Resources\Json\JsonResource;
use Kami\Cocktail\Http\Requests\PriceCategoryRequest;
use Kami\Cocktail\Http\Resources\PriceCategoryResource;

class PriceCategoryController extends Controller
{
    #[OAT\Get(path: '/price-categories', tags: ['Price category'], operationId: 'listPriceCategories', description: 'List all price categories in a bar', summary: 'List price categories', parameters: [
        new BAO\Parameters\BarIdHeaderParameter(),
    ])]
    #[BAO\SuccessfulResponse(content: [
        new BAO\WrapItemsWithData(PriceCategoryResource::class),
    ])]
    public function index(): JsonResource
    {
        $priceCategories = PriceCategory::orderBy('name')->filterByBar()->get();

        return PriceCategoryResource::collection($priceCategories);
    }

    #[OAT\Get(path: '/price-categories/{id}', tags: ['Price category'], description: 'Show a single price category', summary: 'Show price category', parameters: [
        new BAO\Parameters\DatabaseIdParameter(),
    ])]
    #[BAO\SuccessfulResponse(content: [
        new BAO\WrapObjectWithData(PriceCategoryResource::class),
    ])]
    #[BAO\NotAuthorizedResponse]
    #[BAO\NotFoundResponse]
    public function show(Request $request, int $id): JsonResource
    {
        $priceCategory = PriceCategory::findOrFail($id);

        if ($request->user()->cannot('show', $priceCategory)) {
            abort(403);
        }

        return new PriceCategoryResource($priceCategory);
    }

    #[OAT\Post(path: '/price-categories', tags: ['Price category'], operationId: 'savePriceCategory', description: 'Create a new price category', summary: 'Create price category', parameters: [
        new BAO\Parameters\BarIdHeaderParameter(),
    ], requestBody: new OAT\RequestBody(
        required: true,
        content: [
            new OAT\JsonContent(ref: BAO\Schemas\PriceCategoryRequest::class),
        ]
    ))]
    #[OAT\Response(response: 201, description: 'Successful response', content: [
        new BAO\WrapObjectWithData(PriceCategoryResource::class),
    ], headers: [
        new OAT\Header(header: 'Location', description: 'URL of the new resource', schema: new OAT\Schema(type: 'string')),
    ])]
    #[BAO\NotAuthorizedResponse]
    public function store(PriceCategoryRequest $request): JsonResponse
    {
        if ($request->user()->cannot('create', PriceCategory::class)) {
            abort(403);
        }

        $priceCategory = new PriceCategory();
        $priceCategory->name = $request->input('name');
        $priceCategory->description = $request->input('description');
        $priceCategory->currency = $request->input('currency');
        $priceCategory->bar_id = bar()->id;
        $priceCategory->save();

        return (new PriceCategoryResource($priceCategory))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('price-categories.show', $priceCategory->id));
    }

    #[OAT\Put(path: '/price-categories/{id}', tags: ['Price category'], operationId: 'updatePriceCategory', description: 'Update a single price category', summary: 'Update price category', parameters: [
        new BAO\Parameters\DatabaseIdParameter(),
    ], requestBody: new OAT\RequestBody(
        required: true,
        content: [
            new OAT\JsonContent(ref: BAO\Schemas\PriceCategoryRequest::class),
        ]
    ))]
    #[BAO\SuccessfulResponse(content: [
        new BAO\WrapObjectWithData(PriceCategoryResource::class),
    ])]
    #[BAO\NotAuthorizedResponse]
    #[BAO\NotFoundResponse]
    public function update(int $id, PriceCategoryRequest $request): JsonResource
    {
        $priceCategory = PriceCategory::findOrFail($id);

        if ($request->user()->cannot('edit', $priceCategory)) {
            abort(403);
        }

        $priceCategory->name = $request->input('name');
        $priceCategory->description = $request->input('description');
        $priceCategory->currency = $request->input('currency');
        $priceCategory->save();

        return new PriceCategoryResource($priceCategory);
    }

    #[OAT\Delete(path: '/price-categories/{id}', tags: ['Price category'], operationId: 'deletePriceCategory', description: 'Delete a single price category', summary: 'Delete price category', parameters: [
        new BAO\Parameters\DatabaseIdParameter(),
    ])]
    #[OAT\Response(response: 204, description: 'Successful response')]
    #[BAO\NotAuthorizedResponse]
    #[BAO\NotFoundResponse]
    public function delete(Request $request, int $id): Response
    {
        $priceCategory = PriceCategory::findOrFail($id);

        if ($request->user()->cannot('delete', $priceCategory)) {
            abort(403);
        }

        $priceCategory->delete();

        return new Response(null, 204);
    }
}
