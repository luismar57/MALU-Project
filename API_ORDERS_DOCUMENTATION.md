# 📦 API de Órdenes - Documentación Completa

## 🔐 Autenticación

### 1. Login (Obtener Token)
```http
POST /api/login
Content-Type: application/json
Accept: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Respuesta (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Usuario",
    "email": "user@example.com",
    "token": "1|abc123xyz..."
  }
}
```

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid credentials"
}
```

### 2. Registro
```http
POST /api/register
Content-Type: application/json
Accept: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta (201):**
```json
{
  "status": "success",
  "message": "Registration successful",
  "data": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com",
    "token": "2|def456uvw..."
  }
}
```

**Error (422):**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### 3. Logout
```http
POST /api/logout
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Successfully logged out"
}
```

---

## 📋 Endpoints de Órdenes (Requieren Autenticación)

Todos los endpoints de órdenes requieren el header:
```
Authorization: Bearer {access_token}
Accept: application/json
```

### 1. Crear Nueva Orden

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json

{
  "items": [
    {"id": 1, "quantity": 2},
    {"id": 3, "quantity": 1}
  ],
  "subtotal": 25.50,
  "tax": 2.55,
  "shipping": 5.00,
  "discount": 0.00,
  "total": 33.05,
  "promo_code": "",
  "notes": "Por favor, entregar en la mañana",
  "email": "user@example.com",
  "user_id": 1,
  "payment_method": "cod"
}
```

**Campos Requeridos:**
- `items`: Array de productos (id y quantity)
- `subtotal`: Subtotal de productos
- `tax`: Impuestos
- `shipping`: Costo de envío
- `discount`: Descuento aplicado
- `total`: Total de la orden
- `email`: Email del cliente
- `user_id`: ID del usuario (debe coincidir con el usuario autenticado)
- `payment_method`: Método de pago (`cod`, `card`, `paypal`)

**Campos Opcionales:**
- `promo_code`: Código promocional
- `notes`: Notas adicionales

**Respuesta Exitosa (201):**
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "order_id": 123
  }
}
```

**Errores Posibles:**

**Validación fallida (422):**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "items": ["The items field is required."],
    "payment_method": ["The selected payment method is invalid."]
  }
}
```

**Stock insuficiente (400):**
```json
{
  "status": "error",
  "message": "Insufficient stock for product Product Name"
}
```

**Subtotal no coincide (400):**
```json
{
  "status": "error",
  "message": "Subtotal mismatch",
  "details": {
    "expected_subtotal": 30.00,
    "received_subtotal": 25.50,
    "items": [...]
  }
}
```

---

### 2. Obtener Órdenes del Usuario

```http
GET /api/orders
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "user_id": 1,
      "total": 33.05,
      "subtotal": 25.50,
      "tax": 2.55,
      "shipping": 5.00,
      "discount": 0.00,
      "status": "pending",
      "payment_method": "cod",
      "email": "user@example.com",
      "promo_code": null,
      "notes": "Por favor, entregar en la mañana",
      "created_at": "2025-11-26T10:30:00.000000Z",
      "updated_at": "2025-11-26T10:30:00.000000Z",
      "items": [
        {
          "id": 1,
          "product_id": 1,
          "product_name": "Product Name",
          "quantity": 2,
          "price": 12.75,
          "discount": 0.00
        }
      ]
    }
  ]
}
```

**Estados de Orden:**
- `pending`: Pendiente
- `processing`: En proceso
- `completed`: Completada
- `cancelled`: Cancelada

---

### 3. Cancelar Orden

```http
POST /api/orders/{id}/cancel
Authorization: Bearer {token}
Accept: application/json
```

**Ejemplo:**
```http
POST /api/orders/123/cancel
Authorization: Bearer 1|abc123xyz...
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "message": "Order cancelled successfully"
}
```

**Errores Posibles:**

**Orden no encontrada (404):**
```json
{
  "status": "error",
  "message": "Order not found or you do not have permission to cancel it"
}
```

**Orden ya cancelada (400):**
```json
{
  "status": "error",
  "message": "Order is already cancelled"
}
```

**Orden completada (400):**
```json
{
  "status": "error",
  "message": "Cannot cancel a completed order"
}
```

---

## 🌐 Configuración CORS

El backend está configurado para aceptar requests desde:
- `http://localhost:3000`
- `http://127.0.0.1:3000`

