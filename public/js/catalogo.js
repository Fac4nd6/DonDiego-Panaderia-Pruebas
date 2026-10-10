let productoActual = null;
let cantidadActual = 1;

const nombresPresentacion = {
    unidad: ['unidad', 'unidades'],
    docena: ['docena', 'docenas'],
    'media docena': ['media docena', 'medias docenas'],
    kilogramo: ['kilo', 'kilos']
};

function nombrePresentacion(unidad, cantidad = 1) {
    const nombres = nombresPresentacion[unidad] || [unidad, unidad];
    return Number(cantidad) === 1 ? nombres[0] : nombres[1];
}

function factorPiezas(unidad) {
    return unidad === 'docena' ? 12 : unidad === 'media docena' ? 6 : 1;
}

function stockDisponibleModalidad(stocks, unidad) {
    if (unidad === 'kilogramo') {
        return Number(stocks.kilogramo || 0);
    }

    return Math.floor(Number(stocks.unidad || 0) / factorPiezas(unidad));
}

function leerObjetoJson(valor) {
    if (valor && typeof valor === 'object') {
        return valor;
    }

    try {
        return JSON.parse(valor || '{}');
    } catch {
        return {};
    }
}

const overlay = document.getElementById('productoOverlay');
const cerrarProducto = document.getElementById('cerrarProducto');
const detalleImagen = document.getElementById('detalleImagen');
const detalleNombre = document.getElementById('detalleNombre');
const detalleDescripcion = document.getElementById('detalleDescripcion');
const detallePrecio = document.getElementById('detallePrecio');
const detallePresentacionContenedor = document.getElementById('detallePresentacionContenedor');
const detallePresentaciones = document.getElementById('detallePresentacion');
const detallePrecioUnidad = document.getElementById('detallePrecioUnidad');
const detalleStock = document.getElementById('detalleStock');
const detalleCategoria = document.getElementById('detalleCategoria');
const cantidadProducto = document.getElementById('cantidadProducto');
const cantidadMenos = document.getElementById('cantidadMenos');
const cantidadMas = document.getElementById('cantidadMas');
const agregarCarrito = document.getElementById('agregarCarrito');

function abrirProducto(boton) {
    mostrarProducto({
        id: Number.parseInt(boton.dataset.id, 10),
        nombre: boton.dataset.nombre,
        descripcion: boton.dataset.descripcion,
        precio: Number.parseFloat(boton.dataset.precio),
        stock: Number.parseInt(boton.dataset.stock, 10) || 0,
        unidadVenta: boton.dataset.unidadVenta || 'unidad',
        presentaciones: leerObjetoJson(boton.dataset.presentaciones),
        stocks: leerObjetoJson(boton.dataset.stocks),
        categoria: boton.dataset.categoria,
        imagen: boton.dataset.imagen
    });
}

function mostrarProducto(producto) {
    if (!producto) {
        return;
    }

    productoActual = { ...producto };
    productoActual.presentaciones = leerObjetoJson(productoActual.presentaciones);
    productoActual.stocks = leerObjetoJson(productoActual.stocks);

    if (Object.keys(productoActual.presentaciones).length === 0) {
        productoActual.presentaciones = {
            [productoActual.unidadVenta || 'unidad']: Number(productoActual.precio || 0)
        };
    }

    if (Object.keys(productoActual.stocks).length === 0) {
        const modalidadAnterior = productoActual.unidadVenta || 'unidad';
        productoActual.stocks = modalidadAnterior === 'kilogramo'
            ? { unidad: 0, kilogramo: Number(productoActual.stock || 0) }
            : { unidad: Number(productoActual.stock || 0) * factorPiezas(modalidadAnterior), kilogramo: 0 };
    }

    productoActual.unidadVenta = Object.keys(productoActual.presentaciones)[0];
    cantidadActual = 1;

    detalleImagen.src = productoActual.imagen;
    detalleImagen.alt = productoActual.nombre;
    detalleNombre.textContent = productoActual.nombre;
    detalleDescripcion.textContent = productoActual.descripcion;
    detalleCategoria.textContent = productoActual.categoria;

    if (detallePresentaciones) {
        detallePresentaciones.replaceChildren();
        Object.keys(productoActual.presentaciones).forEach(unidad => {
            const opcion = document.createElement('button');
            opcion.type = 'button';
            opcion.className = 'detalle-presentacion-opcion';
            opcion.dataset.unidad = unidad;
            opcion.setAttribute('aria-pressed', 'false');
            opcion.textContent = 'Por ' + nombrePresentacion(unidad);
            opcion.addEventListener('click', () => {
                productoActual.unidadVenta = unidad;
                actualizarPresentacionProducto();
            });
            detallePresentaciones.append(opcion);
        });
    }

    if (detallePresentacionContenedor) {
        detallePresentacionContenedor.hidden = Object.keys(productoActual.presentaciones).length < 2;
    }

    actualizarPresentacionProducto();

    overlay?.classList.add('activo');
    document.body.classList.add('modal-abierto');
    overlay?.setAttribute('aria-hidden', 'false');
}

