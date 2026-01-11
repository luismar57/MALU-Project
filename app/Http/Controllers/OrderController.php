<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // Web Admin Methods
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'user'])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by email or order ID
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $orders = $query->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user']);
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

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

        // If changing from cancelled to another status, reduce product quantities
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

        return redirect()->back()->with('success', 'Estado del pedido actualizado exitosamente');
    }

    // Legacy Cart/Order Methods
    public function store(Request $request)
    {
        $this->authorize('create', Order::class);
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product' => function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->whereNull('deleted_at');
                });
            }])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route(' seating arrangementart.index')->with('error', 'El carrito está vacío');
        }

        $validCartItems = $cartItems->filter(function ($item) {
            return $item->product !== null && $item->quantity > 0;
        });

        if ($validCartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'No hay productos válidos en el carrito');
        }

        foreach ($validCartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return redirect()->route('cart.index')->with('error', "Stock insuficiente para {$item->product->name}");
            }
        }

        $total = $validCartItems->sum(function ($item) {
            return $item->product->discounted_price * $item->quantity;
        });

        try {
            $order = DB::transaction(function () use ($user, $validCartItems, $total) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => $total,
                    'status' => 'pending',
                ]);

                foreach ($validCartItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->discounted_price,
                    ]);
                    $item->product->decrement('stock', $item->quantity);
                }

                Cart::where('user_id', $user->id)->delete();

                return $order;
            });

            return redirect()->route('cart.show', $order)->with('success', 'Pedido realizado exitosamente');
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Error al realizar el pedido. Por favor intente nuevamente.');
        }
    }

    public function cartShow(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['orderItems.product']);
        return view('cart.show', compact('order'));
    }

    public function create()
    {
        $this->authorize('create', Order::class);
        $products = Product::whereHas('category', function ($q) {
            $q->whereNull('deleted_at');
        })->get();
        $cartItems = Auth::user()->cart()->with(['product' => function ($query) {
            $query->whereHas('category', function ($q) {
                $q->whereNull('deleted_at');
            });
        }])->get()->filter(function ($item) {
            return $item->product !== null;
        });
        return view('cart.create', compact('products', 'cartItems'));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        $products = Product::whereHas('category', function ($q) {
            $q->whereNull('deleted_at');
        })->get();
        $order->load('orderItems');
        return view('cart.edit', compact('order', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,pro_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request, $order) {
                $total = 0;
                $itemsToKeep = [];

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$product->name}");
                    }
                    $total += $product->discounted_price * $item['quantity'];
                    $itemsToKeep[] = $item['product_id'];
                }

                $order->update([
                    'total' => $total,
                    'status' => $request->status,
                ]);

                $order->orderItems()->whereNotIn('product_id', $itemsToKeep)->delete();

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $order->orderItems()->updateOrCreate(
                        ['product_id' => $item['product_id']],
                        [
                            'quantity' => $item['quantity'],
                            'price' => $product->discounted_price,
                        ]
                    );
                }
            });

            return redirect()->route('cart.index')->with('success', 'Pedido actualizado exitosamente');
        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return redirect()->route('cart.edit', $order)->with('error', $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $order->delete();
        return redirect()->route('cart.index')->with('success', 'Pedido eliminado exitosamente');
    }
}