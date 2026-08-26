# Don Diego Panadería 🍰

Sistema web desarrollado para **Don Diego Panadería** como proyecto académico.

El objetivo del proyecto es desarrollar una plataforma web destinada principalmente a los comercios que trabajan con Don Diego, permitiéndoles consultar un catálogo digital, seleccionar productos y cantidades, realizar pedidos y consultar el estado e historial de sus pedidos.

El sistema también cuenta con un área administrativa destinada a la gestión de productos y pedidos.

El proyecto utiliza una arquitectura organizada basada en el patrón **MVC (Model-View-Controller)**.

---

# 📌 Sobre Don Diego

Don Diego Panadería cuenta con más de 20 años de funcionamiento y más de 25 personas trabajando en el local.

Su principal actividad es la venta de productos de panadería y confitería. La empresa trabaja también con comercios que realizan pedidos de productos de manera frecuente.

Actualmente, los comercios realizan principalmente sus pedidos mediante WhatsApp. Estos pedidos deben ser posteriormente ingresados manualmente por empleados de Don Diego en el sistema interno utilizado por la empresa.

El proyecto busca digitalizar y organizar esta primera parte del proceso mediante una plataforma web.

---

# 🎯 Objetivo

El objetivo principal es desarrollar una plataforma que permita a los comercios:

* Consultar un catálogo digital.
* Visualizar los productos disponibles.
* Seleccionar cantidades.
* Crear un carrito.
* Realizar pedidos.
* Seleccionar fecha y franja horaria de recepción.
* Consultar pedidos anteriores.
* Consultar el estado de sus pedidos.

Para Don Diego, el sistema busca facilitar la gestión de los pedidos recibidos y reducir la cantidad de información que debe ser transcrita manualmente desde WhatsApp.

Como objetivo futuro, se analizará la posibilidad de integrar la plataforma con el sistema utilizado actualmente por Don Diego para automatizar aún más el proceso.

---

# 🚀 Funcionalidades

## 🏠 Página principal

* Página de inicio.
* Información sobre Don Diego.
* Productos destacados.
* Productos recomendados.
* Navegación hacia el catálogo.
* Diseño adaptable a diferentes dispositivos.

---

## 👤 Usuarios

El sistema cuenta con diferentes tipos de usuario:

* `cliente`
* `empleado`
* `admin`

### Cliente

Puede:

* Registrarse.
* Iniciar sesión.
* Cerrar sesión.
* Verificar su cuenta.
* Gestionar sus datos.
* Consultar el catálogo.
* Agregar productos al carrito.
* Realizar pedidos.
* Consultar sus pedidos.
* Consultar el detalle de sus pedidos.
* Cancelar pedidos cuando corresponda.

### Empleado

Puede acceder a funcionalidades relacionadas con la gestión de pedidos, de acuerdo con los permisos establecidos.

Puede:

* Consultar pedidos.
* Visualizar el detalle de los pedidos.
* Actualizar estados.
* Participar en el procesamiento de pedidos.

### Administrador

Dispone de funciones administrativas adicionales:

* Gestionar productos.
* Crear productos.
* Editar productos.
* Activar productos.
* Desactivar productos.
* Gestionar pedidos.
* Consultar información administrativa.

---

# 📧 Verificación de correo

El sistema incorpora un proceso de verificación de cuentas mediante correo electrónico.

Actualmente contempla:

* Envío de correo de verificación.
* Tokens de verificación.
* Vencimiento de tokens.
* Página de aviso de verificación.
* Reenvío de verificación.
* Límite de reenvíos.
* Restricción de inicio de sesión para cuentas que no hayan sido verificadas.

El envío de correos se realiza mediante la API de **Brevo**.

---

# 🧁 Catálogo

El catálogo permite:

* Consultar los productos disponibles.
* Visualizar información de cada producto.
* Organizar productos mediante categorías.
* Seleccionar cantidades.
* Agregar productos al carrito.
* Visualizar productos destacados.
* Visualizar productos recomendados.

Los productos pueden ser activados o desactivados desde el área administrativa.

Los productos desactivados no se muestran normalmente en el catálogo público.

---

