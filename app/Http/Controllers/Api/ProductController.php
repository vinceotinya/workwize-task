<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $products = Product::with('supplier')->latest()
            ->paginate(config('app.pagination.per_page', 15));

        return $this->successResponse(
            __('Products retrieved successfully'),
            new ProductCollection($products)
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = Product::create($request->validated());

            DB::commit();

            return $this->successResponse(
                __('Product created successfully'),
                new ProductResource($product),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                __('Failed to create product'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(
            __('Product retrieved successfully'),
            new ProductResource($product)
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product->update($request->validated());

            DB::commit();

            return $this->successResponse(
                __('Product updated successfully'),
                new ProductResource($product)
            );
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function destroy(DeleteProductRequest $request, Product $product): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product->delete();

            DB::commit();

            return $this->successResponse(
                __('Product deleted successfully')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
