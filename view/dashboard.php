<?php
// Variables disponibles: $user, $documentos, $documentosPendientes, $documentosCargados, $totalDocumentos, $ultimoRegistro, $filtros
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function truncateText(string $value, int $limit = 60): string
{
    if (mb_strlen($value) <= $limit) {
        return $value;
    }

    return mb_substr($value, 0, $limit - 3) . '...';
}

$areaLabels = [
    'COM' => 'Comunicacion',
    'CU' => 'Control Urbano',
    'DU' => 'Desarrollo Urbano',
    'GA' => 'Gestion Ambiental',
    'JUR' => 'Juridico',
];
$consultaGlobal = $user['rol'] === 'usuario' && (($filtros['area'] ?? 'mi_area') !== 'mi_area');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema EDUMAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        :root {
            --brand-green: #40B554;
            --brand-lime: #A8CF45;
            --cream-1: #fffdf4;
            --cream-2: #f8f6e8;
            --panel-bg: rgba(255, 252, 244, 0.92);
            --panel-border: #dbe7b7;
            --panel-shadow: 0 20px 45px rgba(114, 145, 55, 0.12);
            --text-main: #455136;
            --text-soft: #6f7b5c;
            --line-strong: #cfe0a0;
            --line-soft: #e7efd0;
            --header-grad: linear-gradient(135deg, rgba(168, 207, 69, 0.24), rgba(64, 181, 84, 0.14));
            --card-grad: linear-gradient(180deg, rgba(255, 253, 244, 0.98), rgba(245, 249, 229, 0.96));
        }

        .compact-input {
            width: 100%;
            border: 1px solid var(--panel-border);
            border-radius: 0.75rem;
            padding: 0.65rem 0.85rem;
            font-size: 0.875rem;
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.88);
        }

        .compact-input:focus {
            outline: none;
            border-color: var(--brand-green);
            box-shadow: 0 0 0 3px rgba(168, 207, 69, 0.22);
        }

        .compact-input[disabled] {
            background: #f6f5ea;
            color: var(--text-soft);
        }

        .soft-panel {
            border: 1px solid var(--panel-border);
            background: var(--card-grad);
            box-shadow: var(--panel-shadow);
        }

        .soft-stat {
            border: 1px solid var(--panel-border);
            background: linear-gradient(180deg, rgba(255, 251, 240, 0.96), rgba(242, 248, 224, 0.98));
            box-shadow: 0 12px 30px rgba(133, 163, 63, 0.1);
        }

        .soft-section-loaded {
            border-bottom: 1px solid var(--panel-border);
            background: linear-gradient(90deg, rgba(168, 207, 69, 0.22), rgba(255, 253, 244, 0.94), rgba(64, 181, 84, 0.18));
        }

        .soft-section-pending {
            border-bottom: 1px solid var(--panel-border);
            background: linear-gradient(90deg, rgba(255, 244, 210, 0.88), rgba(255, 251, 240, 0.94), rgba(168, 207, 69, 0.14));
        }

        .soft-btn {
            background: linear-gradient(135deg, var(--brand-lime), var(--brand-green));
            color: #fff;
            box-shadow: 0 10px 22px rgba(99, 157, 63, 0.22);
        }

        .soft-btn:hover {
            filter: brightness(0.97);
            box-shadow: 0 14px 26px rgba(99, 157, 63, 0.28);
        }

        .soft-badge-loaded {
            background: rgba(64, 181, 84, 0.14);
            color: #46763a;
        }

        .soft-badge-pending {
            background: rgba(168, 207, 69, 0.2);
            color: #6b7c2e;
        }

        .table-head {
            background: linear-gradient(180deg, rgba(245, 249, 229, 0.95), rgba(235, 243, 208, 0.95));
        }

        .dashboard-table {
            min-width: 1320px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-left: 1px solid var(--line-strong);
            border-top: 1px solid var(--line-strong);
        }

        .dashboard-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            border-right: 1px solid var(--line-strong);
            border-bottom: 1px solid var(--line-strong);
            color: #66714a;
        }

        .dashboard-table tbody td {
            border-right: 1px solid var(--line-soft);
            border-bottom: 1px solid var(--line-soft);
        }

        .dashboard-table thead th:first-child,
        .dashboard-table tbody td:first-child {
            border-left: none;
        }

        .dashboard-table thead th:last-child,
        .dashboard-table tbody td:last-child {
            border-right: none;
        }
    </style>