# 🛒 Carrito

El sistema dispone de un carrito de compras.

Permite:

* Agregar productos.
* Modificar cantidades.
* Eliminar productos.
* Vaciar el carrito.
* Calcular el total.
* Revisar los productos antes de confirmar el pedido.

El carrito se mantiene mediante la sesión del usuario.

---

# 📦 Pedidos

Los usuarios pueden realizar pedidos mediante la plataforma.

El proceso permite:

* Seleccionar productos.
* Seleccionar cantidades.
* Revisar el carrito.
* Seleccionar fecha de recepción.
* Seleccionar franja horaria.
* Utilizar la información de dirección registrada.
* Seleccionar método de pago.
* Revisar un resumen.
* Confirmar el pedido.
* Consultar posteriormente el pedido.

Los estados actualmente contemplados son:

```text
pendiente
confirmado
en_preparacion
listo
entregado
cancelado
```

---

# 📋 Historial de pedidos

Los usuarios pueden consultar los pedidos que realizaron anteriormente.

El sistema permite visualizar:

* Fecha.
* Estado.
* Total.
* Productos incluidos.
* Cantidades.
* Información del pedido.

También existe una vista específica para consultar el detalle de cada pedido.

Una posible mejora futura es permitir repetir directamente un pedido anterior.

---

# 🛠️ Administración

El sistema cuenta con un área administrativa para gestionar la información utilizada por la plataforma.

## Productos

El administrador puede:

* Crear productos.
* Editar productos.
* Activar productos.
* Desactivar productos.
* Eliminar productos.
* Asignar categorías.
* Gestionar imágenes.

## Pedidos

Los usuarios autorizados pueden:

* Consultar pedidos recibidos.
* Visualizar información de los pedidos.
* Consultar los productos incluidos.
* Consultar información del cliente.
* Actualizar estados.
* Gestionar los pedidos durante su procesamiento.

---

# 💳 Métodos de pago

El sistema contempla actualmente:

* Efectivo.
* Mercado Pago.

El método de efectivo forma parte del flujo de pedidos.

La integración con Mercado Pago se encuentra actualmente **en desarrollo**.

---

# 💳 Mercado Pago

El proyecto cuenta con una estructura inicial para integrar Mercado Pago.

Actualmente se dispone de:

* Servicio para comunicarse con Mercado Pago.
* Controlador para gestionar operaciones relacionadas con pagos.
* Token de acceso.
* Generación de checkout.
* `external_reference` para relacionar operaciones con pedidos.
* Consultas a la API desde el servidor.
* Comprobaciones de respuestas.
* Estructura inicial para recibir notificaciones mediante webhook.

La integración todavía **no está preparada para producción**.

Antes de habilitar pagos reales se deben completar, entre otras, las siguientes tareas:

* Validar la firma de los webhooks.
* Verificar correctamente la orden asociada al pago.
* Persistir correctamente los identificadores de Mercado Pago.
* Implementar idempotencia.
* Prevenir pagos duplicados.
* Validar monto y moneda.
* Configurar una URL pública HTTPS.
* Sincronizar completamente la base de datos con el código.
* Realizar pruebas de errores y reintentos.

---

# 🔐 Seguridad

El proyecto incorpora diferentes mecanismos de seguridad.

Actualmente se utilizan:

* Consultas preparadas mediante `mysqli`.
* `password_hash()` para almacenar contraseñas.
* `password_verify()` para comprobar contraseñas.
* Regeneración del ID de sesión después del inicio de sesión.
* Tokens de verificación generados mediante `random_bytes()`.
* Expiración de tokens.
* Protección CSRF en diferentes partes del sistema.
* Control de acceso mediante roles.
* Validación de información recibida desde formularios.
* Escape de información mostrada en HTML.
* Restricción de pedidos según el usuario correspondiente.

Durante la auditoría de seguridad también se detectaron aspectos que deben corregirse antes de utilizar el sistema en producción.

Entre ellos:

