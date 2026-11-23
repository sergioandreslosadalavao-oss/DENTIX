<?php
// app/odontologo_catalogos.php - Catálogos dinámicos de Especialidades Odontológicas y Consultorios por Sede (RF-039 / RF-041 / RN-19 / RN-20)

/**
 * Retorna el listado por defecto de 10 consultorios iniciales (usado en la semilla inicial o respaldo).
 */
function obtenerListaBaseConsultorios(): array
{
    $consultorios = [];
    for ($i = 1; $i <= 10; $i++) {
        $consultorios[] = 'Consultorio ' . $i;
    }
    return $consultorios;
}

/**
 * Normaliza una cadena (minúsculas, sin espacios dobles ni acentos) para comparar duplicados.
 */
function normalizarClaveCatalogo(string $texto): string
{
    $texto = trim(preg_replace('/\s+/u', ' ', $texto) ?? $texto);
    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($convertido !== false) {
        $texto = $convertido;
    }
    return strtolower($texto);
}

/**
 * Garantiza de forma idempotente la existencia de la tabla dinámica `consultorio` (RF-041 / RN-20).
 * Si la tabla aún no ha sido creada manualmente desde pgAdmin, la inicializa con los 10 consultorios
 * base y sincroniza los consultorios ya asignados a odontólogos existentes.
 */
function asegurarTablaConsultorio(PDO $conexion): bool
{
    static $verificada = false;
    if ($verificada) {
        return true;
    }

    try {
        $existe = $conexion->query("SELECT to_regclass('public.consultorio')")->fetchColumn();
        if ($existe === null || $existe === false) {
            $conexion->exec(
                "CREATE TABLE IF NOT EXISTS consultorio (
                    id        INT GENERATED ALWAYS AS IDENTITY,
                    nombre    VARCHAR(30) NOT NULL,
                    creado_en TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT pk_consultorio PRIMARY KEY (id),
                    CONSTRAINT ck_consultorio_nombre CHECK (length(trim(nombre)) > 0)
                )"
            );
            $conexion->exec(
                "CREATE UNIQUE INDEX IF NOT EXISTS uq_consultorio_nombre_ci
                 ON consultorio (lower(trim(nombre)))"
            );
            $conexion->exec(
                "INSERT INTO consultorio (nombre)
                 SELECT v.nombre
                 FROM (VALUES
                     ('Consultorio 1'), ('Consultorio 2'), ('Consultorio 3'),
                     ('Consultorio 4'), ('Consultorio 5'), ('Consultorio 6'),
                     ('Consultorio 7'), ('Consultorio 8'), ('Consultorio 9'),
                     ('Consultorio 10')
                 ) AS v(nombre)
                 WHERE NOT EXISTS (SELECT 1 FROM consultorio LIMIT 1)"
            );
            $conexion->exec(
                "INSERT INTO consultorio (nombre)
                 SELECT DISTINCT trim(o.consultorio)
                 FROM odontologo o
                 WHERE o.consultorio IS NOT NULL
                   AND trim(o.consultorio) <> ''
                   AND NOT EXISTS (
                       SELECT 1 FROM consultorio c
                       WHERE lower(trim(c.nombre)) = lower(trim(o.consultorio))
                   )"
            );
        }

        $verificada = true;
        return true;
    } catch (PDOException $e) {
        error_log('Error al asegurar tabla consultorio: ' . $e->getMessage());
        return false;
    }
}

/**
 * Consulta todos los consultorios configurados para la sede en PostgreSQL, ordenados de forma natural
 * (Consultorio 1, Consultorio 2, ..., Consultorio 10, Consultorio 11).
 */
