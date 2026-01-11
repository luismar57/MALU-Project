@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-100">Detalle del Pedido #{{ $order->id }}</h1>
            <p class="text-gray-400 mt-1">Información completa del pedido</p>
        </div>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Products -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4 flex items-center">
                    <i class="fas fa-box text-cyan-400 mr-2"></i>
                    Productos del Pedido
                </h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded-lg border border-gray-700/30">
                        <div class="flex-1">
                            <h3 class="text-white font-medium">{{ $item->product->pro_name ?? 'Producto Eliminado' }}</h3>
                            <p class="text-sm text-gray-400">Código: {{ $item->product->pro_code ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-400">Cantidad: {{ $item->quantity }}</p>
                            @if($item->discount > 0)
                            <span class="text-xs text-yellow-400">Descuento: {{ $item->discount }}%</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-emerald-400">${{ number_format($item->price, 2) }}</p>
                            <p class="text-sm text-gray-400">Subtotal: ${{ number_format($item->price * $item->quantity, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Notes -->
            @if($order->notes)
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-yellow-400 mr-2"></i>
                    Notas del Pedido
                </h2>
                <p class="text-gray-300">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4">Resumen</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal:</span>
                        <span class="text-gray-200 font-medium">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    
                    @if($order->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Impuestos:</span>
                        <span class="text-gray-200 font-medium">${{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif
                    
                    @if($order->shipping > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Envío:</span>
                        <span class="text-gray-200 font-medium">${{ number_format($order->shipping, 2) }}</span>
                    </div>
                    @endif
                    
                    @if($order->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Descuento:</span>
                        <span class="text-red-400 font-medium">-${{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="border-t border-gray-700 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-gray-200">Total:</span>
                            <span class="text-2xl font-bold text-emerald-400">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4 flex items-center">
                    <i class="fas fa-user text-cyan-400 mr-2"></i>
                    Cliente
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Nombre:</p>
                        <p class="text-gray-200 font-medium">{{ $order->user->name ?? 'Guest' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Email:</p>
                        <p class="text-gray-200">{{ $order->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4 flex items-center">
                    <i class="fas fa-credit-card text-purple-400 mr-2"></i>
                    Pago
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Método:</p>
                        @php
                            $paymentClasses = match($order->payment_method) {
                                'cod' => 'bg-purple-500/10 text-purple-400 border border-purple-500/30',
                                'card' => 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30',
                                default => 'bg-blue-500/10 text-blue-400 border border-blue-500/30'
                            };
                        @endphp
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-medium rounded-full {{ $paymentClasses }}">
                            {{ strtoupper($order->payment_method) }}
                        </span>
                    </div>
                    @if($order->promo_code)
                    <div>
                        <p class="text-sm text-gray-400">Código Promocional:</p>
                        <p class="text-gray-200 font-mono">{{ $order->promo_code }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Status -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6">
                <h2 class="text-xl font-semibold text-gray-200 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                    Estado
                </h2>
                <form method="POST" action="{{ route('orders.updateStatus', $order) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent mb-3">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>En Proceso</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    <button type="submit" class="w-full px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Actualizar Estado
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-700">
                    <p class="text-sm text-gray-400">Creado:</p>
                    <p class="text-gray-200">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    
                    <p class="text-sm text-gray-400 mt-2">Actualizado:</p>
                    <p class="text-gray-200">{{ $order->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    alert('{{ session('success') }}');
</script>
@endif
@endsection
