@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-100">Gestión de Pedidos</h1>
            <p class="text-gray-400 mt-1">Administra todos los pedidos de clientes</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 p-6 mb-6">
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En Proceso</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completado</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email o ID de orden" class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900/70">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Método Pago</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-cyan-400">#{{ $order->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-300">{{ $order->user->name ?? 'Guest' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-300">{{ $order->email }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-emerald-400">${{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClasses = match($order->status) {
                                    'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                    'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                    'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    default => 'bg-red-500/10 text-red-400 border-red-500/30'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium border inline-flex items-center {{ $statusClasses }}">
                                @if($order->status == 'pending')
                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                @elseif($order->status == 'processing')
                                    <i class="fas fa-spinner mr-1"></i> En Proceso
                                @elseif($order->status == 'completed')
                                    <i class="fas fa-check-circle mr-1"></i> Completado
                                @else
                                    <i class="fas fa-times-circle mr-1"></i> Cancelado
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $paymentClasses = match($order->payment_method) {
                                    'cod' => 'bg-purple-500/10 text-purple-400 border border-purple-500/30',
                                    'card' => 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30',
                                    default => 'bg-blue-500/10 text-blue-400 border border-blue-500/30'
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentClasses }}">
                                {{ strtoupper($order->payment_method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('orders.show', $order) }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                <button onclick="openStatusModal({{ $order->id }}, '{{ $order->status }}')" class="text-purple-400 hover:text-purple-300 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Estado
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-shopping-cart text-6xl text-gray-600 mb-4"></i>
                                <p class="text-gray-400 text-lg">No hay pedidos disponibles</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-700/50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
        <div class="bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 border border-yellow-500/30 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-400 text-sm font-medium">Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-300 mt-1">{{ \App\Models\Order::where('status', 'pending')->count() }}</p>
                </div>
                <i class="fas fa-clock text-3xl text-yellow-400/50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/30 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-400 text-sm font-medium">En Proceso</p>
                    <p class="text-3xl font-bold text-blue-300 mt-1">{{ \App\Models\Order::where('status', 'processing')->count() }}</p>
                </div>
                <i class="fas fa-spinner text-3xl text-blue-400/50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 border border-emerald-500/30 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-400 text-sm font-medium">Completados</p>
                    <p class="text-3xl font-bold text-emerald-300 mt-1">{{ \App\Models\Order::where('status', 'completed')->count() }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl text-emerald-400/50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500/10 to-red-600/10 border border-red-500/30 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-400 text-sm font-medium">Cancelados</p>
                    <p class="text-3xl font-bold text-red-300 mt-1">{{ \App\Models\Order::where('status', 'cancelled')->count() }}</p>
                </div>
                <i class="fas fa-times-circle text-3xl text-red-400/50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div id="statusModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-2xl border border-gray-700/50 p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-gray-100 mb-4">Cambiar Estado del Pedido</h3>
        <form id="statusForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nuevo Estado</label>
                <select name="status" id="statusSelect" class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-gray-100 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <option value="pending">⏳ Pendiente</option>
                    <option value="processing">🔄 En Proceso</option>
                    <option value="completed">✅ Completado</option>
                    <option value="cancelled">❌ Cancelado</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeStatusModal()" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-medium transition-colors">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(orderId, currentStatus) {
    const modal = document.getElementById('statusModal');
    const form = document.getElementById('statusForm');
    const select = document.getElementById('statusSelect');
    
    form.action = `/orders/${orderId}/status`;
    select.value = currentStatus;
    modal.classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});
</script>

@if(session('success'))
<script>
    alert('{{ session('success') }}');
</script>
@endif
@endsection
