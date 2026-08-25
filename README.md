# Don Diego 🍰

Sistema web para **Don Diego Panadería**, desarrollado como proyecto académico.

El objetivo del proyecto es desarrollar una plataforma web que permita a los clientes consultar productos, registrarse, verificar su correo electrónico, gestionar un carrito y realizar pedidos. También cuenta con un área administrativa para la gestión de productos y pedidos.

El proyecto utiliza una arquitectura basada en el patrón **MVC (Model-View-Controller)**.

---

## 🚀 Funcionalidades

### 🏠 Inicio

* Página principal.
* Productos destacados.
* Productos recomendados aleatoriamente.
* Información sobre Don Diego.
* Diseño adaptable.

### 👤 Usuarios

* Registro de usuarios.
* Inicio de sesión.
* Cierre de sesión.
* Gestión de datos de la cuenta.
* Contraseñas protegidas mediante `password_hash()`.
* Verificación mediante `password_verify()`.
* Sistema de roles:

  * `cliente`
  * `empleado`
  * `admin`

### 📧 Verificación de correo

* Verificación de correo electrónico después del registro.
* Tokens de verificación seguros.
* Tokens con vencimiento de 24 horas.
* Página de aviso de verificación.
* Reenvío del correo de verificación.
* Límite de reenvíos para evitar abusos.
* Las cuentas sin verificar no pueden iniciar sesión.
* Integración con la API de **Brevo** para el envío de correos.

### 🧁 Productos

* Catálogo de productos.
* Visualización de productos.
* Información de cada producto.
* Productos activos.
* Gestión de productos desde el panel administrativo.

### 🛒 Carrito

* Agregar productos.
* Modificar cantidades.
* Vaciar carrito.
* Cálculo automático del total.
* Persistencia del carrito mediante sesión.

### 📦 Pedidos

* Creación de pedidos.
* Consulta de pedidos realizados.
* Visualización del detalle de un pedido.
* Cancelación de pedidos.
* Selección de fecha de recepción.
* Selección de franja horaria.
* Dirección de entrega.
* Selección del método de pago.
* Límite de pedidos pendientes por usuario.

Estados disponibles:

```text
pendiente
confirmado
en_preparacion
listo
entregado
cancelado
```

### 🛠️ Administración

Los usuarios con rol `admin` o `empleado` pueden acceder a funcionalidades administrativas.

* Panel de pedidos.
* Visualización de pedidos.
* Visualización del detalle de pedidos.
* Actualización del estado de los pedidos.
* Gestión de productos.
* Creación y edición de productos.
* Control de permisos según rol.

---

# 🔐 Seguridad

El proyecto incorpora diferentes medidas de seguridad:

* Consultas preparadas mediante `mysqli`.
* Protección contra SQL Injection mediante prepared statements.
* Contraseñas almacenadas mediante `password_hash()`.
* Verificación mediante `password_verify()`.
* Protección CSRF.
* Regeneración del ID de sesión después del inicio de sesión.
* Control de intentos de login.
* Control de acceso mediante roles.
* Verificación obligatoria del correo electrónico.
* Tokens de verificación generados mediante `random_bytes()`.
* Expiración de tokens.
* Control de reenvíos de verificación.
* Validación de datos recibidos desde formularios.
* Escape de datos mostrados mediante `htmlspecialchars()`.

---

# 📧 Brevo

El proyecto utiliza la API de **Brevo** para enviar correos electrónicos.

Actualmente se utiliza para:

* Verificación de cuentas.
* Reenvío de enlaces de verificación.

Por seguridad, las credenciales reales de Brevo no deben subirse a GitHub.

El repositorio incluye:

```text
config/
├── Brevo.php
└── Brevo.ejemplo.php
```

`Brevo.ejemplo.php` sirve como plantilla para configurar el servicio.

El archivo `Brevo.php` debe permanecer fuera del repositorio si contiene credenciales reales.

---

# 🛠️ Tecnologías

* HTML5
* CSS3
* JavaScript
* PHP 8.2+
* MySQL
* Apache
* XAMPP
* cURL
* Brevo API
* Git
* GitHub
* Font Awesome

