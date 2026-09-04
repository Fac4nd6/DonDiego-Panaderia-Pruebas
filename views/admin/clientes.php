<?php

// Muestra y filtra los clientes registrados.
$pageCss = "admin-productos.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-admin">

    <main class="admin-productos-container">

        <section class="admin-header">
            <div>
                <span class="admin-etiqueta">DON DIEGO</span>
                <h1>Clientes</h1>
                <p>Consultá los clientes registrados.</p>
            </div>

            <div class="admin-header-botones">
                <a
                    href="<?= url('/admin/productos') ?>"
                    class="btn-agregar">
                    Volver a productos
                </a>
            </div>
        </section>

        <section class="admin-resumen clientes-busqueda">
            <div class="resumen-card">
                <form method="GET" action="<?= url('/admin/clientes') ?>">
                    <input type="hidden" name="accion" value="clientes">
                    <label for="buscar_cliente">Buscar usuario</label>
                    <div class="clientes-busqueda-controles">
                        <input
                            type="search"
                            id="buscar_cliente"
                            name="buscar"
                            value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Nombre, email o teléfono">
                        <select name="rol" aria-label="Filtrar por rol">
                            <option value="todos" <?= $rol === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="cliente" <?= $rol === 'cliente' ? 'selected' : '' ?>>Clientes</option>
                            <option value="empleado" <?= $rol === 'empleado' ? 'selected' : '' ?>>Empleados</option>
                            <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administradores</option>
                        </select>
                        <button type="submit" class="btn-agregar">Buscar</button>
                        <?php if ($busqueda !== '' || $rol !== 'cliente'): ?>
                            <a href="<?= url('/admin/clientes') ?>" class="btn-filtro-limpiar">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <?php if (empty($clientes)): ?>
            <section class="productos-vacio">
                <h2>No se encontraron resultados</h2>
                <p><?= $busqueda !== '' ? 'Probá con otro criterio de búsqueda.' : 'Todavía no hay clientes registrados.' ?></p>
            </section>
        <?php else: ?>
            <section class="tabla-contenedor">
                <table class="tabla-productos tabla-clientes">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cliente['nombre_completo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($cliente['nombre_comercio'])): ?>
                                        <span class="cliente-comercio"><?= htmlspecialchars($cliente['nombre_comercio'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($cliente['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= !empty($cliente['telefono']) ? htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8') : 'No informado' ?></td>
                                <td><?= htmlspecialchars(ucfirst($cliente['rol']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>