Si necesitas agregar más dominios, edita `config/cors.php`:
```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'https://tu-dominio.com'
],
```

---

## 🧪 Ejemplo de Uso con JavaScript/React

### Login y Guardar Token
```javascript
const login = async (email, password) => {
  const response = await fetch('http://127.0.0.1:8000/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (response.ok) {
    // Guardar token y datos del usuario
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('user', JSON.stringify({
      id: data.data.id,
      name: data.data.name,
      email: data.data.email
    }));
    return data.data;
  }
  throw new Error(data.message);
};
```

### Crear Orden
```javascript
const createOrder = async (orderData) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://127.0.0.1:8000/api/orders', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(orderData)
  });
  
  const data = await response.json();
  
  if (response.ok) {
    return data;
  }
  throw new Error(data.message);
};
```

### Obtener Órdenes
```javascript
const getOrders = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://127.0.0.1:8000/api/orders', {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (response.ok) {
    return data.data;
  }
  throw new Error(data.message);
};
```

### Cancelar Orden
```javascript
const cancelOrder = async (orderId) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}/cancel`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (response.ok) {
    return data;
  }
  throw new Error(data.message);
};
```

---

## 📊 Base de Datos

### Tabla `orders`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INTEGER | ID único de la orden |
| user_id | INTEGER | ID del usuario |
| subtotal | DECIMAL(10,2) | Subtotal de productos |
| tax | DECIMAL(10,2) | Impuestos |
| shipping | DECIMAL(10,2) | Costo de envío |
| discount | DECIMAL(10,2) | Descuento aplicado |
| total | DECIMAL(10,2) | Total de la orden |
| promo_code | VARCHAR | Código promocional |
| notes | TEXT | Notas adicionales |
| status | VARCHAR | Estado (pending, processing, completed, cancelled) |
| email | VARCHAR | Email del cliente |
| payment_method | VARCHAR | Método de pago (cod, card, paypal) |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Fecha de actualización |

### Tabla `order_items`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INTEGER | ID único del item |
| order_id | INTEGER | ID de la orden |
| product_id | INTEGER | ID del producto |
| quantity | INTEGER | Cantidad |
| price | DECIMAL(10,2) | Precio unitario |
| discount | DECIMAL(10,2) | Descuento del producto |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Fecha de actualización |

---

## ✅ Verificación

Para verificar que todo está funcionando:

1. **Verifica las rutas:**
```bash
php artisan route:list --path=api/orders
```

2. **Prueba el login:**
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"haksimpledev@gmail.com","password":"11111111"}'
```

3. **Prueba crear una orden (reemplaza {TOKEN}):**
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}" \
  -d '{
    "items": [{"id": 1, "quantity": 2}],
    "subtotal": 25.50,
    "tax": 2.55,
    "shipping": 5.00,
    "discount": 0.00,
    "total": 33.05,
    "promo_code": "",
    "notes": "",
    "email": "user@example.com",
    "user_id": 1,
    "payment_method": "cod"
  }'
```

---

## 🐛 Troubleshooting

### Error: "Unauthenticated"
- Verifica que estás enviando el header `Authorization: Bearer {token}`
- Verifica que el token no ha expirado
- Haz login nuevamente para obtener un token válido

### Error: "CORS"
- Verifica que `config/cors.php` incluye tu dominio frontend
- Verifica que estás enviando el header `Accept: application/json`

### Error: "Subtotal mismatch"
- El backend calcula el subtotal basándose en los precios actuales de los productos
- Asegúrate de obtener los precios actualizados antes de crear la orden

### Error: "Insufficient stock"
- El producto no tiene suficiente cantidad en stock
- Verifica la cantidad disponible antes de crear la orden