function obtenerConsultoriosCatalogo(PDO $conexion): array
{
    if (!asegurarTablaConsultorio($conexion)) {
        return array_map(
            static fn(string $n): array => ['id' => null, 'nombre' => $n],
            obtenerListaBaseConsultorios()
        );
    }

    try {
        $rows = $conexion->query(
            "SELECT id, trim(nombre) AS nombre FROM consultorio ORDER BY id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $lista = [];
        foreach ($rows as $row) {
            $nombre = trim((string) ($row['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $lista[] = [
                'id' => (int) $row['id'],
                'nombre' => $nombre,
            ];
        }

        usort($lista, static fn(array $a, array $b): int => strnatcasecmp($a['nombre'], $b['nombre']));
        return $lista;
    } catch (PDOException $e) {
        error_log('Error al leer catálogo de consultorios: ' . $e->getMessage());
        return array_map(
            static fn(string $n): array => ['id' => null, 'nombre' => $n],
            obtenerListaBaseConsultorios()
        );
    }
}

/**
 * Obtiene dinámicamente las especialidades odontológicas disponibles (RF-044 / RN-21):
 * La especialidad clínica de cada odontólogo corresponde única y exclusivamente a los
 * servicios odontológicos registrados en la tabla `servicio` (los 5 servicios oficiales
 * de la clínica), garantizando coincidencia exacta 1:1 para que Recepción solo pueda
 * agendarle a cada paciente un odontólogo especializado en el servicio que solicitó.
 */
function obtenerEspecialidadesOdontologia(PDO $conexion): array
{
    $serviciosBase = [
        'Consulta general',
        'Limpieza dental',
        'Calza (resina)',
        'Blanqueamiento dental',
        'Ortodoncia correctiva',
    ];

    $mapa = [];

    try {
        $rowsServ = $conexion->query(
            "SELECT trim(nombre) AS nombre
             FROM servicio
             WHERE nombre IS NOT NULL AND trim(nombre) <> ''
             ORDER BY id ASC"
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach ($rowsServ as $serv) {
            $serv = trim((string) $serv);
            if ($serv === '') {
                continue;
            }
            $clave = normalizarClaveCatalogo($serv);
            if (!isset($mapa[$clave])) {
                $mapa[$clave] = $serv;
            }
        }
    } catch (PDOException $e) {
        error_log('Error al cargar servicios como especialidades odontológicas: ' . $e->getMessage());
    }

    if ($mapa === []) {
        foreach ($serviciosBase as $serv) {
            $mapa[normalizarClaveCatalogo($serv)] = $serv;
        }
    }

    return array_values($mapa);
}

/**
 * Verifica si la especialidad de un odontólogo corresponde al servicio solicitado por el paciente (RN-21).
 */
function coincideEspecialidadConServicio(string $especialidadOdontologo, string $nombreServicio): bool
{
    $claveEsp = normalizarClaveCatalogo($especialidadOdontologo);
    $claveSrv = normalizarClaveCatalogo($nombreServicio);
    return $claveEsp !== '' && $claveEsp === $claveSrv;
}

/**
 * Infiere si un primer nombre corresponde al género femenino o masculino y antepone
 * automáticamente "Dr. " o "Dra. " cuando el recepcionista no lo ingresó manualmente (RF-045 / RN-22).
 * Si el nombre ya inicia con Dr./Dr/Dra./Dra, lo normaliza manteniendo la elección del usuario.
 */
function formatearNombreOdontologoConTitulo(string $nombre): string
{
    $limpio = trim(preg_replace('/\s+/u', ' ', $nombre) ?? $nombre);
    if ($limpio === '') {
        return '';
    }

    if (preg_match('/^(dra?)\.?\s+(.+)$/iu', $limpio, $m)) {
        $prefijo = strtolower($m[1]) === 'dra' ? 'Dra.' : 'Dr.';
        $resto = trim($m[2]);
        return $resto === '' ? '' : ($prefijo . ' ' . $resto);
    }

    $partes = preg_split('/\s+/u', $limpio);
    $primerNombre = normalizarClaveCatalogo((string) ($partes[0] ?? ''));

    $nombresFemeninosSinA = [
        'abril', 'ailin', 'amparo', 'angie', 'araceli', 'astrid', 'beatriz', 'belen',
        'carmen', 'carol', 'celeste', 'chloe', 'cindy', 'consuelo', 'daisy', 'deisy',
        'denisse', 'dolores', 'edith', 'elizabeth', 'esther', 'evelyn', 'flor', 'genesis',
        'guadalupe', 'heidy', 'ingrid', 'irene', 'iris', 'isabel', 'janeth', 'jennifer',
        'jenny', 'judith', 'karen', 'katerin', 'katherine', 'kathy', 'kelly', 'kimberly',
        'lady', 'leidy', 'lesly', 'lilibeth', 'liz', 'lizeth', 'luz', 'magaly', 'maribel',
        'marisol', 'marlene', 'mary', 'mayra', 'mayerly', 'mercedes', 'michelle', 'milagros',
        'miriam', 'naomi', 'nataly', 'nathaly', 'nancy', 'nicole', 'nieves', 'noemi',
        'pilar', 'rachel', 'raquel', 'rocio', 'rosario', 'ruth', 'salome', 'sandy',
        'sarai', 'sharick', 'sharon', 'soledad', 'sol', 'stephanie', 'sully', 'trinidad',
        'wendy', 'yaneth', 'yudy', 'yuly', 'zoe'
    ];

    $nombresMasculinosConA = [
        'borja', 'bautista', 'ezra', 'joshua', 'luca', 'mustafa', 'nikola'
    ];

    $esFemenino = false;
    if ($primerNombre !== '') {
        if (in_array($primerNombre, $nombresFemeninosSinA, true)) {
            $esFemenino = true;
        } elseif (!in_array($primerNombre, $nombresMasculinosConA, true) && str_ends_with($primerNombre, 'a')) {
            $esFemenino = true;
        }
    }

    return ($esFemenino ? 'Dra. ' : 'Dr. ') . $limpio;
}

/**
 * Consulta en PostgreSQL el estado en tiempo real de todos los consultorios configurados para la sede.
 * Si $excluirOdontologoId se indica (modo edición), el consultorio propio de ese odontólogo
 * se marca como disponible para él mismo.
 */
function obtenerEstadoConsultorios(PDO $conexion, ?int $excluirOdontologoId = null): array
{
    $catalogo = obtenerConsultoriosCatalogo($conexion);
    $ocupadosPorClave = [];

    try {
        $stmt = $conexion->query(
            "SELECT id, nombre, especialidad, consultorio, activo
             FROM odontologo
             WHERE activo IS TRUE
               AND consultorio IS NOT NULL
               AND trim(consultorio) <> ''
             ORDER BY id ASC"
        );
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($asignaciones as $asig) {
            if ($excluirOdontologoId !== null && (int) $asig['id'] === $excluirOdontologoId) {
                continue;
            }
            $clave = normalizarClaveCatalogo((string) $asig['consultorio']);
            $ocupadosPorClave[$clave] = $asig;
        }
    } catch (PDOException $e) {
        error_log('Error al consultar disponibilidad de consultorios: ' . $e->getMessage());
    }

    $todos = [];
    $libres = [];
    $ocupados = [];
    $clavesExistentes = [];

    foreach ($catalogo as $idx => $consRow) {
        $nombreConsultorio = $consRow['nombre'];
        $clave = normalizarClaveCatalogo($nombreConsultorio);
        $clavesExistentes[$clave] = true;
        $asignado = $ocupadosPorClave[$clave] ?? null;

        $item = [
            'id' => $consRow['id'],
            'numero' => $idx + 1,
            'nombre' => $nombreConsultorio,
            'ocupado' => $asignado !== null,
            'odontologo' => $asignado,
        ];
        $todos[] = $item;
        if ($asignado !== null) {
            $ocupados[] = $item;
        } else {
            $libres[] = $item;
        }
    }

    foreach ($ocupadosPorClave as $clave => $asig) {
        if (!isset($clavesExistentes[$clave])) {
            $clavesExistentes[$clave] = true;
            $item = [
                'id' => null,
                'numero' => count($todos) + 1,
                'nombre' => trim((string) $asig['consultorio']),
                'ocupado' => true,
                'odontologo' => $asig,
            ];
            $todos[] = $item;
            $ocupados[] = $item;
        }
    }

    usort($todos, static fn(array $a, array $b): int => strnatcasecmp($a['nombre'], $b['nombre']));
    usort($libres, static fn(array $a, array $b): int => strnatcasecmp($a['nombre'], $b['nombre']));
    usort($ocupados, static fn(array $a, array $b): int => strnatcasecmp($a['nombre'], $b['nombre']));

    $numSugerido = 1;
    while (isset($clavesExistentes[normalizarClaveCatalogo('Consultorio ' . $numSugerido)])) {
        $numSugerido++;
    }

    return [
        'todos' => $todos,
        'libres' => $libres,
        'ocupados' => $ocupados,
        'total' => count($todos),
        'siguiente_sugerido' => 'Consultorio ' . $numSugerido,
    ];
}
