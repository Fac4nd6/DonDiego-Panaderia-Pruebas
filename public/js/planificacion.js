document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

document.querySelectorAll('.planificacion-asignar-control input').forEach((checkbox) => {
	checkbox.addEventListener('change', () => {
		const label = checkbox.closest('.planificacion-asignar-control');
		const text = label?.querySelector('span');
		const card = checkbox.closest('.planificacion-producto-grupo');
		if (text) {
			text.textContent = checkbox.checked ? 'Agregado a la selección' : 'Asignar a producción';
		}
		card?.classList.toggle('grupo-seleccionado', checkbox.checked);
	});
});

document.querySelectorAll('[data-destinations]').forEach((container) => {
	const list = container.querySelector('[data-destination-list]');
	const addButton = container.querySelector('[data-add-destination]');

	if (!list || !addButton) {
		return;
	}

	const updateAddButton = () => {
		addButton.disabled = list.children.length >= 20;
	};

	addButton.addEventListener('click', () => {
		if (list.children.length >= 20) {
			return;
		}

		const row = document.createElement('div');
		row.className = 'planificacion-destino-fila';
		const label = document.createElement('label');
		label.className = 'solo-lectores';
		label.textContent = 'Destino';
		const input = document.createElement('input');
		input.type = 'text';
		input.name = 'destinos[]';
		input.maxLength = 150;
		input.placeholder = 'Ej.: Sucursal Centro';
		const removeButton = document.createElement('button');
		removeButton.type = 'button';
		removeButton.dataset.removeDestination = '';
		removeButton.setAttribute('aria-label', 'Quitar destino');
		removeButton.textContent = 'Quitar';
		row.append(label, input, removeButton);
		list.append(row);
		input.focus();
		updateAddButton();
	});

	list.addEventListener('click', (event) => {
		const removeButton = event.target.closest('[data-remove-destination]');
		if (!removeButton) {
			return;
		}
		removeButton.closest('.planificacion-destino-fila')?.remove();
		updateAddButton();
	});

	updateAddButton();
});