---

# 📁 Estructura del proyecto

```text
DonDiego-Panaderia-Pruebas/
│
├── README.md
│
├── config/
│   ├── Brevo.ejemplo.php
│   ├── Brevo.php
│   ├── Csrf.php
│   └── Database.php
│
├── controllers/
│   ├── BrevoController.php
│   ├── CarritoController.php
│   ├── CatalogoController.php
│   ├── HomeController.php
│   ├── PedidoController.php
│   ├── ProductoController.php
│   ├── UsuarioController.php
│   └── logout.php
│
├── database/
│   └── don_diego.sql
│
├── models/
│   ├── Carrito.php
│   ├── Pedido.php
│   ├── Producto.php
│   └── Usuario.php
│
├── public/
│   ├── css/
│   ├── img/
│   └── js/
│
└── views/
    ├── admin/
    ├── carrito/
    ├── home/
    ├── layouts/
    ├── pedidos/
    ├── productos/
    └── usuarios/
```

La aplicación separa sus responsabilidades de la siguiente manera:

### `controllers/`

Contiene la lógica que procesa las solicitudes y conecta los modelos con las vistas.

### `models/`

Contiene las operaciones relacionadas con la base de datos y la gestión de los datos.

### `views/`

Contiene las interfaces que ve el usuario.

### `config/`

Contiene configuraciones y servicios utilizados por el sistema.

### `public/`

Contiene los archivos públicos del proyecto:

* CSS
* JavaScript
* Imágenes

### `database/`

Contiene el archivo SQL utilizado para crear y configurar la base de datos.

---

# 💻 Cómo levantar el proyecto

## 1. Requisitos

Se necesita tener instalado:

* XAMPP
* PHP 8.2 o superior
* Apache
* MySQL
* Git

También se necesita tener habilitada la extensión **cURL de PHP** para utilizar Brevo.

---

## 2. Clonar el repositorio

Abrir una terminal en la carpeta `htdocs` de XAMPP:

```bash
git clone URL_DEL_REPOSITORIO
```

Entrar al proyecto:

```bash
cd DonDiego-Panaderia-Pruebas
```

---

## 3. Iniciar XAMPP

Abrir XAMPP y encender:

```text
Apache
MySQL
```

Ambos servicios deben aparecer como activos.

---

# 🗄️ Configurar MySQL

## 4. Crear la base de datos

Abrir:

```text
http://localhost/phpmyadmin
```

Crear una base de datos llamada:

```text
don_diego
```

Luego importar:

```text
database/don_diego.sql
```

Esto creará las tablas necesarias para el funcionamiento del sistema.

---

# ⚙️ Configurar Database.php

Abrir:

```text
config/Database.php
```

Configurar los datos de conexión correspondientes al entorno local.

Una configuración típica de XAMPP puede ser:

```php
$host = 'localhost';
$usuario = 'root';
$password = '';
$baseDatos = 'don_diego';
```

Los valores pueden variar dependiendo de la configuración de cada integrante.

> Si `Database.php` contiene credenciales privadas, no deben subirse al repositorio. En ese caso se recomienda utilizar un archivo `Database.ejemplo.php` como plantilla.

---

# 📧 Configurar Brevo

El repositorio incluye:

```text
config/Brevo.ejemplo.php
```

Copiar el archivo como:

```text
config/Brevo.php
```

Luego completar la API Key y el correo remitente correspondiente.

Ejemplo:

```php
define(
    'BREVO_API_KEY',
    'TU_API_KEY_DE_BREVO'
);

define(
    'BREVO_SENDER_EMAIL',
    'tu-correo@ejemplo.com'
);

define(
    'BREVO_SENDER_NAME',
    'Don Diego'
);
```

### ⚠️ Importante

**Nunca subir la API Key real a GitHub.**

El archivo:

```text
Brevo.ejemplo.php
```

sí debe estar en el repositorio.

El archivo:

```text
Brevo.php
```

debe estar incluido en `.gitignore` si contiene credenciales reales.