</head>

<body class="text-slate-800" style="background: linear-gradient(180deg, var(--cream-1) 0%, #f6f8e7 45%, #eef7e8 100%);">
    <!-- HEADER -->
    <header class="border-b shadow-sm backdrop-blur" style="border-color: var(--panel-border); background: linear-gradient(180deg, rgba(255,252,244,0.96), rgba(247,250,234,0.92));">
        <div class="mx-auto max-w-[1700px] px-4 sm:px-5 lg:px-6">
            <div
                class="flex min-h-[68px] flex-col justify-center gap-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:py-0">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-bold tracking-[0.24em] text-white" style="background: linear-gradient(135deg, #A8CF45, #40B554); box-shadow: 0 10px 22px rgba(99, 157, 63, 0.22);">
                        ED</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight" style="color: #5f7d2d;">EDUMAS</h1>
                        <span class="text-xs text-slate-500 sm:text-sm">
                            <?= $user['rol'] === 'admin' ? 'Panel de Administración' : 'Portal de Gestión de Documentos' ?>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-900">
                            <?= htmlspecialchars($user['nombre'] ?? $user['nombre_usuario']) ?></p>
                        <p class="text-xs text-slate-500">
                            <?= htmlspecialchars($user['area']) ?> |
                            <?= $user['rol'] === 'admin' ? 'Administrador' : 'Usuario' ?>
                        </p>
                    </div>
                    <a href="index.php?action=logout"
                        class="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700 sm:text-sm">
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="mx-auto max-w-[1700px] px-4 py-6 sm:px-5 lg:px-6">
        <!-- FLASH MESSAGES -->
        <?php if (isset($flash) && is_array($flash)): ?>
            <div
                class="mb-5 rounded-2xl border px-4 py-3 <?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800' ?>">
                <p class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></p>
            </div>
        <?php endif; ?>

        <!-- ESTADÍSTICAS POR ÁREA (admin) -->
        <?php if ($user['rol'] === 'admin' && !empty($areaConteos)): ?>
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5">
                <?php foreach ($areaConteos as $areaInfo): ?>
                    <div class="soft-stat rounded-2xl px-5 py-4 text-center">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <?= htmlspecialchars($areaLabels[$areaInfo['area']] ?? $areaInfo['area']) ?>
                            (<?= htmlspecialchars($areaInfo['area']) ?>)
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900"><?= (int) $areaInfo['total'] ?></p>
                        <p class="text-xs text-slate-400">Documentos</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ESTADÍSTICAS -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <?php if ($user['rol'] === 'admin'): ?>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total Documentos</p>
                    <p class="text-3xl font-bold text-slate-900"><?= $totalDocumentos ?></p>
                </div>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total Usuarios</p>
                    <p class="text-3xl font-bold text-slate-900"><?= $totalUsuarios ?? 0 ?></p>
                </div>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuarios Activos (30
                        días)</p>
                    <p class="text-3xl font-bold text-slate-900"><?= $usuariosActivos ?? 0 ?></p>
                </div>
            <?php else: ?>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total Documentos</p>
                    <p class="text-3xl font-bold text-slate-900"><?= $totalDocumentos ?></p>
                </div>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pendientes de Cargar
                    </p>
                    <p class="text-3xl font-bold text-slate-900"><?= count($documentosPendientes) ?></p>
                </div>
                <div class="soft-stat rounded-2xl px-5 py-4">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cargados</p>
                    <p class="text-3xl font-bold text-slate-900"><?= count($documentosCargados) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="xl:col-span-3">
                <div class="soft-panel rounded-3xl p-5">
                    <?php if ($user['rol'] === 'admin'): ?>
                        <!-- FORMULARIO PARA CREAR USUARIO (ADMIN) -->
                        <h2 class="mb-5 text-base font-bold text-slate-900">Crear Nuevo Usuario</h2>

                        <form method="POST" action="index.php?action=crear_usuario" class="space-y-3.5">
                            <!-- CORREO ELECTRÓNICO -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Correo
                                    electrónico *</label>
                                <input type="email" name="email" required class="compact-input"
                                    placeholder="usuario@ejemplo.com">
                            </div>

                            <!-- NOMBRE DE USUARIO -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nombre
                                    de usuario *</label>
                                <input type="text" name="username" required class="compact-input" placeholder="usuario123">
                            </div>

                            <!-- NOMBRE COMPLETO -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nombre
                                    completo *</label>
                                <input type="text" name="nombre" required class="compact-input" placeholder="Juan Pérez">
                            </div>

                            <!-- CONTRASEÑA -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Contraseña
                                    *</label>
                                <input type="password" name="password" minlength="6" required class="compact-input"
                                    placeholder="••••••••">
                            </div>

                            <!-- ROL -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Rol
                                    *</label>
                                <select name="rol" required class="compact-input">
                                    <option value="usuario">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>

                            <!-- ÁREA -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Área
                                    *</label>
                                <select name="area" required class="compact-input">
                                    <option value="COM">COM</option>
                                    <option value="CU">CU</option>
                                    <option value="DU">DU</option>
                                    <option value="GA">GA</option>
                                    <option value="JUR">JUR</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="soft-btn w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                Crear Usuario
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- FORMULARIO PARA CREAR DOCUMENTO (USUARIO) -->
                        <h2 class="mb-5 text-base font-bold text-slate-900">Crear Documento</h2>

                        <form method="POST" action="index.php?action=crear" class="space-y-3.5">
                            <!-- ÁREA (automática, solo lectura) -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Área</label>
                                <input type="text" value="<?= htmlspecialchars($user['area']) ?>" disabled
                                    class="compact-input">
                            </div>

                            <!-- FECHA (automática, solo lectura) -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Fecha</label>
                                <input type="text" value="<?= date('d/m/Y') ?>" disabled class="compact-input">
                            </div>

                            <!-- USUARIO (automático, solo lectura) -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Usuario</label>
                                <input type="text" value="<?= htmlspecialchars($user['nombre_usuario']) ?>" disabled
                                    class="compact-input">
                            </div>

                            <!-- CORREO (automático, solo lectura) -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Correo</label>
                                <input type="text" value="<?= htmlspecialchars($user['correo']) ?>" disabled
                                    class="compact-input">
                            </div>

                            <!-- NOMBRE DEL DOCUMENTO -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nombre
                                    del Documento *</label>
                                <input type="text" name="nombre" required class="compact-input"
                                    placeholder="Ej: Solicitud de Presupuesto">
                            </div>

                            <!-- DESCRIPCIÓN -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Descripción</label>
                                <textarea name="descripcion" rows="3" class="compact-input min-h-[96px] resize-y"
                                    placeholder="Detalles adicionales..."></textarea>
                            </div>

                            <button type="submit"
                                class="soft-btn w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                Crear Documento
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUMNA DERECHA: DOCUMENTOS -->
            <div class="space-y-5 xl:col-span-9">
                <!-- FILTROS -->
                <div class="soft-panel rounded-3xl p-5">
                    <h3 class="mb-4 text-base font-bold text-slate-900">Filtros</h3>
                    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <input type="hidden" name="action" value="dashboard">

                        <input type="text" name="nombre" placeholder="Nombre documento"
                            value="<?= htmlspecialchars($filtros['nombre'] ?? '') ?>" class="compact-input">

                        <select name="area" class="compact-input">
                            <?php if ($user['rol'] === 'usuario'): ?>
                                <option value="" <?= (($filtros['area'] ?? '') === '') ? 'selected' : '' ?>>Todas las áreas</option>
                                <option value="mi_area" <?= (($filtros['area'] ?? 'mi_area') === 'mi_area') ? 'selected' : '' ?>>Mi área</option>
                                <option value="COM" <?= ($filtros['area'] === 'COM') ? 'selected' : '' ?>>Comunicacion (COM)</option>
                                <option value="CU" <?= ($filtros['area'] === 'CU') ? 'selected' : '' ?>>Control Urbano (CU)</option>
                                <option value="DU" <?= ($filtros['area'] === 'DU') ? 'selected' : '' ?>>Desarrollo Urbano (DU)</option>
                                <option value="GA" <?= ($filtros['area'] === 'GA') ? 'selected' : '' ?>>Gestion Ambiental (GA)</option>
                                <option value="JUR" <?= ($filtros['area'] === 'JUR') ? 'selected' : '' ?>>Juridico (JUR)</option>
                            <?php else: ?>
                                <option value="">Todas las áreas</option>
                                <option value="COM" <?= ($filtros['area'] === 'COM') ? 'selected' : '' ?>>COM</option>
                                <option value="CU" <?= ($filtros['area'] === 'CU') ? 'selected' : '' ?>>CU</option>
                                <option value="DU" <?= ($filtros['area'] === 'DU') ? 'selected' : '' ?>>DU</option>
                                <option value="GA" <?= ($filtros['area'] === 'GA') ? 'selected' : '' ?>>GA</option>
                                <option value="JUR" <?= ($filtros['area'] === 'JUR') ? 'selected' : '' ?>>JUR</option>
                            <?php endif; ?>
                        </select>

                        <input type="date" name="fecha" value="<?= htmlspecialchars($filtros['fecha'] ?? '') ?>"
                            class="compact-input">

                        <input type="text" name="numero_registro" placeholder="Número de registro"
                            value="<?= htmlspecialchars($filtros['numero_registro'] ?? '') ?>" class="compact-input">

                        <button type="submit"
                            class="soft-btn rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                            Filtrar
                        </button>
                    </form>
                </div>

                <!-- DOCUMENTOS CARGADOS -->
                <div class="soft-panel overflow-hidden rounded-3xl">
                    <div class="soft-section-loaded px-5 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Documentos Cargados</h3>
                                <p class="text-xs text-slate-500">
                                    <?= $consultaGlobal
                                        ? 'Consulta global en modo solo lectura. Puedes ver PDFs de otros usuarios, pero no modificarlos.'
                                        : 'Listado de PDFs ya disponibles para consulta.' ?>
                                </p>
                            </div>
                            <span class="soft-badge-loaded inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">
                                <?= count($documentosCargados) ?> registrados
                            </span>
                        </div>
                    </div>
                    <?php if (empty($documentosCargados)): ?>
                        <div class="px-6 py-10 text-center text-slate-500">
                            <p class="text-sm font-medium">No hay documentos cargados</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="dashboard-table">
                                <thead class="table-head">
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            #</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Número</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Área</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Fecha</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Documento</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Registrante</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Correo</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Descripción</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                            Adjunto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    <?php foreach ($documentosCargados as $index => $doc): ?>
                                        <tr class="transition odd:bg-[rgba(255,253,244,0.92)] even:bg-[rgba(245,249,229,0.7)] hover:bg-[rgba(223,242,210,0.85)]">
                                            <td class="px-3 py-2.5 align-middle">
                                                <span
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[11px] font-bold text-slate-700">
                                                    <?= $index + 1 ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                <?= htmlspecialchars($doc['numero_registro']) ?></td>
                                            <td class="px-3 py-2.5 align-middle text-xs font-semibold text-slate-600">
                                                <?= htmlspecialchars($doc['area']) ?></td>
                                            <td class="px-3 py-2.5 align-middle text-xs text-slate-600">
                                                <?= htmlspecialchars($doc['fecha']) ?></td>
                                            <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                <?= htmlspecialchars(truncateText($doc['nombre_documento'], 34)) ?></td>
                                            <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                <?= htmlspecialchars(truncateText($doc['registrante_nombre'] ?? $user['nombre'] ?? $user['nombre_usuario'], 28)) ?>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-xs text-slate-600">
                                                <?= htmlspecialchars(truncateText($doc['registrante_correo'] ?? $user['correo'], 32)) ?>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-xs text-slate-500"
                                                title="<?= htmlspecialchars($doc['descripcion'] ?? '') ?>">
                                                <?= htmlspecialchars(truncateText($doc['descripcion'] ?: 'Sin descripción', 40)) ?>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle">
                                                <div class="flex  flex-wrap justify-end gap-2">
                                                    <?php if (!empty($doc['archivo_disponible'])): ?>
                                                        <a href="index.php?action=ver_pdf&id=<?= (int) $doc['id'] ?>" target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold transition" style="background: rgba(168, 207, 69, 0.18); color: #4f7a2d;">
                                                            Ver PDF
                                                        </a>
                                                    <?php else: ?>
                                                        <span
                                                            class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold" style="background: rgba(223, 228, 207, 0.6); color: #8a9278;">
                                                            No disponible
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($user['rol'] === 'admin'): ?>
                                                        <a href="index.php?action=eliminar&id=<?= $doc['id'] ?>"
                                                            onclick="return confirm('¿Eliminar documento?')"
                                                            class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700">
                                                            Eliminar
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- DOCUMENTOS PENDIENTES -->
                <?php if (!$consultaGlobal): ?>
                    <div class="soft-panel overflow-hidden rounded-3xl">
                        <div class="soft-section-pending px-5 py-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Pendientes de Cargar</h3>
                                    <p class="text-xs text-slate-500">Documentos creados que aún no tienen PDF adjunto.</p>
                                </div>
                                <span class="soft-badge-pending inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">
                                    <?= count($documentosPendientes) ?> pendientes
                                </span>
                            </div>
                        </div>
                        <?php if (empty($documentosPendientes)): ?>
                            <div class="px-6 py-10 text-center text-slate-500">
                                <p class="text-sm font-medium">No hay documentos pendientes</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="dashboard-table">
                                    <thead class="table-head">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                #</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Número</th>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Área</th>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Fecha</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Documento</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Registrante</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Correo</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Descripción</th>
                                            <th scope="col"
                                                class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                                Adjunto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        <?php foreach ($documentosPendientes as $index => $doc): ?>
                                            <tr class="transition odd:bg-[rgba(255,253,244,0.92)] even:bg-[rgba(250,247,226,0.8)] hover:bg-[rgba(245,238,198,0.75)]">
                                                <td class="px-3 py-2.5 align-middle">
                                                    <span
                                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[11px] font-bold text-slate-700">
                                                        <?= $index + 1 ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                    <?= htmlspecialchars($doc['numero_registro']) ?></td>
                                                <td class="px-3 py-2.5 align-middle text-xs font-semibold text-slate-600">
                                                    <?= htmlspecialchars($doc['area']) ?></td>
                                                <td class="px-3 py-2.5 align-middle text-xs text-slate-600">
                                                    <?= htmlspecialchars($doc['fecha']) ?></td>
                                                <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                    <?= htmlspecialchars(truncateText($doc['nombre_documento'], 34)) ?></td>
                                                <td class="px-4 py-2.5 align-middle text-xs text-slate-700">
                                                    <?= htmlspecialchars(truncateText($doc['registrante_nombre'] ?? $user['nombre'] ?? $user['nombre_usuario'], 28)) ?>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle text-xs text-slate-600">
                                                    <?= htmlspecialchars(truncateText($doc['registrante_correo'] ?? $user['correo'], 32)) ?>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle text-xs text-slate-500"
                                                    title="<?= htmlspecialchars($doc['descripcion'] ?? '') ?>">
                                                    <?= htmlspecialchars(truncateText($doc['descripcion'] ?: 'Sin descripción', 40)) ?>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle">
                                                    <div class="flex flex-wrap justify-end gap-2">
                                                        <?php if ($user['rol'] === 'usuario'): ?>
                                                            <form method="POST" action="index.php?action=subir"
                                                                enctype="multipart/form-data"
                                                                class="flex min-w-[260px] flex-wrap justify-end gap-1.5">
                                                                <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                                <input type="file" name="pdf" accept="application/pdf" required
                                                                    class="block min-w-[180px] text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                                                <button type="submit"
                                                                    class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold text-[#40b554] transition hover:bg-blue-50 hover:text-blue-700">
                                                                    Subir
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span
                                                                class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold text-amber-600">
                                                                Pendiente
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($user['rol'] === 'admin'): ?>
                                                            <a href="index.php?action=eliminar&id=<?= $doc['id'] ?>"
                                                                onclick="return confirm('¿Eliminar documento?')"
                                                                class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700">
                                                                Eliminar
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>
