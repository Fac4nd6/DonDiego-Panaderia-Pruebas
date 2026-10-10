document.addEventListener('DOMContentLoaded', () => {
    const lista = document.querySelector('[data-encargo-productos]');
    const template = document.getElementById('encargo-producto-template');
    const agregar = document.querySelector('[data-agregar-producto]');

    if (!lista || !template || !agregar) {
        return;
    }

    function actualizarPasoCantidad(fila) {
        const modalidad = fila.querySelector('[data-encargo-modalidad]');
        const cantidad = fila.querySelector('[name="cantidad[]"]');
        const esKilo = modalidad.value === 'kilogramo';
        cantidad.min = esKilo ? '0.1' : '1';
        cantidad.step = esKilo ? '0.1' : '1';
    }

    function actualizarVistaProducto(fila) {
        const producto = fila.querySelector('[data-encargo-producto]');
        const opcion = producto.selectedOptions[0];
        const imagen = fila.querySelector('[data-producto-preview-imagen]');
        const nombre = fila.querySelector('[data-producto-preview-nombre]');

        if (!opcion || !opcion.value) {
            imagen.src = `${window.APP_BASE_URL}/public/img/logo.avif`;
            imagen.alt = '';
            nombre.textContent = 'Elegí para ver el producto';
            return;
        }

        imagen.src = opcion.dataset.imagen || `${window.APP_BASE_URL}/public/img/logo.avif`;
        imagen.alt = opcion.textContent.trim();
        nombre.textContent = opcion.textContent.trim();
    }

    function actualizarModalidades(fila) {
        const producto = fila.querySelector('[data-encargo-producto]');
        const modalidad = fila.querySelector('[data-encargo-modalidad]');
        const opcion = producto.selectedOptions[0];
        modalidad.replaceChildren();

        if (!opcion || !opcion.value) {
            modalidad.add(new Option('Primero elegí un producto', ''));
            modalidad.disabled = true;
            actualizarPasoCantidad(fila);
            return;
        }

        let presentaciones = {};
        try {
            presentaciones = JSON.parse(opcion.dataset.presentaciones || '{}');
        } catch {
            presentaciones = {};
        }

        Object.entries(presentaciones).forEach(([unidad, precio]) => {
            const etiqueta = unidad === 'kilogramo' ? 'Kilo' : unidad.charAt(0).toUpperCase() + unidad.slice(1);
            modalidad.add(new Option(`${etiqueta} · $${Number(precio).toLocaleString('es-UY')}`, unidad));
        });

        modalidad.disabled = modalidad.options.length === 0;
        if (modalidad.options.length === 0) {
            modalidad.add(new Option('Sin modalidades disponibles', ''));
        }

        actualizarPasoCantidad(fila);
    }

    function actualizarFilas() {
        const filas = [...lista.querySelectorAll('.encargo-producto-fila')];
        filas.forEach(fila => {
            const quitar = fila.querySelector('[data-quitar-producto]');
            quitar.hidden = filas.length === 1;
        });
    }

    lista.addEventListener('change', event => {
        if (event.target.matches('[data-encargo-producto]')) {
            const fila = event.target.closest('.encargo-producto-fila');
            fila.querySelector('[name="cantidad[]"]').value = '';
            actualizarVistaProducto(fila);
            actualizarModalidades(fila);
        }

        if (event.target.matches('[data-encargo-modalidad]')) {
            actualizarPasoCantidad(event.target.closest('.encargo-producto-fila'));
        }
    });

    lista.addEventListener('click', event => {
        const quitar = event.target.closest('[data-quitar-producto]');
        if (quitar) {
            quitar.closest('.encargo-producto-fila').remove();
            actualizarFilas();
        }
    });

    agregar.addEventListener('click', () => {
        const fila = template.content.firstElementChild.cloneNode(true);
        lista.append(fila);
        actualizarFilas();
        fila.querySelector('[data-encargo-producto]').focus();
    });

    lista.querySelectorAll('.encargo-producto-fila').forEach(fila => {
        actualizarVistaProducto(fila);
        actualizarModalidades(fila);
    });
    actualizarFilas();
});