* Protección CSRF todavía no aplicada de forma uniforme.
* Algunas acciones administrativas utilizan GET.
* Configuración de sesión pendiente de endurecimiento.
* Configuración de MySQL de desarrollo que no debe utilizarse en producción.
* Errores PHP visibles durante el desarrollo.
* Seguridad del webhook de Mercado Pago pendiente.
* Revisión de credenciales y secretos.
* Necesidad de realizar pruebas de seguridad adicionales.

No se encontró una inyección SQL directa confirmada durante la auditoría realizada.

Tampoco se encontró una vulnerabilidad IDOR evidente en el acceso de clientes a sus pedidos durante las pruebas realizadas.

---

# 📧 Brevo

El sistema utiliza la API de **Brevo** para el envío de correos relacionados con la verificación de cuentas.

Las credenciales reales no deben incluirse en el repositorio.

La configuración utiliza archivos separados para evitar almacenar las credenciales directamente en el código compartido.

Ejemplo de configuración:

```text
config/
├── Brevo.php
└── Brevo.ejemplo.php
```

`Brevo.ejemplo.php` sirve como referencia para configurar el servicio.

`Brevo.php` debe mantenerse fuera del repositorio cuando contiene credenciales reales.

> **Importante:** si una API Key real se encuentra expuesta, debe revocarse y reemplazarse inmediatamente.

---

# 🗄️ Base de datos

El proyecto utiliza **MySQL**.

Las principales tablas utilizadas son:

```text
usuarios
productos
categorias
pedidos
pedido_detalles
```

La base de datos relaciona usuarios, productos y pedidos para permitir el funcionamiento del sistema.

Los pedidos contienen información relacionada con:

* Usuario.
* Productos.
* Cantidades.
* Precios.
* Estado.
* Fecha.
* Recepción.
* Método de pago.

La estructura SQL debe mantenerse sincronizada con el código actual del proyecto.

Actualmente existe una tarea pendiente de sincronización relacionada con los campos utilizados por Mercado Pago.

---

# ⚙️ Tecnologías

* HTML5
* CSS3
* JavaScript
* PHP 8.2+
* MySQL
* Apache
* XAMPP
* `mysqli`
* cURL
* Brevo API
* Mercado Pago API
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
│   ├── Database.php
│   ├── MercadoPagoClient.php
│   └── mercadopago.php
│
├── controllers/
│   ├── BrevoController.php
│   ├── CarritoController.php
│   ├── CatalogoController.php
│   ├── HomeController.php
│   ├── MercadoPagoController.php
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
├── service/
│   └── MercadoPagoService.php
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

---

# 🏗️ Arquitectura

El proyecto utiliza una estructura basada en **MVC**.

### `controllers/`

Procesa las solicitudes del usuario y coordina las acciones entre modelos y vistas.

### `models/`

Contiene las operaciones relacionadas con los datos y la base de datos.

### `views/`

Contiene las interfaces que se muestran al usuario.

### `config/`

Contiene configuraciones y componentes necesarios para servicios externos y conexión con la base de datos.

### `service/`

Contiene servicios utilizados para comunicarse con sistemas externos.

### `public/`

Contiene los recursos públicos:

* CSS.
* JavaScript.
* Imágenes.

### `database/`

Contiene el archivo SQL utilizado para crear la base de datos.

---

# 💻 Instalación y ejecución

## 1. Requisitos

Para ejecutar el proyecto en un entorno local se necesita:

* XAMPP.
* PHP 8.2 o superior.
* Apache.
* MySQL.
* Git.
* Extensión cURL de PHP.

---

## 2. Clonar el repositorio

Desde la carpeta `htdocs` de XAMPP:

```bash
git clone URL_DEL_REPOSITORIO
```

Entrar al proyecto:

```bash
cd DonDiego-Panaderia-Pruebas
```

---

## 3. Iniciar XAMPP

Abrir XAMPP e iniciar:

```text
Apache
MySQL
```

---

# 🗄️ Configurar la base de datos

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

> La estructura del archivo SQL debe coincidir con la versión actual del proyecto. Si se realizaron cambios recientes en el código relacionados con Mercado Pago, se debe actualizar el esquema antes de realizar una instalación limpia.

---

# ⚙️ Configurar la conexión

Abrir:

```text
config/Database.php
```

