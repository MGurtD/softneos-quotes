<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReferenceRequest;
use App\Http\Requests\UpdateProductReferenceRequest;
use App\Http\Resources\ProductReferenceResource;
use App\Models\ProductReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductReferenceController extends Controller
{
    /**
     * Paginated list. ?q= searches code and description; ?active=1 hides
     * discontinued items (used by the quote line picker).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $references = ProductReference::query()
            ->with('vatType')
            ->when($request->string('q')->trim()->value(), function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('code', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($request->boolean('active'), fn ($query) => $query->where('active', true))
            ->orderBy('code')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return ProductReferenceResource::collection($references);
    }

    public function store(StoreProductReferenceRequest $request): JsonResponse
    {
        $reference = ProductReference::create($request->validated());

        return (new ProductReferenceResource($reference->load('vatType')))->response()->setStatusCode(201);
    }

    public function show(ProductReference $productReference): ProductReferenceResource
    {
        return new ProductReferenceResource($productReference->load('vatType'));
    }

    public function update(UpdateProductReferenceRequest $request, ProductReference $productReference): ProductReferenceResource
    {
        $productReference->update($request->validated());

        return new ProductReferenceResource($productReference->load('vatType'));
    }
}
