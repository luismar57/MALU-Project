# 🚀 MaluStore - E-commerce Backend Laravel

## 📋 Información General

- **Nombre del Proyecto**: MaluStore Backend
- **Tipo**: E-commerce API & Admin Panel
- **Tecnología**: Laravel 12.x + PHP 8.2
- **Base de Datos**: SQLite
- **Frontend**: Vite + TailwindCSS
- **Desarrolladores**: Luis Muñoz

---

## 🎯 Descripción del Proyecto

MaluStore Backend es un sistema completo de gestión de comercio electrónico desarrollado con Laravel, que incluye un panel de administración y una API REST para gestionar productos, categorías, pedidos y más.

### ✨ Características Principales

1. **🎨 Panel de Administración Moderno**
   - Dashboard con estadísticas en tiempo real
   - Diseño responsivo con TailwindCSS
   - Interfaz dark mode
   - Sistema de navegación lateral

2. **📦 Gestión Completa de Productos**
   - CRUD completo de productos
   - Sistema de subida de imágenes optimizado
   - Categorización avanzada
   - Filtros y búsqueda

3. **🏪 Sistema de Categorías**
   - Gestión de categorías con soft deletes
   - Jerarquía de categorías
   - Contadores de productos por categoría

4. **🎠 Sistema de Slides/Banners**
   - Carrusel de imágenes para homepage
   - Múltiples imágenes por slide
   - Estado activo/inactivo
   - Gestión desde admin panel

5. **👥 Gestión de Usuarios y Empleados**
   - Sistema de autenticación Laravel Sanctum
   - Roles y permisos
   - Gestión de empleados
   - Perfiles de usuario

6. **📊 Sistema de Pedidos**
   - Gestión completa de órdenes
   - Items de pedido detallados
   - Estados de pedido
   - Emails de confirmación

---

## 🏗️ Arquitectura Técnica

### **Backend Stack**
- **Laravel 12.x**: Framework principal PHP
- **PHP 8.2.12**: Runtime via XAMPP
- **SQLite**: Base de datos ligera
- **Sanctum**: Autenticación API
- **Vite**: Build tool para assets
- **TailwindCSS**: Framework de estilos

### **Estructura del Proyecto**
```
MaluStore-Backend/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/              # Modelos Eloquent
│   ├── Middleware/          # Middleware personalizado
│   ├── Policies/            # Políticas de autorización
│   └── Mail/               # Clases de email
├── database/
│   ├── migrations/         # Migraciones de BD
│   ├── seeders/           # Seeders de datos
│   └── factories/         # Factories para testing
├── resources/
│   ├── views/             # Vistas Blade
│   ├── js/               # JavaScript/Vue
│   └── css/              # Estilos CSS
├── routes/
│   ├── web.php           # Rutas web
│   ├── api.php           # Rutas API REST
│   └── console.php       # Comandos Artisan
└── storage/
    └── app/public/       # Archivos públicos
```

### **Modelos Principales**
- **Product**: Gestión de productos con imágenes
- **Category**: Categorías con soft deletes
- **Order & OrderItem**: Sistema completo de pedidos
- **SlideHero**: Banners para homepage
- **Employee**: Gestión de personal
- **User**: Usuarios del sistema

---

## 🌟 Características Técnicas Destacadas

### **1. Sistema de Imágenes Optimizado**
```php
// ProductController - Manejo de imágenes
public function store(Request $request)
{
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
        $product->image = '/storage/' . $imagePath;
    }
}
```

### **2. API REST Completa**
```php
// Rutas API para frontend
Route::prefix('api')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('slide-heroes', SlideHeroController::class);
});
```

### **3. Middleware de Debugging**
```php
// DebugImageUpload - Middleware personalizado
class DebugImageUpload
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('POST') && $request->hasFile('image')) {
            Log::info('Image upload attempt', [
                'file_info' => $request->file('image')->getClientOriginalName()
            ]);
        }
        return $next($request);
    }
}
```

### **4. Seeders con Datos Reales**
```php
// CategorySeeder
public function run()
{
    Category::create(['name' => 'Electrónicos', 'slug' => 'electronicos']);
    Category::create(['name' => 'Ropa', 'slug' => 'ropa']);
    Category::create(['name' => 'Hogar', 'slug' => 'hogar']);
}
```

---

## 📊 Funcionalidades API REST

### ✅ Endpoints Disponibles

**Productos**
- `GET /api/products` - Listar productos
- `GET /api/products/{id}` - Ver producto específico
- `PUT /api/products/{id}` - Actualizar producto
- `DELETE /api/products/{id}` - Eliminar producto

**Categorías**
- `GET /api/categories` - Listar categorías
- `GET /api/categories/{id}` - Ver categoría específica
- `PUT /api/categories/{id}` - Actualizar categoría
- `DELETE /api/categories/{id}` - Eliminar categoría

**Slides/Banners**
- `GET /api/slide-heroes` - Listar slides
- `POST /api/slide-heroes` - Crear slide
- `PUT /api/slide-heroes/{id}` - Actualizar slide
- `DELETE /api/slide-heroes/{id}` - Eliminar slide
- `GET /api/slide-heroes/active/list` - Slides activos

**Autenticación**
- `POST /api/add-user` - Registrar usuario
- `GET /api/users` - Listar usuarios
- `PUT /api/update-user/{id}` - Actualizar usuario
- `DELETE /api/delete-user/{id}` - Eliminar usuario

---

## 🚀 Instalación y Configuración

### **Requisitos del Sistema**
- PHP 8.2+
- Composer
- Node.js 18+
- XAMPP (opcional)

