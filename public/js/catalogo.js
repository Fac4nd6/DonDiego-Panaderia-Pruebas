let productoActual = null;
let cantidadActual = 1;


/* =========================================================
   ELEMENTOS
========================================================= */

const overlay =
    document.getElementById('productoOverlay');

const cerrarProducto =
    document.getElementById('cerrarProducto');

const detalleImagen =
    document.getElementById('detalleImagen');

const detalleNombre =
    document.getElementById('detalleNombre');

const detalleDescripcion =
    document.getElementById('detalleDescripcion');

const detallePrecio =
    document.getElementById('detallePrecio');

const detalleCategoria =
    document.getElementById('detalleCategoria');

const cantidadProducto =
    document.getElementById('cantidadProducto');

const cantidadMenos =
    document.getElementById('cantidadMenos');

const cantidadMas =
    document.getElementById('cantidadMas');

const agregarCarrito =
    document.getElementById('agregarCarrito');


/* =========================================================
   ABRIR PRODUCTO DESDE BOTÓN DEL CATÁLOGO
========================================================= */

function abrirProducto(boton) {

    const producto = {

        id: parseInt(boton.dataset.id),

        nombre: boton.dataset.nombre,

        descripcion: boton.dataset.descripcion,

        precio: parseFloat(boton.dataset.precio),

        stock: parseInt(boton.dataset.stock, 10) || 0,

        categoria: boton.dataset.categoria,

        imagen: boton.dataset.imagen

    };

    mostrarProducto(producto);
}


/* =========================================================
   MOSTRAR PRODUCTO
========================================================= */

function mostrarProducto(producto) {

    if (!producto) {
        return;
    }


    productoActual = producto;

    cantidadActual = 1;

    if (cantidadMas) {
        cantidadMas.disabled = productoActual.stock <= 1;
    }

    if (agregarCarrito) {
        agregarCarrito.disabled = productoActual.stock <= 0;
        agregarCarrito.textContent = productoActual.stock > 0
            ? 'Agregar al carrito'
            : 'Producto agotado';
    }


    /* =====================================================
       INFORMACIÓN
    ===================================================== */

    detalleImagen.src =
        productoActual.imagen;

    detalleImagen.alt =
        productoActual.nombre;


    detalleNombre.textContent =
        productoActual.nombre;


    detalleDescripcion.textContent =
        productoActual.descripcion;


    detalleCategoria.textContent =
        productoActual.categoria;


    detallePrecio.textContent =
        '$' +
        productoActual.precio.toLocaleString(
            'es-UY',
            {
                maximumFractionDigits: 0
            }
        );


    cantidadProducto.textContent =
        cantidadActual;


    /* =====================================================
       ABRIR MODAL
    ===================================================== */

    if (!overlay) {
        return;
    }


    overlay.classList.add('activo');

    document.body.classList.add('modal-abierto');

    overlay.setAttribute(
        'aria-hidden',
        'false'
    );
}


/* =========================================================
   ABRIR PRODUCTO DESDE HOME
========================================================= */

function abrirProductoDesdeDatos(producto) {

    if (!producto) {
        return;
    }


    const productoFormateado = {

        id: parseInt(producto.id),

        nombre: producto.nombre,

        descripcion: producto.descripcion,

        precio: parseFloat(producto.precio),

        stock: parseInt(producto.stock, 10) || 0,

        categoria: producto.categoria,

        imagen:
            producto.imagen
                ? '/DonDiego-Panaderia-Pruebas/public/img/' +
                  producto.imagen
                : '/DonDiego-Panaderia-Pruebas/public/img/logo.avif'

    };


    mostrarProducto(
        productoFormateado
    );
}


/* =========================================================
   CERRAR PRODUCTO
========================================================= */

function cerrarDetalleProducto() {

    if (!overlay) {
        return;
    }


    overlay.classList.remove('activo');

    document.body.classList.remove('modal-abierto');

    overlay.setAttribute(
        'aria-hidden',
        'true'
    );
}


/* =========================================================
   BOTÓN CERRAR
========================================================= */

if (cerrarProducto) {

    cerrarProducto.addEventListener(
        'click',
        cerrarDetalleProducto
    );
}


/* =========================================================
   CERRAR HACIENDO CLICK FUERA
========================================================= */

if (overlay) {

    overlay.addEventListener(
        'click',
        function (event) {

            if (event.target === overlay) {

                cerrarDetalleProducto();

            }

        }
    );
}


/* =========================================================
   CERRAR CON ESC
========================================================= */

document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape' &&
            overlay &&
            overlay.classList.contains('activo')
        ) {

            cerrarDetalleProducto();

        }

    }
);


/* =========================================================
   RESTAR CANTIDAD
========================================================= */

