<?php
$pageCss = ['admin-pedidos.css', 'planificacion.css'];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';
$tandas = $tandas ?? [];
$e = static fn ($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<body class="pagina-admin-pedidos pagina-planificacion">
    <main class="admin-pedidos-container">
        <header class="admin-pedidos-header">
            <div><span class="admin-etiqueta">PRODUCCIÓN</span><h1>Historial de tandas</h1><p>Consultá tandas guardadas y volvé a imprimir sus documentos.</p></div>
            <div class="admin-header-acciones">
                <?php if (usuarioPuede('gestionar_produccion')): ?><a href="<?= url('/admin/planificacion') ?>" class="btn-agregar">Nueva tanda</a><?php endif; ?>
            </div>
        </header>

        <?php if ($tandas === []): ?>
            <section class="admin-pedidos-vacio"><h2>Todavía no hay tandas</h2><p>Las tandas creadas aparecerán aquí con sus documentos disponibles.</p></section>
        <?php else: ?>
            <section class="planificacion-seccion">
                <div class="planificacion-tabla-contenedor">
                    <table class="planificacion-tabla">
                        <thead><tr><th>Tanda</th><th>Fecha y hora</th><th>Responsable</th><th>Pedidos</th><th>Líneas</th><th>Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach ($tandas as $tanda): ?>
                                <tr>
                                    <?php $tituloTanda = trim((string) ($tanda['titulo'] ?? '')) ?: 'Tanda #' . (int) $tanda['id'] . ' - ' . date('d/m/Y H:i', strtotime($tanda['fecha_creacion'])); ?>
                                    <td data-label="Tanda"><strong>#<?= (int) $tanda['id'] ?></strong><br><?= $e($tituloTanda) ?></td>
                                    <td data-label="Fecha y hora"><?= $e(date('d/m/Y H:i', strtotime($tanda['fecha_creacion']))) ?></td>
                                    <td data-label="Responsable"><?= $e($tanda['responsable']) ?></td>
                                    <td data-label="Pedidos"><?= (int) $tanda['cantidad_pedidos'] ?></td>
                                    <td data-label="Líneas"><?= (int) $tanda['cantidad_lineas'] ?></td>
                                    <td data-label="Acciones" class="planificacion-fila-acciones">
                                        <a href="<?= url('/admin/tandas/detalle?id=' . (int) $tanda['id']) ?>">Ver tanda</a>
                                        <a href="<?= url('/admin/tandas/imprimir?id=' . (int) $tanda['id']) ?>">Imprimir / PDF</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>