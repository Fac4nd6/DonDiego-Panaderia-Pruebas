let productoActual = null;
let cantidadActual = 1;


/* =========================================================
   ELEMENTOS
========================================================= */

const overlay = document.getElementById('productoOverlay');

const cerrarProducto = document.getElementById('cerrarProducto');

const detalleImagen = document.getElementById('detalleImagen');

const detalleNombre = document.getElementById('detalleNombre');

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
   ABRIR PRODUCTO
========================================================= */

function abrirProducto(boton) {

    productoActual = {

        id: parseInt(boton.dataset.id),

        nombre: boton.dataset.nombre,

        descripcion: boton.dataset.descripcion,

        precio: parseFloat(boton.dataset.precio),

        categoria: boton.dataset.categoria,

        imagen: boton.dataset.imagen

    };


    cantidadActual = 1;


    /* =====================================================
       MOSTRAR INFORMACIÓN
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

    overlay.classList.add('activo');

    document.body.classList.add('modal-abierto');

    overlay.setAttribute(
        'aria-hidden',
        'false'
    );

}


/* =========================================================
   CERRAR PRODUCTO
========================================================= */

function cerrarDetalleProducto() {

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
        function(event) {

            if (event.target === overlay) {

                cerrarDetalleProducto();

            }

        }
    );

}


/* =========================================================
   ESC PARA CERRAR
========================================================= */

document.addEventListener(
    'keydown',
    function(event) {

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
        function() {

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
        function() {

            if (cantidadActual < 99) {

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
        async function() {

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
                   CERRAR MODAL DESPUÉS DE UN MOMENTO
                ============================================= */

                setTimeout(
                    function() {

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
                    function() {

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


        /*
         * Buscamos la cantidad de productos
         * directamente desde la página del carrito.
         */

        const temporal =
            document.createElement('div');

        temporal.innerHTML =
            html;


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