---

# 🌐 Ejecutar el proyecto

Con Apache y MySQL funcionando, acceder desde:

```text
http://localhost/DonDiego-Panaderia-Pruebas/
```

Dependiendo de la configuración del proyecto, las páginas también pueden utilizar los controladores correspondientes.

---

# 📱 Acceso desde otros dispositivos

Durante el desarrollo local, las URLs utilizan `localhost`.

Por ejemplo:

```text
http://localhost/DonDiego-Panaderia-Pruebas/
```

`localhost` hace referencia al propio dispositivo desde el que se accede.

Por lo tanto, un teléfono u otra computadora no puede utilizar directamente esa URL para acceder al servidor de tu PC.

Para que el sistema pueda ser utilizado públicamente será necesario desplegarlo en un servidor accesible desde Internet y utilizar una URL o dominio público.

---

# 🧪 Cuentas de prueba

Para facilitar las pruebas del sistema, la base de datos de desarrollo incluye cuentas de prueba para cada rol.

| Rol           | Correo               | Contraseña    |
| ------------- | -------------------- | ------------- |
| Administrador | `admin@gmail.com`    | `Admin123`    |
| Empleado      | `empleado@gmail.com` | `empleado123` |
| Cliente       | `cliente@gmail.com`  | `Cliente123`  |

Estas cuentas son **únicamente para el entorno de desarrollo y pruebas académicas**.

Las credenciales no deben utilizarse en producción ni asociarse a información personal o datos reales.

### Permisos de cada rol

**Administrador**

* Acceso al panel administrativo.
* Gestión de productos.
* Visualización y gestión de pedidos.
* Actualización de estados de pedidos.

**Empleado**

* Acceso al panel de pedidos.
* Visualización de pedidos.
* Visualización del detalle.
* Actualización de estados de pedidos.
* No posee las mismas funciones de administración que el rol `admin`.

**Cliente**

* Registro e inicio de sesión.
* Consulta del catálogo.
* Gestión del carrito.
* Creación de pedidos.
* Consulta de sus propios pedidos.
* Cancelación de pedidos cuando corresponda.
* Gestión de sus datos de cuenta.


# 🌿 Flujo de trabajo con Git

Las ramas principales del proyecto son:

```text
main
  ↑
develop
  ↑
├── facu
├── mati
├── cony
└── cano
```

### `main`

Contiene la versión estable del proyecto.

### `develop`

Rama utilizada para integrar los cambios realizados por el equipo.

### Ramas individuales

Cada integrante trabaja principalmente en su propia rama:

```text
facu
mati
cony
cano
```

El flujo de trabajo recomendado es:

```text
Rama individual
      ↓
   Commit
      ↓
   Push
      ↓
Pull Request
      ↓
   develop
      ↓
   Pruebas
      ↓
    main
```

Los cambios importantes deben integrarse mediante Pull Requests para facilitar la revisión y evitar conflictos.

---

# 👥 Equipo

* Facu
* Cano
* Cony
* Mati

---

# 📌 Estado actual

### Implementado

* Página de inicio.
* Catálogo de productos.
* Productos destacados.
* Productos recomendados aleatorios.
* Registro.
* Login.
* Logout.
* Gestión de cuenta.
* Verificación de correo.
* Reenvío de verificación.
* Expiración de tokens.
* Carrito.
* Creación de pedidos.
* Consulta de pedidos.
* Detalle de pedidos.
* Cancelación de pedidos.
* Estados de pedidos.
* Panel administrativo.
* Gestión de productos.
* Roles y permisos.
* Protección CSRF.
* Validaciones de formularios.
* Integración con Brevo.

### Pendiente / posibles mejoras

* Integración completa con Mercado Pago.
* Control de stock.
* Mejoras adicionales de seguridad.
* Mejoras de experiencia de usuario.
* Despliegue en un servidor público.
* Configuración de dominio para producción.

---

# 🎓 Proyecto académico

Proyecto desarrollado como parte del proyecto final de **Bachillerato en Tecnologías de la Información**.

## Don Diego Panadería 🍰
