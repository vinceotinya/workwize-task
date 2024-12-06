<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateDeliveryRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderCollection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    use ApiResponse;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product', 'items.product.supplier', 'creator'])
            ->latest()->bySupplier(auth()->user())
            ->paginate(config('app.pagination.per_page', 15));

        return $this->successResponse(
            __('Orders retrieved successfully'),
            new OrderCollection($orders)
        );
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = $this->orderService->createOrder(
                $request->validated(),
                auth()->id()
            );

            DB::commit();

            return $this->successResponse(
                __('Order created successfully'),
                new OrderResource($order),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function show(Order $order): JsonResponse
    {
            $order->load(['items.product', 'items.product.supplier', 'creator']);
            
            return $this->successResponse(
                __('Order details retrieved successfully'),
                new OrderResource($order)
            );
    }

    public function updateDelivery(UpdateDeliveryRequest $request, OrderItem $orderItem): JsonResponse
    {
        try {
            DB::beginTransaction();

            $orderItem = $this->orderService->updateDelivery(
                $orderItem,
                $request->validated()
            );

            DB::commit();

            return $this->successResponse(
                __('Delivery details updated successfully'),
                new OrderItemResource($orderItem)
            );

        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
