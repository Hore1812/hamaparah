<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';
require_once '../funciones.php';

$response = [
    'success' => false,
    'message' => 'Petición no válida.',
    'data' => null
];

if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso denegado. Sesión no iniciada.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT, ['options' => ['default' => date('Y')]]);
    $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
    $idcliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);
    $idparticipante = filter_input(INPUT_POST, 'idparticipante', FILTER_VALIDATE_INT);

    try {
        global $pdo;
        
        // --- CONSULTA 1: Horas liquidadas VINCULADAS a una planificación ---
        $sql_planificadas = "SELECT * FROM vista_planificacion_vs_participantes_completado WHERE 1=1";
        $params_planificadas = [];

        if (!empty($anio)) {
            if (!empty($mes)) {
                $mes_str = str_pad($mes, 2, '0', STR_PAD_LEFT);
                $sql_planificadas .= " AND MesPlan = :mes_plan";
                $params_planificadas[':mes_plan'] = "$anio-$mes_str";
            } else {
                $sql_planificadas .= " AND MesPlan LIKE :anio_like";
                $params_planificadas[':anio_like'] = "$anio-%";
            }
        }
        
        if (!empty($idcliente)) {
            $sql_planificadas .= " AND idContratoCliente IN (SELECT idcontratocli FROM contratocliente WHERE idcliente = :idcliente)";
            $params_planificadas[':idcliente'] = $idcliente;
        }

        if (!empty($idparticipante)) {
            $sql_planificadas .= " AND IdParticipante = :idparticipante";
            $params_planificadas[':idparticipante'] = $idparticipante;
        }
        
        $stmt_planificadas = $pdo->prepare($sql_planificadas);
        $stmt_planificadas->execute($params_planificadas);
        $resultados_planificados = $stmt_planificadas->fetchAll(PDO::FETCH_ASSOC);

        // --- CONSULTA 2: Horas liquidadas SIN planificación correspondiente ---
        $sql_no_planificadas = "
            SELECT
                l.idcontratocli,
                c.nombrecomercial AS NombreCliente,
                dh.participante AS IdParticipante,
                e.nombrecorto AS NombreParticipante,
                SUM(dh.calculo) AS HorasCompletadasPorParticipante
            FROM liquidacion l
            JOIN distribucionhora dh ON l.idliquidacion = dh.idliquidacion
            JOIN empleado e ON dh.participante = e.idempleado
            JOIN contratocliente cc ON l.idcontratocli = cc.idcontratocli
            JOIN cliente c ON cc.idcliente = c.idcliente
            WHERE l.estado = 'Completo' AND l.activo = 1";

        $params_no_planificadas = [];
        
        if ($anio) {
            $sql_no_planificadas .= " AND YEAR(l.fecha) = :anio";
            $params_no_planificadas[':anio'] = $anio;
        }
        if ($mes) {
            $sql_no_planificadas .= " AND MONTH(l.fecha) = :mes";
            $params_no_planificadas[':mes'] = $mes;
        }
        if ($idcliente) {
            $sql_no_planificadas .= " AND cc.idcliente = :idcliente";
            $params_no_planificadas[':idcliente'] = $idcliente;
        }
        if ($idparticipante) {
            $sql_no_planificadas .= " AND dh.participante = :idparticipante";
            $params_no_planificadas[':idparticipante'] = $idparticipante;
        }

        $sql_no_planificadas .= "
            AND NOT EXISTS (
                SELECT 1 FROM planificacion p
                WHERE p.idContratoCliente = l.idcontratocli
                AND YEAR(p.fechaplan) = YEAR(l.fecha)
                AND MONTH(p.fechaplan) = MONTH(l.fecha)
            )
            GROUP BY l.idcontratocli, c.nombrecomercial, dh.participante, e.nombrecorto";
        
        $stmt_no_planificadas = $pdo->prepare($sql_no_planificadas);
        $stmt_no_planificadas->execute($params_no_planificadas);
        $resultados_no_planificados = $stmt_no_planificadas->fetchAll(PDO::FETCH_ASSOC);

        // --- Unificar resultados ---
        if (empty($resultados_planificados) && empty($resultados_no_planificados)) {
            $response['success'] = true;
            $response['message'] = 'No se encontraron datos para los filtros seleccionados.';
            $response['data'] = [];
            echo json_encode($response);
            exit;
        }

        $planificaciones = [];
        // Procesar resultados planificados
        foreach ($resultados_planificados as $fila) {
            $idplan = $fila['Idplanificacion'];
            if (!isset($planificaciones[$idplan])) {
                $planificaciones[$idplan] = [
                    'id' => $idplan,
                    'nombre' => $fila['NombrePlan'],
                    'cliente' => $fila['NombreCliente'],
                    'horas_planificadas' => floatval($fila['HorasPlanificadasGlobal']),
                    'total_horas_completadas' => floatval($fila['TotalHorasLiquidadasCompletadas']),
                    'participantes' => []
                ];
            }
            if ($fila['IdParticipante']) {
                 $participante_existente = false;
                 foreach($planificaciones[$idplan]['participantes'] as &$p) { // Usar referencia para actualizar
                     if ($p['id'] == $fila['IdParticipante']) {
                         $p['horas_completadas'] += floatval($fila['HorasCompletadasPorParticipante']);
                         $participante_existente = true;
                         break;
                     }
                 }
                 if (!$participante_existente) {
                     $planificaciones[$idplan]['participantes'][] = [
                        'id' => $fila['IdParticipante'],
                        'nombre' => $fila['NombreParticipante'],
                        'horas_completadas' => floatval($fila['HorasCompletadasPorParticipante']),
                        'porcentaje_contribucion' => floatval($fila['PorcentajeDelParticipanteEnCompletadas'])
                    ];
                 }
            }
        }
        
        // Procesar y añadir resultados no planificados
        if (!empty($resultados_no_planificados)) {
            $id_no_planificado = 'no-planificadas';
            if (!isset($planificaciones[$id_no_planificado])) {
                $planificaciones[$id_no_planificado] = [
                    'id' => $id_no_planificado,
                    'nombre' => 'Liquidaciones no planificadas',
                    'cliente' => 'Varios', // O se podría agrupar por cliente si es necesario
                    'horas_planificadas' => 0,
                    'total_horas_completadas' => 0,
                    'participantes' => []
                ];
            }
            
            $participantes_no_planificados = [];
            foreach ($resultados_no_planificados as $fila) {
                $id_participante = $fila['IdParticipante'];
                if(!isset($participantes_no_planificados[$id_participante])) {
                    $participantes_no_planificados[$id_participante] = [
                        'id' => $id_participante,
                        'nombre' => $fila['NombreParticipante'],
                        'horas_completadas' => 0
                    ];
                }
                $horas = floatval($fila['HorasCompletadasPorParticipante']);
                $participantes_no_planificados[$id_participante]['horas_completadas'] += $horas;
                $planificaciones[$id_no_planificado]['total_horas_completadas'] += $horas;
            }
            $planificaciones[$id_no_planificado]['participantes'] = array_values($participantes_no_planificados);
        }

        $response['data'] = array_values($planificaciones);
        $response['success'] = true;
        $response['message'] = 'Datos obtenidos correctamente.';

    } catch (PDOException $e) {
        error_log("Error de BD en obtener_reporte_participacion.php: " . $e->getMessage());
        $response['message'] = 'Error de base de datos al generar el reporte: ' . $e->getMessage();
    } catch (Exception $e) {
        error_log("Error general en obtener_reporte_participacion.php: " . $e->getMessage());
        $response['message'] = 'Error inesperado al procesar la solicitud.';
    }
}

echo json_encode($response);
?>