function abrirProductoDesdeDatos(producto) {
    if (!producto) {
        return;
    }

    mostrarProducto({
        id: Number.parseInt(producto.id, 10),
        nombre: producto.nombre,
        descripcion: producto.descripcion,
        precio: Number.parseFloat(producto.precio),
        stock: Number.parseInt(producto.stock, 10) || 0,
        unidadVenta: producto.unidad_venta || 'unidad',
        presentaciones: producto.presentaciones || {},
        stocks: producto.stocks_modalidades || producto.stocks || {},
        categoria: producto.categoria,
        imagen: producto.imagen
            ? window.APP_BASE_URL + '/public/img/' + producto.imagen
            : window.APP_BASE_URL + '/public/img/logo.avif'
    });
}

function actualizarPresentacionProducto() {
    if (!productoActual) {
        return;
    }

    const unidad = productoActual.unidadVenta;
    const esKilo = unidad === 'kilogramo';
    productoActual.precio = Number(productoActual.presentaciones[unidad]);
    productoActual.stock = stockDisponibleModalidad(productoActual.stocks, unidad);

    cantidadProducto.min = esKilo ? '0.1' : '1';
    cantidadProducto.step = esKilo ? '0.1' : '1';
    cantidadProducto.max = String(Math.min(99, productoActual.stock));
    cantidadMenos.hidden = esKilo;
    cantidadMas.hidden = esKilo;

    if (productoActual.stock > 0) {
        cantidadActual = Math.max(Number(cantidadProducto.min), Math.min(cantidadActual, productoActual.stock));
    } else {
        cantidadActual = esKilo ? 0.1 : 1;
    }
    cantidadProducto.value = cantidadActual;
    cantidadProducto.setCustomValidity('');
    cantidadMas.disabled = productoActual.stock <= cantidadActual;
    cantidadMenos.disabled = cantidadActual <= Number(cantidadProducto.min);

    agregarCarrito.disabled = productoActual.stock <= 0 || !cantidadProducto.checkValidity();
    agregarCarrito.textContent = productoActual.stock > 0
        ? 'Agregar al carrito'
        : 'Producto agotado';

    detallePrecio.textContent = '$' + productoActual.precio.toLocaleString('es-UY', {
        maximumFractionDigits: 2
    });
    detallePrecioUnidad.textContent = 'por ' + nombrePresentacion(unidad);
    detalleStock.textContent = productoActual.stock > 0
        ? 'Stock disponible: ' + productoActual.stock + ' ' + nombrePresentacion(unidad, productoActual.stock)
        : 'Producto agotado';

    detallePresentaciones?.querySelectorAll('[data-unidad]').forEach(opcion => {
        const seleccionada = opcion.dataset.unidad === unidad;
        opcion.classList.toggle('activo', seleccionada);
        opcion.setAttribute('aria-pressed', seleccionada ? 'true' : 'false');
    });
}

