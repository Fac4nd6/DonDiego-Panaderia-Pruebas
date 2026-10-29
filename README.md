# Don Diego Panadería 🍰

Plataforma digital propia de **Don Diego Panadería**, desarrollada por Facundo Leites para organizar la venta y gestión de pedidos de la panadería.

La plataforma está destinada principalmente a los comercios que trabajan con Don Diego. Permite consultar el catálogo, seleccionar productos y cantidades, realizar pedidos y consultar su estado e historial.

Incluye un área de administración para gestionar productos, pedidos y planificación de producción. Está construida en PHP y organizada con el patrón **MVC (Model-View-Controller)**.

---

# 📌 Sobre Don Diego

Don Diego Panadería cuenta con más de 20 años de funcionamiento y más de 25 personas trabajando en el local.

Su principal actividad es la venta de productos de panadería y confitería. La empresa trabaja también con comercios que realizan pedidos de productos de manera frecuente.

La plataforma centraliza la recepción y organización de pedidos de los comercios, reduciendo la carga manual de transcribirlos desde canales de contacto.

---

# 🎯 Objetivo

La plataforma permite a los comercios:

* Consultar un catálogo digital.
* Visualizar los productos disponibles.
* Seleccionar cantidades.
* Crear un carrito.
* Realizar pedidos.
* Seleccionar fecha y franja horaria de recepción.
* Consultar pedidos anteriores.
* Consultar el estado de sus pedidos.

Para Don Diego, facilita la gestión de los pedidos recibidos y reúne en un solo lugar la información necesaria para su preparación y seguimiento.

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

El sistema verifica las cuentas mediante correo electrónico y permite recuperar el acceso a una cuenta.

Actualmente contempla:

* Envío de correo de verificación.
* Tokens de verificación.
* Vencimiento de tokens.
* Página de aviso de verificación.
* Reenvío de verificación.
* Límite de reenvíos.
* Restricción de inicio de sesión para cuentas que no hayan sido verificadas.
* Recuperación de contraseña mediante un código temporal.

El envío de correos se realiza mediante la API de **Brevo**.

---

# 🧁 Catálogo

El catálogo permite:

* Consultar los productos disponibles.
* Visualizar información de cada producto.
* Organizar productos mediante categorías.
* Seleccionar cantidades.
* Ver la presentación de venta de cada producto.
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

Cada producto puede tener varias modalidades de venta configurables desde el
área administrativa. Cada modalidad mantiene su precio independiente.
Las opciones disponibles son:

* Unidad.
* Docena.
* Media docena.
* Kilogramo.

Unidad, media docena y docena comparten un único inventario contado en piezas:
una docena descuenta 12 piezas y media docena descuenta 6. El inventario en
kilos es independiente y admite cantidades con una cifra decimal, como `0,5 kg`.

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

Al confirmar un pedido, el cliente permanece en la web y accede al detalle del
pedido registrado. La actualización de esa página no vuelve a enviar el pedido.

Cada pedido tiene un **ID global**, por ejemplo `ID del pedido #40`. Este
identificador permite localizar el pedido en el sistema y no representa la
cantidad de pedidos realizados por el cliente.

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
* Elegir la presentación de venta.
* Definir el stock disponible de cada presentación.

## Pedidos

Los usuarios autorizados pueden:

* Consultar pedidos recibidos.
* Visualizar información de los pedidos.
* Consultar los productos incluidos.
* Consultar información del cliente.
* Actualizar estados.
* Gestionar los pedidos durante su procesamiento.

## Planificación de producción

El personal autorizado puede abrir **Planificación** desde la gestión de
pedidos, filtrar por estado comercial y seleccionar varios pedidos. Los
productos se agrupan automáticamente solo si coinciden producto, presentación
y sector; la asignación normal toma el remanente completo y permite un ajuste
parcial secundario por pedido de origen. El sistema conserva la distribución
por pedido, el estado comercial, el sector guardado en cada línea y resta las
cantidades ya comprometidas en otras tandas. El historial conserva las tandas
y ofrece documentos agrupados por sector, imprimibles o guardables como PDF
desde el navegador.
La planificación es el único punto de entrada para crear tandas. Las columnas
históricas de producción de `pedido_detalles` se conservan para bloquear
duplicados de líneas enviadas anteriormente.

El esquema completo de tandas, incluidos título, destinos, observaciones y su
historial de edición, está en `database/don_diego.sql`. Para una base existente,
ejecutar únicamente el bloque `ALTER TABLE tandas_produccion` que aparece al
final del archivo; no importar el dump completo sobre datos existentes. El
bloque agrega columnas idempotentes, no crea tablas ni modifica líneas de
pedidos. Las tandas antiguas muestran un título automático hasta que se les
asigne uno explícitamente.

---

# 💳 Métodos de pago

El sistema contempla actualmente:

* Efectivo.

El método de efectivo forma parte del flujo de pedidos.

---

# 🔐 Seguridad

La aplicación incorpora medidas de protección para las cuentas, los pedidos y los datos:

