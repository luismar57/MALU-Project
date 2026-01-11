# 🎯 Guía Rápida - Gestión de Pedidos para Administradores

## 📌 Resumen de Endpoints

### **Para Clientes** (usuarios normales):
- `GET /api/orders` - Ver **sus propios** pedidos
- `POST /api/orders` - Crear un nuevo pedido
- `POST /api/orders/{id}/cancel` - Cancelar **su propio** pedido

### **Para Administradores**:
- `GET /api/admin/orders` - Ver **TODOS** los pedidos (con filtros y paginación)
- `GET /api/admin/orders/{id}` - Ver detalle completo de cualquier pedido
- `PUT /api/admin/orders/{id}/status` - Cambiar estado de cualquier pedido

---

## 🔐 Autenticación

Todos los endpoints requieren autenticación con Sanctum:

```http
Authorization: Bearer {tu_token_de_acceso}
```

**Obtener token:**
```bash
POST /api/login
{
  "email": "admin@example.com",
  "password": "tu_password"
}
```

---

## 📋 Ejemplos Prácticos

### 1. Ver todos los pedidos pendientes

```bash
GET http://127.0.0.1:8000/api/admin/orders?status=pending
Authorization: Bearer {token}
```

### 2. Buscar pedidos por email

```bash
GET http://127.0.0.1:8000/api/admin/orders?search=cliente@example.com
Authorization: Bearer {token}
```

### 3. Ver pedidos de un rango de fechas

```bash
GET http://127.0.0.1:8000/api/admin/orders?from_date=2025-01-01&to_date=2025-01-31
Authorization: Bearer {token}
```

### 4. Ver detalle completo de un pedido

```bash
GET http://127.0.0.1:8000/api/admin/orders/123
Authorization: Bearer {token}
```

### 5. Marcar un pedido como "En Proceso"

```bash
PUT http://127.0.0.1:8000/api/admin/orders/123/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "processing"
}
```

### 6. Completar un pedido

```bash
PUT http://127.0.0.1:8000/api/admin/orders/123/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "completed"
}
```

### 7. Cancelar un pedido (restaura inventario)

```bash
PUT http://127.0.0.1:8000/api/admin/orders/123/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "cancelled"
}
```

---

## 📊 Estados de Pedidos

| Estado | Descripción | ¿Afecta inventario? |
|--------|-------------|-------------------|
| `pending` | Pedido creado, esperando confirmación | ✅ Sí (reduce stock) |
| `processing` | Pedido en preparación/envío | ✅ Sí |
| `completed` | Pedido entregado | ✅ Sí |
| `cancelled` | Pedido cancelado | ❌ No (restaura stock) |

---

## 🎨 Ejemplo con JavaScript/React

### Hook personalizado para gestionar pedidos (Admin)

```javascript
import { useState, useEffect } from 'react';

export const useAdminOrders = () => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchOrders = async (filters = {}) => {
    setLoading(true);
    setError(null);
    
    try {
      const token = localStorage.getItem('token');
      const params = new URLSearchParams(filters);
      
      const response = await fetch(
        `http://127.0.0.1:8000/api/admin/orders?${params}`,
        {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        }
      );
      
      const data = await response.json();
      
      if (response.ok) {
        setOrders(data.data);
      } else {
        throw new Error(data.message);
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const updateOrderStatus = async (orderId, newStatus) => {
    try {
      const token = localStorage.getItem('token');
      
      const response = await fetch(
        `http://127.0.0.1:8000/api/admin/orders/${orderId}/status`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({ status: newStatus })
        }
      );
      
      const data = await response.json();
      
      if (response.ok) {
        // Refrescar la lista después de actualizar
        await fetchOrders();
        return data;
      } else {
        throw new Error(data.message);
      }
    } catch (err) {
      setError(err.message);
      throw err;
    }
  };

  return {
    orders,
    loading,
    error,
    fetchOrders,
    updateOrderStatus
  };
};
```

### Componente de ejemplo

```javascript
import React, { useEffect } from 'react';
import { useAdminOrders } from './hooks/useAdminOrders';

const AdminOrdersPanel = () => {
  const { orders, loading, error, fetchOrders, updateOrderStatus } = useAdminOrders();

  useEffect(() => {
    fetchOrders({ status: 'pending' });
  }, []);

  const handleStatusChange = async (orderId, newStatus) => {
    try {
      await updateOrderStatus(orderId, newStatus);
      alert('Estado actualizado correctamente');
    } catch (err) {
      alert('Error: ' + err.message);
    }
  };

  if (loading) return <div>Cargando...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h2>Gestión de Pedidos</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {orders.data?.map(order => (
            <tr key={order.id}>
              <td>#{order.id}</td>
              <td>{order.user.name}</td>
              <td>${order.total}</td>
              <td>{order.status}</td>
              <td>{new Date(order.created_at).toLocaleDateString()}</td>
              <td>
                <select 
                  value={order.status}
                  onChange={(e) => handleStatusChange(order.id, e.target.value)}
                >
                  <option value="pending">Pendiente</option>
                  <option value="processing">En Proceso</option>
                  <option value="completed">Completado</option>
                  <option value="cancelled">Cancelado</option>
                </select>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default AdminOrdersPanel;
```

---

## ⚡ Características Importantes

### ✅ Gestión Automática de Inventario
- Cuando se crea un pedido → **reduce** el stock de productos
- Cuando se cancela un pedido → **restaura** el stock de productos
- Si intentas reactivar un pedido cancelado, verifica que haya stock disponible

### 🔍 Filtros Disponibles
- **Por estado**: `?status=pending`
- **Por fecha**: `?from_date=2025-01-01&to_date=2025-01-31`
- **Por búsqueda**: `?search=email@example.com` (busca en email y ID)
- **Paginación**: `?page=2&per_page=20`

### 📦 Paginación
Los resultados están paginados (15 por defecto). La respuesta incluye:
- `current_page`: Página actual
- `last_page`: Última página
- `total`: Total de registros
- `per_page`: Registros por página
- `next_page_url`: URL de la siguiente página

---

## 🚀 Inicio Rápido

1. **Inicia el servidor:**
```bash
php artisan serve
```

2. **Haz login:**
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"tu@email.com","password":"tu_password"}'
```

3. **Obtén todos los pedidos:**
```bash
curl http://127.0.0.1:8000/api/admin/orders \
  -H "Authorization: Bearer TU_TOKEN"
```

4. **Cambia el estado de un pedido:**
```bash
curl -X PUT http://127.0.0.1:8000/api/admin/orders/123/status \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"completed"}'
```

---

## 📝 Notas Adicionales

- **Seguridad**: Actualmente, cualquier usuario autenticado puede acceder a los endpoints de admin. Considera agregar un middleware para verificar roles de administrador.
- **Email**: Cuando se crea un pedido, se envía automáticamente un email de confirmación al cliente.
- **Transacciones**: Todas las operaciones que afectan el inventario usan transacciones de base de datos para garantizar la integridad.

---

## 🔗 Recursos

- Documentación completa: [API_ORDERS_DOCUMENTATION.md](API_ORDERS_DOCUMENTATION.md)
- Repositorio: [MALU-Backend-Laravel](.)