function cerrarDetalleProducto() {
    overlay?.classList.remove('activo');
    document.body.classList.remove('modal-abierto');
    overlay?.setAttribute('aria-hidden', 'true');
}

cerrarProducto?.addEventListener('click', cerrarDetalleProducto);

overlay?.addEventListener('click', event => {
    if (event.target === overlay) {
        cerrarDetalleProducto();
    }
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && overlay?.classList.contains('activo')) {
        cerrarDetalleProducto();
    }
});

cantidadProducto?.addEventListener('input', () => {
    cantidadActual = Number(cantidadProducto.value);
    cantidadProducto.setCustomValidity('');
    const cantidadValida = Number.isFinite(cantidadActual)
        && cantidadProducto.value !== ''
        && cantidadProducto.checkValidity()
        && cantidadActual >= Number(cantidadProducto.min)
        && cantidadActual <= Math.min(99, productoActual?.stock ?? 0);
    cantidadProducto.setCustomValidity(cantidadValida ? '' : 'Ingresá una cantidad disponible.');
    agregarCarrito.disabled = !cantidadValida;
});

cantidadMenos?.addEventListener('click', () => {
    const paso = Number(cantidadProducto.step);
    cantidadActual = Math.max(Number(cantidadProducto.min), Number((cantidadActual - paso).toFixed(1)));
    cantidadProducto.value = cantidadActual;
    cantidadMenos.disabled = cantidadActual <= Number(cantidadProducto.min);
    cantidadMas.disabled = cantidadActual >= productoActual.stock;
});

cantidadMas?.addEventListener('click', () => {
    if (!productoActual) {
        return;
    }

    const paso = Number(cantidadProducto.step);
    cantidadActual = Math.min(
        Math.min(99, productoActual.stock),
        Number((cantidadActual + paso).toFixed(1))
    );
    cantidadProducto.value = cantidadActual;
    cantidadMenos.disabled = cantidadActual <= Number(cantidadProducto.min);
    cantidadMas.disabled = cantidadActual >= productoActual.stock;
});

agregarCarrito?.addEventListener('click', async () => {
    if (!productoActual) {
        return;
    }

    if (agregarCarrito.dataset.autenticado !== '1') {
        window.location.href = window.APP_BASE_URL + '/login';
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        agregarCarrito.textContent = 'Error de seguridad';
        agregarCarrito.disabled = false;
        return;
    }

    agregarCarrito.disabled = true;
    agregarCarrito.textContent = 'Agregando...';

    const datos = new URLSearchParams({
        accion: 'agregar',
        producto_id: String(productoActual.id),
        cantidad: String(cantidadActual),
        unidad_venta: productoActual.unidadVenta,
        csrf_token: csrfToken
    });

    try {
        const respuesta = await fetch(window.APP_BASE_URL + '/carrito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos.toString()
        });

        if (!respuesta.ok) {
            throw new Error('Error HTTP: ' + respuesta.status);
        }

        await respuesta.text();
        await actualizarContadorCarrito();
        agregarCarrito.textContent = 'Agregado al carrito';
        window.setTimeout(() => {
            cerrarDetalleProducto();
            actualizarPresentacionProducto();
        }, 800);
    } catch (error) {
        console.error('Error al agregar al carrito:', error);
        agregarCarrito.textContent = 'Error al agregar';
        window.setTimeout(actualizarPresentacionProducto, 1500);
    }
});

async function actualizarContadorCarrito() {
    try {
        const respuesta = await fetch(window.APP_BASE_URL + '/carrito');
        if (!respuesta.ok) {
            return;
        }

        const html = await respuesta.text();
        const temporal = document.createElement('div');
        temporal.innerHTML = html;
        const cantidad = temporal.querySelector('.resumen-linea span:last-child');
        const contador = document.querySelector('.cart-count');

        if (cantidad && contador) {
            contador.textContent = cantidad.textContent.trim();
        }
    } catch (error) {
        console.error('No se pudo actualizar el contador:', error);
    }
}