Configurar los datos correspondientes al entorno local.

Una configuración típica de XAMPP es:

```php
$host = 'localhost';
$usuario = 'root';
$password = '';
$baseDatos = 'don_diego';
```

Esta configuración está destinada al **entorno local de desarrollo**.

Para producción se debe utilizar un usuario específico para la aplicación, con contraseña segura y permisos limitados.

---

# 📧 Configurar Brevo

Crear una copia de:

```text
config/Brevo.ejemplo.php
```

y utilizarla como:

```text
config/Brevo.php
```

Completar las credenciales correspondientes.

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

No subir nunca una API Key real a GitHub.

Si una credencial real fue expuesta, debe revocarse y reemplazarse.

---

# 💳 Configuración de Mercado Pago

La integración de Mercado Pago se encuentra en desarrollo.

La configuración utiliza componentes relacionados con:

```text
config/mercadopago.php
config/MercadoPagoClient.php
service/MercadoPagoService.php
controllers/MercadoPagoController.php
```

Para utilizar Mercado Pago en producción será necesario configurar:

* Credenciales reales.
* URL pública HTTPS.
* Webhook accesible desde Internet.
* Validación de firma.
* Identificación de pedidos.
* Persistencia de identificadores de pago.
* Idempotencia.
* Validación de monto y moneda.

Actualmente el sistema no debe considerarse preparado para recibir pagos reales en producción.

---

# 🌐 Ejecutar el proyecto

Con Apache y MySQL funcionando, acceder desde:

```text
http://localhost/DonDiego-Panaderia-Pruebas/
```

---

# 📱 Acceso desde otros dispositivos

Durante el desarrollo local se utiliza `localhost`.

Por ejemplo:

```text
http://localhost/DonDiego-Panaderia-Pruebas/
```

`localhost` hace referencia al propio dispositivo.

Para utilizar la plataforma desde otros dispositivos o desde Internet será necesario configurar un servidor accesible desde la red correspondiente.

Para una instalación pública se recomienda utilizar:

* HTTPS.
* Dominio.
* Servidor web.
* Base de datos protegida.
* Variables de entorno para secretos.
* Configuración de producción.

---

# 🧪 Cuentas de prueba

El entorno de desarrollo puede contar con cuentas destinadas a las pruebas de los diferentes roles.

| Rol        | Función                    |
| ---------- | -------------------------- |
| `admin`    | Administración del sistema |
| `empleado` | Gestión de pedidos         |
| `cliente`  | Realización de pedidos     |

Las credenciales de prueba deben mantenerse únicamente en entornos de desarrollo.

No deben utilizarse cuentas con contraseñas conocidas o datos ficticios en una instalación real.

---

# 🧪 Estado del proyecto

Actualmente el proyecto se encuentra en una **etapa avanzada de desarrollo**.

## ✅ Implementado

* Página principal.
* Catálogo.
* Categorías.
* Productos destacados.
* Productos recomendados.
* Registro.
* Inicio de sesión.
* Cierre de sesión.
* Gestión de cuenta.
* Verificación de correo.
* Reenvío de verificación.
* Carrito.
* Creación de pedidos.
* Historial de pedidos.
* Detalle de pedidos.
* Cancelación de pedidos.
* Estados de pedidos.
* Panel administrativo.
* Gestión de productos.
* Gestión de pedidos.
* Roles de usuario.
* Selección de métodos de pago.
* Integración inicial con Brevo.
* Estructura inicial de Mercado Pago.
* Diseño responsive.

## 🟡 En desarrollo / corrección

* Integración completa con Mercado Pago.
* Seguridad del webhook.
* Persistencia de información de pagos.
* Idempotencia de operaciones de pago.
* Sincronización completa de la base de datos.
* Transacciones para la creación de pedidos.
* Validación final de productos y precios.
* Protección CSRF uniforme.
* Endurecimiento de sesiones.
* Correcciones de rutas y navegación.
* Pruebas completas responsive.
* Pruebas automatizadas.

## 🔴 Pendiente

* Integración con el sistema interno de Don Diego.
* Despliegue en un servidor público.
* Configuración definitiva de producción.
* Dominio y HTTPS.
* Pruebas finales de seguridad.
* Pruebas completas de pagos.