if (cantidadMenos) {

    cantidadMenos.addEventListener(
        'click',
        function () {

            if (cantidadActual > 1) {

                cantidadActual--;

                cantidadProducto.textContent =
                    cantidadActual;

            }

        }
    );
}


/* =========================================================
   SUMAR CANTIDAD
========================================================= */

if (cantidadMas) {

    cantidadMas.addEventListener(
        'click',
        function () {

            if (
                productoActual &&
                cantidadActual < 99 &&
                cantidadActual < productoActual.stock
            ) {

                cantidadActual++;

                cantidadProducto.textContent =
                    cantidadActual;

            }

        }
    );
}


/* =========================================================
   AGREGAR AL CARRITO
========================================================= */

if (agregarCarrito) {

    agregarCarrito.addEventListener(
        'click',
        async function () {

            /* -------------------------------------------------
               COMPROBAR PRODUCTO
            ------------------------------------------------- */

            if (!productoActual) {

                console.error(
                    'No hay ningún producto seleccionado.'
                );

                return;
            }


            /* -------------------------------------------------
               DESACTIVAR BOTÓN
            ------------------------------------------------- */

            agregarCarrito.disabled = true;

            agregarCarrito.textContent =
                'Agregando...';


            /* -------------------------------------------------
               OBTENER CSRF
            ------------------------------------------------- */

            const csrfToken =
                document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content;


            if (!csrfToken) {

                console.error(
                    'No se encontró el token CSRF.'
                );

                agregarCarrito.textContent =
                    'Error de seguridad';

                agregarCarrito.disabled =
                    false;

                return;
            }


            /* -------------------------------------------------
               CREAR DATOS
            ------------------------------------------------- */

            const datos =
                new URLSearchParams();


            datos.append(
                'accion',
                'agregar'
            );


            datos.append(
                'producto_id',
                productoActual.id
            );


            datos.append(
                'cantidad',
                cantidadActual
            );


            /* -------------------------------------------------
               CSRF
            ------------------------------------------------- */

            datos.append(
                'csrf_token',
                csrfToken
            );


            try {

                /* =============================================
                   ENVIAR AL CONTROLLER
                ============================================= */

                const respuesta =
                    await fetch(
                        '/DonDiego-Panaderia-Pruebas/controllers/CarritoController.php',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded'
                            },

                            body: datos.toString()
                        }
                    );


                /* =============================================
                   COMPROBAR RESPUESTA
                ============================================= */

                if (!respuesta.ok) {

                    throw new Error(
                        'Error HTTP: ' +
                        respuesta.status
                    );

                }


                /* =============================================
                   LEER RESPUESTA
                ============================================= */

                const resultado =
                    await respuesta.text();


                console.log(
                    'Respuesta del servidor:',
                    resultado
                );


                /* =============================================
                   ACTUALIZAR CONTADOR
                ============================================= */

                actualizarContadorCarrito();


                /* =============================================
                   CAMBIAR BOTÓN
                ============================================= */

                agregarCarrito.textContent =
                    '✓ Agregado al carrito';


                /* =============================================
                   CERRAR MODAL
                ============================================= */

                setTimeout(
                    function () {

                        cerrarDetalleProducto();

                        agregarCarrito.textContent =
                            'Agregar al carrito';

                        agregarCarrito.disabled =
                            false;

                    },
                    800
                );


            } catch (error) {

                console.error(
                    'Error al agregar al carrito:',
                    error
                );


                agregarCarrito.textContent =
                    'Error al agregar';


                setTimeout(
                    function () {

                        agregarCarrito.textContent =
                            'Agregar al carrito';

                        agregarCarrito.disabled =
                            false;

                    },
                    1500
                );

            }

        }
    );
}


/* =========================================================
   ACTUALIZAR CONTADOR DEL CARRITO
========================================================= */

async function actualizarContadorCarrito() {

    try {

        const respuesta =
            await fetch(
                '/DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
            );


        if (!respuesta.ok) {

            return;
        }


        const html =
            await respuesta.text();


        /* -------------------------------------------------
           CREAR DOCUMENTO TEMPORAL
        ------------------------------------------------- */

        const temporal =
            document.createElement('div');

        temporal.innerHTML =
            html;


        /* -------------------------------------------------
           BUSCAR CANTIDAD
        ------------------------------------------------- */

        const cantidad =
            temporal.querySelector(
                '.resumen-linea span:last-child'
            );


        const contador =
            document.querySelector(
                '.cart-count'
            );


        if (
            cantidad &&
            contador
        ) {

            contador.textContent =
                cantidad.textContent.trim();

        }

    } catch (error) {

        console.error(
            'No se pudo actualizar el contador:',
            error
        );

    }

}