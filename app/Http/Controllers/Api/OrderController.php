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
                'message' => 'No autorizado',
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
                    'message' => "Producto ID {$item['id']} no encontrado",
                ], 404);
            }

            if ($product->qty < $item['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stock insuficiente para el producto {$product->pro_name}",
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
                'message' => 'Pedido creado exitosamente',
                'data' => [
                    'order_id' => $order->id,
                    'subtotal' => round($calculatedSubtotal, 2),
                    'total' => round($calculatedTotal, 2),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el pedido: ' . $e->getMessage(),
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
                    'message' => 'No autorizado',
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
                                'product_name' => $item->product->pro_name ?? 'Producto no encontrado',
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
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage(),
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
                    'message' => 'No autorizado',
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pedido no encontrado o no tiene permisos para cancelarlo',
                ], 404);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El pedido ya está cancelado',
                ], 400);
            }

            if ($order->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede cancelar un pedido completado',
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
                'message' => 'Pedido cancelado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cancelar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Admin Methods
    public function adminIndex(Request $request)
    {
        try {
            $query = Order::with(['items.product', 'user']);

            // Filter by status if provided
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Search by email or order ID
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15))
                ->through(function ($order) {
                    return [
                        'id' => $order->id,
                        'user' => [
                            'id' => $order->user->id ?? null,
                            'name' => $order->user->name ?? 'Invitado',
                            'email' => $order->user->email ?? $order->email,
                        ],
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
                        'items_count' => $order->items->count(),
                        'created_at' => $order->created_at->toIso8601String(),
                        'updated_at' => $order->updated_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los pedidos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function adminShow($id)
    {
        try {
            $order = Order::with(['items.product', 'user'])->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            $orderData = [
                'id' => $order->id,
                'user' => [
                    'id' => $order->user->id ?? null,
                    'name' => $order->user->name ?? 'Invitado',
                    'email' => $order->user->email ?? $order->email,
                ],
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
                        'product_name' => $item->product->pro_name ?? 'Producto no encontrado',
                        'product_code' => $item->product->pro_code ?? null,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'discount' => (float) $item->discount,
                        'subtotal' => (float) ($item->price * $item->quantity),
                    ];
                }),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $orderData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validación fallida',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::with('items')->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            $newStatus = $request->input('status');
            $oldStatus = $order->status;

            // If changing to cancelled, restore product quantities
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                DB::transaction(function () use ($order) {
                    foreach ($order->items as $item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('qty', $item->quantity);
                        }
                    }
                });
            }

            // If changing from cancelled to another status, reduce product quantities again
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                DB::transaction(function () use ($order) {
                    foreach ($order->items as $item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            if ($product->qty < $item->quantity) {
                                throw new \Exception("Stock insuficiente para el producto {$product->pro_name}");
                            }
                            $product->decrement('qty', $item->quantity);
                        }
                    }
                });
            }

            $order->update(['status' => $newStatus]);

            return response()->json([
                'status' => 'success',
                'message' => 'Estado del pedido actualizado exitosamente',
                'data' => [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el estado del pedido: ' . $e->getMessage(),
            ], 500);
        }
    }
}