---

# 🔒 Preparación para producción

El proyecto funciona como aplicación de desarrollo y presentación académica, pero **todavía no debe considerarse listo para producción**.

Antes de una implementación real se deben completar, entre otras, las siguientes tareas:

* [ ] Revocar y reemplazar credenciales expuestas.
* [ ] Utilizar variables de entorno para secretos.
* [ ] Revisar el historial de Git.
* [ ] Sincronizar completamente la base de datos.
* [ ] Implementar CSRF en todas las operaciones necesarias.
* [ ] Convertir acciones administrativas que modifican datos a POST.
* [ ] Endurecer la configuración de sesiones.
* [ ] Desactivar `display_errors` en producción.
* [ ] Utilizar un usuario MySQL específico.
* [ ] Implementar transacciones en la creación de pedidos.
* [ ] Validar productos y precios nuevamente al confirmar.
* [ ] Completar la integración segura con Mercado Pago.
* [ ] Validar firmas de webhook.
* [ ] Implementar idempotencia.
* [ ] Validar montos y monedas.
* [ ] Corregir rutas de imágenes.
* [ ] Completar pruebas responsive.
* [ ] Crear pruebas automatizadas.
* [ ] Configurar HTTPS.
* [ ] Analizar la integración con el sistema interno de Don Diego.

---

# 📱 Responsive y accesibilidad

El sistema está diseñado para adaptarse a diferentes dispositivos.

Se contemplan:

* Computadoras.
* Tablets.
* Celulares.

Entre las funcionalidades responsive implementadas se encuentran:

* Menú hamburguesa.
* Catálogo adaptable.
* Carrito adaptable.
* Formularios adaptados a pantallas pequeñas.
* Modal de productos.
* Controles táctiles.
* Diferentes presentaciones según el tamaño de pantalla.

Durante la revisión se comprobó el catálogo público en una pantalla de aproximadamente 390 px sin detectar overflow horizontal.

Todavía deben realizarse pruebas completas en diferentes resoluciones y en las páginas que requieren autenticación.

---

# 🔍 Auditoría del proyecto

Se realizó una auditoría estática del código para revisar:

* Seguridad.
* Autenticación.
* Autorización.
* Carrito.
* Pedidos.
* Productos.
* Base de datos.
* Mercado Pago.
* Vistas.
* Responsive.
* Configuración.

La auditoría no modificó ni eliminó archivos del proyecto.

La revisión permitió detectar problemas que deben solucionarse antes de una utilización en producción, además de confirmar diferentes mecanismos de seguridad y funcionalidades que ya se encuentran implementados.

---

# 🧩 Mejoras futuras

Entre las mejoras previstas se encuentran:

* Integración con el sistema interno de Don Diego.
* Repetición de pedidos anteriores.
* Control de stock, si el negocio lo requiere.
* Sistema de notificaciones.
* Mejoras de accesibilidad.
* Mejoras de rendimiento.
* Paginación del catálogo.
* Logs y monitoreo.
* Pruebas automatizadas.
* Mejoras adicionales de seguridad.
* Despliegue en un servidor real.

---

# 🌿 Flujo de trabajo con Git

El proyecto utiliza diferentes ramas para organizar el desarrollo.

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

Se utiliza para integrar los cambios realizados por los integrantes antes de incorporarlos a la versión estable.

### Ramas individuales

Cada integrante trabaja principalmente desde su propia rama.

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

Los cambios importantes deben integrarse mediante Pull Requests para facilitar la revisión y reducir conflictos.

---

# 👥 Equipo

* Thiago Cano
* Facundo Leites
* Constanza Ortiz
* Matias Hernandes

---

# 🎓 Proyecto académico

Proyecto desarrollado como parte del proyecto final de **Bachillerato en Tecnologías de la Información**.

## Don Diego Panadería 🍰

La plataforma busca digitalizar el proceso de realización y gestión de pedidos de los comercios que trabajan con Don Diego, proporcionando una base para futuras mejoras e integraciones con los sistemas internos de la empresa.