### **Pasos de Instalación**


1. **Instalar Dependencias PHP**
   ```bash
   composer install
   ```

2. **Configurar Entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configurar Base de Datos**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Crear Enlace de Storage**
   ```bash
   php artisan storage:link
   ```

5. **Instalar Dependencias Node**
   ```bash
   npm install
   ```

### **Ejecutar el Proyecto**

1. **Iniciar Laravel**
   ```bash
   php artisan serve
   # Servidor: http://127.0.0.1:8000
   ```

2. **Iniciar Vite (en terminal separado)**
   ```bash
   npm run dev
   # Assets: http://localhost:5174 (puerto puede variar)
   ```

3. **Acceder al Admin Panel**
   ```
   URL: http://127.0.0.1:8000/login
   Email: haksimpledev@gmail.com
   Password: 11111111
   ```

### **📋 Estado Actual del Proyecto**

**✅ Sistema Backend Funcionando:**
- Laravel Server: `http://127.0.0.1:8000`
- API REST: 25+ endpoints disponibles
- Base de datos: SQLite con datos de prueba
- Sistema de imágenes: Configurado y operativo

**✅ Sistema Frontend Funcionando:**
- Vite Dev Server: `http://localhost:5174`
- React App: Conectando correctamente con la API
- Productos: 4+ productos siendo mostrados correctamente
- Filtros: Sistema de categorías operativo
- Slides: Sistema de banners/slides activo y funcionando

**✅ Sistema de Slides/Banners Actualizado:**
- Campo `is_active` agregado para control de visibilidad
- Slides activos disponibles en `/api/slide-heroes/active/list`
- Imágenes múltiples por slide (image1, image2, image3)
- Almacenamiento en `/storage/slides/` funcionando correctamente

**⚠️ Nota sobre errores de conectividad:**
Los errores `net::ERR_NAME_NOT_RESOLVED` son problemas de conectividad a servicios externos (como placeholder.com) y no afectan la funcionalidad principal de MaluStore.

---

## 🛡️ Configuración de Seguridad

### **Variables de Entorno (.env)**
```env
APP_NAME=MaluStore
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
FILESYSTEM_DISK=public
MAIL_FROM_ADDRESS="hello@malustore.com"
```

### **Características de Seguridad**
- Autenticación Laravel Sanctum
- Validación de formularios
- Políticas de autorización
- Protección CSRF
- Rate limiting en API

---

## 📈 Rutas del Sistema

### **Rutas Web (Admin Panel)**
- `/login` - Autenticación
- `/dashboard` - Panel principal
- `/products` - Gestión de productos
- `/categories` - Gestión de categorías
- `/slide-heroes` - Gestión de slides
- `/employees` - Gestión de empleados
- `/orders` - Gestión de pedidos

### **Rutas API (Para Frontend)**
- `/api/products` - API de productos
- `/api/categories` - API de categorías
- `/api/slide-heroes` - API de slides
- `/api/users` - API de usuarios

---

## 🎨 Características del Admin Panel

### **Dashboard**
- Estadísticas de productos, categorías, pedidos
- Gráficos de ventas
- Actividad reciente
- Accesos rápidos

### **Gestión de Productos**
- Formulario con validación completa
- Subida de imágenes con preview
- Asignación de categorías
- Estados activo/inactivo

### **Sistema de Slides**
- Múltiples imágenes por slide
- Editor de título y descripción
- Vista previa en tiempo real
- Control de estado activo

---

## 🔧 Herramientas de Desarrollo

### **Debugging y Logging**
```php
// Middleware de debug personalizado
Log::info('Product created', ['product_id' => $product->id]);
```

### **Comandos Artisan Útiles**
```bash
php artisan route:list          # Ver todas las rutas
php artisan migrate:fresh --seed # Resetear BD con datos
php artisan config:clear        # Limpiar caché config
php artisan storage:link        # Enlazar storage público
```

---

## 🚀 Futuras Mejoras

### **Backend**
- [ ] Sistema de roles avanzado
- [ ] API de búsqueda con Elasticsearch
- [ ] Sistema de notificaciones
- [ ] Integración con pagos (Stripe/PayPal)
- [ ] Sistema de inventario
- [ ] Analytics avanzados

### **Frontend**
- [ ] Dashboard con React/Vue
- [ ] PWA capabilities
- [ ] Real-time notifications
- [ ] Multi-idioma

---

## 📊 Métricas del Proyecto

- **Líneas de Código Backend**: ~8,000+
- **Modelos Eloquent**: 12+
- **Controladores**: 15+
- **Migraciones**: 15+
- **Rutas Totales**: 89
- **API Endpoints**: 25+

---

## 🏆 Características Destacadas

- ✅ API REST completa y documentada
- ✅ Panel de administración funcional
- ✅ Sistema de imágenes optimizado
- ✅ Base de datos bien estructurada
- ✅ Middleware de debugging personalizado
- ✅ Seeders con datos reales
- ✅ Autenticación robusta
- ✅ Diseño responsive moderno

---

## 📞 Información de Contacto

**Ubicación:**
- Chone, Ecuador

**Horario de Apertura:**
- Lunes - Viernes: 24/7
- Fin de semana: 10:00 - 02:00

**Contacto:**
- Teléfono: +593 986 660 488
- Correo electrónico: luisnovak57@gmail.com

**Credenciales de Admin:**
- Email: haksimpledev@gmail.com
- Password: 11111111

**URLs del Proyecto:**
- Admin Panel: http://127.0.0.1:8000/login
- API Base: http://127.0.0.1:8000/api