* Consultas preparadas mediante `mysqli`.
* `password_hash()` para almacenar contraseñas.
* `password_verify()` para comprobar contraseñas.
* Regeneración del ID de sesión después del inicio de sesión.
* Tokens de verificación generados mediante `random_bytes()`.
* Expiración de tokens.
* Códigos de recuperación de un solo uso almacenados como hash.
* Protección CSRF en las operaciones que modifican datos.
* Control de acceso mediante roles.
* Validación de información recibida desde formularios.
* Escape de información mostrada en HTML.
* Restricción de pedidos según el usuario correspondiente.

---

# 📧 Brevo

El sistema utiliza la API de **Brevo** para el envío de correos de verificación y recuperación de cuentas.

Las credenciales reales no deben incluirse en el repositorio.

La configuración utiliza archivos separados para evitar almacenar las credenciales directamente en el código compartido.

Ejemplo de configuración:

```text
config/
├── Brevo.php
└── Brevo.example.php
```

`Brevo.example.php` sirve como referencia para configurar el servicio.

`Brevo.php` contiene la configuración privada y debe mantenerse fuera del repositorio cuando incluye credenciales reales.

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

La tabla `productos` incluye la columna `unidad_venta`, con valor por defecto
`unidad`. Si se actualiza una base de datos existente sin volver a importar el
archivo SQL, agregarla manualmente:

```sql
ALTER TABLE productos
ADD COLUMN unidad_venta VARCHAR(30) NOT NULL DEFAULT 'unidad'
AFTER stock;
```

Para habilitar precios por presentación y guardar la presentación elegida en
nuevos pedidos existentes, ejecutar también:

```sql
ALTER TABLE productos
ADD COLUMN presentaciones TEXT DEFAULT NULL AFTER unidad_venta;

ALTER TABLE pedido_detalles
ADD COLUMN unidad_venta VARCHAR(30) NOT NULL DEFAULT 'unidad' AFTER cantidad;

ALTER TABLE productos
ADD COLUMN stocks_modalidades TEXT DEFAULT NULL AFTER presentaciones;

ALTER TABLE pedido_detalles
MODIFY COLUMN cantidad DECIMAL(10,1) NOT NULL;
```

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
* Git
* GitHub
* Font Awesome

---

# 📁 Estructura del proyecto

```text
DonDiego-Panaderia/
│
├── README.md
│
├── config/
│   ├── Brevo.example.php
│   ├── EmailService.php
│   ├── Database.php
│   ├── Session.php
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
│   ├── migrations/
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
cd DonDiego-Panaderia
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

> La estructura del archivo SQL debe coincidir con la versión actual del proyecto.

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

En un servidor, utilizar un usuario específico para la aplicación, con contraseña segura y permisos limitados.

---

# 📧 Configurar Brevo

Crear una copia de:

```text
config/Brevo.example.php
```

y utilizarla como:

```text
config/Brevo.php
```

Completar las credenciales correspondientes.

El servicio usa la API cuando `BREVO_API_KEY` está configurada y el transporte es `api`. El remitente debe estar registrado en Brevo. Se puede definir la clave por variable de entorno o en el archivo privado ignorado por Git. En el plan Free, Brevo publica un máximo de 300 envíos diarios.

La plantilla incluye SMTP como transición explícita. Para seleccionarlo, configurar `BREVO_MAIL_TRANSPORT` como `smtp` y cargar sus credenciales. Un error de la API no activa SMTP automáticamente para evitar el envío duplicado.

El envío por API requiere cURL y acceso HTTPS saliente desde el servidor.

Antes de desplegar el límite compartido de solicitudes, aplicar la migración aditiva una vez:

```text
database/migrations/20261028_rate_limit_buckets.sql
```

La tabla almacena categorías, hashes de sujetos, contadores y expiración; el código bloquea la acción sensible si el almacenamiento no está disponible. Hacer copia de seguridad y aplicar la migración antes de publicar el código.

Los límites se aplican por IP a login, registro y reenvío, por IP/correo a recuperación, a los intentos del código y por usuario a pedidos. No se confía en `X-Forwarded-For` sin una configuración de proxy confiable.

Ejemplo de configuración:

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

## WhatsApp de contacto

WhatsApp se ofrece como canal opcional de contacto desde el footer y la sección de contacto. Los pedidos se crean y consultan dentro de la web.

No requiere dependencias externas.

El número se configura en `config/whatsapp.php` mediante la constante `WHATSAPP_NUMERO`. Debe utilizarse un número internacional sin `+`, espacios ni símbolos.

---

# 🌐 Ejecutar el proyecto

Con Apache y MySQL funcionando, acceder desde:

```text
http://localhost/<carpeta-del-proyecto>/
```

---

# 📱 Acceso desde otros dispositivos

Durante el desarrollo local se utiliza `localhost`.

Por ejemplo:

```text
http://localhost/<carpeta-del-proyecto>/
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

# 👤 Responsable

**Facundo Leites** es el único responsable y colaborador del proyecto.
