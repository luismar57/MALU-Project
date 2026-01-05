<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,pro_id',
            'items.*.quantity' => 'required|integer|min:1',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'email' => 'required|email',
            'payment_method' => 'required|string|in:cod,card,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get authenticated user
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $items = $request->input('items');
        $calculatedSubtotal = 0;
        $productDetails = [];

        // Calculate subtotal based on database prices
        foreach ($items as $item) {
            $product = Product::select('pro_id', 'pro_name', 'price', 'discount', 'qty')
                ->where('pro_id', $item['id'])
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Product ID {$item['id']} not found",
                ], 404);
            }

            if ($product->qty < $item['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient stock for product {$product->pro_name}",
                ], 400);
            }

            $price = $product->discounted_price; // Use accessor
            $itemSubtotal = $price * $item['quantity'];
            $calculatedSubtotal += $itemSubtotal;

            $productDetails[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => round($price, 2),
            ];
        }

        // Calculate total using server-calculated subtotal
        $tax = $request->input('tax', 0);
        $shipping = $request->input('shipping', 0);
        $discount = $request->input('discount', 0);
        $calculatedTotal = $calculatedSubtotal + $tax + $shipping - $discount;

        try {
            $order = DB::transaction(function () use ($user, $request, $productDetails, $calculatedSubtotal, $tax, $shipping, $discount, $calculatedTotal) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => round($calculatedSubtotal, 2),
                    'tax' => round($tax, 2),
                    'shipping' => round($shipping, 2),
                    'discount' => round($discount, 2),
                    'total' => round($calculatedTotal, 2),
                    'promo_code' => $request->input('promo_code'),
                    'notes' => $request->input('notes'),
                    'status' => 'pending',
                    'email' => $request->input('email'),
                    'payment_method' => $request->input('payment_method'),
                ]);

                foreach ($productDetails as $detail) {
                    $product = $detail['product'];
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->pro_id,
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                        'discount' => $product->discount ?? 0,
                    ]);

                    $product->decrement('qty', $detail['quantity']);
                }

                return $order;
            });

            Mail::to($request->input('email'))->queue(new OrderConfirmation($order));

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'subtotal' => round($calculatedSubtotal, 2),
                    'total' => round($calculatedTotal, 2),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $orders = Order::with(['items.product'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'user_id' => $order->user_id,
                        'total' => (float) $order->total,
                        'subtotal' => (float) $order->subtotal,
                        'tax' => (float) $order->tax,
                        'shipping' => (float) $order->shipping,
                        'discount' => (float) $order->discount,
                        'status' => $order->status,
                        'payment_method' => $order->payment_method,
                        'email' => $order->email,
                        'promo_code' => $order->promo_code,
                        'notes' => $order->notes,
                        'created_at' => $order->created_at->toIso8601String(),
                        'updated_at' => $order->updated_at->toIso8601String(),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->pro_name ?? 'Product not found',
                                'quantity' => $item->quantity,
                                'price' => (float) $item->price,
                                'discount' => (float) $item->discount,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found or you do not have permission to cancel it',
                ], 404);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order is already cancelled',
                ], 400);
            }

            if ($order->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot cancel a completed order',
                ], 400);
            }

            DB::transaction(function () use ($order) {
                // Restore product quantities
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('qty', $item->quantity);
                    }
                }

                $order->update(['status' => 'cancelled']);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Order cancelled successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
            ], 500);
        }
    }
}