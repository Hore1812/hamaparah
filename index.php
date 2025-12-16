<?php
require_once 'includes/header.php';
require_once 'funciones.php';

$isAdmin = ($_SESSION['tipo_usuario'] ?? 0) == 1;
?>

<div class="container-fluid mt-4">
    <!-- Fila superior -->
    <div class="row mb-4">
        <!-- Subárea superior izquierda -->
        <div class="col-md-6">
            <div class="card h-100 bg-primary text-white">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <?php
                    date_default_timezone_set('America/Lima');
                    $hora = date('H');
                    $saludo = '';
                    if ($hora < 12) {
                        $saludo = 'Buenos días';
                    } elseif ($hora < 18) {
                        $saludo = 'Buenas tardes';
                    } else {
                        $saludo = 'Buenas noches';
                    }
                    ?>
                    <h1 class="display-4"><?= $saludo ?>, <?= htmlspecialchars($_SESSION['nombre_empleado'] ?? 'Usuario') ?>!</h1>
                    <p class="lead">Conoce las últimas noticias del mundo de las telecomunicaciones.</p>
                    <div class="mt-4 d-grid gap-2 d-md-block">
                        <a href="alertas_normativas.php" class="btn btn-orange btn-lg">Alerta Normativa</a>
                        <a href="boletin_regulatorio.php" class="btn btn-light btn-lg">Boletín Regulatorio</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Subárea superior derecha -->
        <div class="col-md-6">
            <div class="card h-100 rounded bg-primary">
                <div class="card-body p-0">
                    <?php $anunciosActivos = obtenerAnunciosActivos(); ?>
                    <?php if (count($anunciosActivos) > 1): ?>
                        <div id="carouselAnuncios" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <?php foreach ($anunciosActivos as $index => $anuncio): ?>
                                    <button type="button" data-bs-target="#carouselAnuncios" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner rounded">
                                <?php foreach ($anunciosActivos as $index => $anuncio): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($anuncio['rutaarchivo']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($anuncio['comentario']) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselAnuncios" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselAnuncios" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php elseif (count($anunciosActivos) === 1): ?>
                        <img src="<?= htmlspecialchars($anunciosActivos[0]['rutaarchivo']) ?>" class="d-block w-100 rounded" alt="<?= htmlspecialchars($anunciosActivos[0]['comentario']) ?>">
                    <?php else: ?>
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <p class="text-white">No hay anuncios para mostrar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila inferior -->
    <div class="row transparent-cards-row">
        <?php
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $nombreMes = $meses[date('n') - 1];
        
        if ($isAdmin):
        ?>
            <!-- Admin Layout: 3 Gráficos -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Horas por Contrato (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoHorasTipo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Horas Soporte Equipo (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoSoporte"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Rendimiento del Equipo (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoHorasUsuario"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Non-Admin Layout: 3 Gráficos -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Horas Soporte Equipo (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoSoporte"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Mi cumplimiento (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoHorasUsuario"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-center">Mi cumplimiento por tipo hora (<?= $nombreMes ?>)</h5>
                        <div style="width:95%; margin: auto;">
                            <canvas id="graficoCumplimientoTipoHora"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$horasCompletadas = obtenerHorasCompletadasSoporteMesActual();
$horasPlanificadas = obtenerHorasPlanificadasSoporteMesActual();
$horasPendientes = max(0, $horasPlanificadas - $horasCompletadas);

if (!$isAdmin) {
    $idUsuarioActual = $_SESSION['idemp'] ?? 0;
    $horasUsuario = obtenerHorasAsignadasUsuarioMesActual($idUsuarioActual);
    $horasMeta = obtenerHorasMetaEmpleado($idUsuarioActual);
    $horasRestantesMeta = max(0, $horasMeta - $horasUsuario);
    $datosCumplimientoTipoHora = obtenerHorasCumplidasPorTipoUsuarioMesActual($idUsuarioActual);
}

$idUsuarioActual = $_SESSION['idemp'] ?? 0;
$datosContratos = $isAdmin ? obtenerDatosPlanVsLiquidadoPorContrato() : obtenerDatosPlanVsLiquidadoPorContrato($idUsuarioActual);

$nombresContrato = array_column($datosContratos, 'Contrato');
$horasPlanificadasPorContrato = array_column($datosContratos, 'HorasPlanificadas');
$horasCompletadasPorContrato = array_column($datosContratos, 'HorasCompletadas');
?>

<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Spinner para botones
    const buttons = document.querySelectorAll('.btn-lg');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...`;
            this.disabled = true;
            setTimeout(() => {
                window.location.href = this.href;
            }, 750);
        });
    });

    const isAdmin = <?= json_encode($isAdmin) ?>;

    if (isAdmin) {
        // Gráfico de Soporte
        const ctx = document.getElementById('graficoSoporte').getContext('2d');
        const horasCompletadas = <?= json_encode($horasCompletadas) ?>;
        const horasPlanificadas = <?= json_encode($horasPlanificadas) ?>;
        const horasPendientes = <?= json_encode($horasPendientes) ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completadas', 'Pendientes'],
                datasets: [{
                    data: [horasCompletadas, horasPendientes],
                    backgroundColor: ['rgba(75, 192, 192, 0.4)', 'rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Total Planificado: ${horasPlanificadas} horas`
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (horasPlanificadas === 0) {
                                return '0%';
                            }
                            let percentage = (value * 100 / horasPlanificadas).toFixed(2) + '%';
                            return percentage;
                        },
                        color: '#000',
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Horas de Usuario
        const ctx2 = document.getElementById('graficoHorasUsuario').getContext('2d');
        <?php $datosEquipo = obtenerDatosCumplimientoEquipo(); ?>
        const datosEquipo = <?= json_encode($datosEquipo) ?>;
        const nombres = datosEquipo.map(d => d.nombre);
        const horasCumplidas = datosEquipo.map(d => parseFloat(d.horas_cumplidas) || 0);
        const horasMeta = datosEquipo.map(d => parseFloat(d.horas_meta) || 0);

        // Ajustar dinámicamente la altura del contenedor del gráfico
        const numColaboradores = nombres.length;
        const alturaCanvas = Math.max(200, numColaboradores * 50); // 50px por colaborador, mínimo 200px
        ctx2.canvas.parentNode.style.height = `${alturaCanvas}px`;


        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: nombres,
                datasets: [{
                    label: 'Horas Cumplidas',
                    data: horasCumplidas,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    barThickness: 15,
                    categoryPercentage: 0.8, 
                    barPercentage: 0.7 
                }, {
                    label: 'Horas Meta',
                    data: horasMeta,
                    backgroundColor: 'rgba(255, 170, 132, 0.6)',
                    borderColor: 'rgba(255, 170, 132, 1)',
                    borderWidth: 1,
                    barThickness: 15, 
                    categoryPercentage: 0.8,
                    barPercentage: 0.7
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: false,
                    },
                    y: {
                        stacked: false,
                        ticks: {
                            autoSkip: false // Asegura que se muestren todas las etiquetas
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Cumplimiento del Equipo vs. Meta Mensual'
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        formatter: (value) => {
                            return parseFloat(value).toFixed(2);
                        },
                        color: '#000'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const datasetLabel = context.dataset.label || '';
                                const value = context.raw;
                                if (datasetLabel === 'Horas Cumplidas') {
                                    const meta = horasMeta[context.dataIndex];
                                    const percentage = meta > 0 ? (value * 100 / meta).toFixed(2) : 0;
                                    return `${datasetLabel}: ${value.toFixed(2)} (${percentage}%)`;
                                }
                                return `${datasetLabel}: ${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

    } else {
        // Non-admin charts

        // Gráfico de Soporte
        const ctx = document.getElementById('graficoSoporte').getContext('2d');
        const horasCompletadas = <?= json_encode($horasCompletadas) ?>;
        const horasPlanificadas = <?= json_encode($horasPlanificadas) ?>;
        const horasPendientes = <?= json_encode($horasPendientes) ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completadas', 'Pendientes'],
                datasets: [{
                    data: [horasCompletadas, horasPendientes],
                    backgroundColor: ['rgba(75, 192, 192, 0.4)', 'rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Total Planificado: ${horasPlanificadas} horas`
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (horasPlanificadas === 0) {
                                return '0%';
                            }
                            let percentage = (value * 100 / horasPlanificadas).toFixed(2) + '%';
                            return percentage;
                        },
                        color: '#000',
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        const horasUsuario = <?= json_encode($horasUsuario ?? 0) ?>;
        const horasRestantesMeta = <?= json_encode($horasRestantesMeta ?? 0) ?>;
        const horasMeta = <?= json_encode($horasMeta ?? 0) ?>;

        new Chart(document.getElementById('graficoHorasUsuario').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Cumplidas', 'Pendientes'],
                datasets: [{
                    data: [horasUsuario, horasRestantesMeta],
                    backgroundColor: ['rgba(24, 62, 235, 0.4)', 'rgba(201, 203, 207, 0.2)'],
                    borderColor: ['rgba(24, 62, 235, 1)', 'rgba(201, 203, 207, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Meta Mensual: ${parseFloat(horasMeta).toFixed(2)} horas`
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (horasMeta === 0) {
                                return '0%';
                            }
                            let percentage = (value * 100 / horasMeta).toFixed(2) + '%';
                            return percentage;
                        },
                        color: '#000',
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Gráfico de Cumplimiento por Tipo de Hora para no-admin
        const ctx3 = document.getElementById('graficoCumplimientoTipoHora').getContext('2d');
        const datosCumplimientoTipoHora = <?= json_encode($datosCumplimientoTipoHora ?? []) ?>;
        const tiposHora = datosCumplimientoTipoHora.map(d => d.tipohora);
        const horasCumplidasTipo = datosCumplimientoTipoHora.map(d => parseFloat(d.HorasCumplidas) || 0);
        const totalHorasCumplidas = horasCumplidasTipo.reduce((a, b) => a + b, 0);

        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: tiposHora,
                datasets: [{
                    data: horasCumplidasTipo,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.4)',
                        'rgba(54, 162, 235, 0.4)',
                        'rgba(255, 206, 86, 0.4)',
                        'rgba(75, 192, 192, 0.4)',
                        'rgba(153, 102, 255, 0.4)',
                        'rgba(255, 159, 64, 0.4)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Total Horas Cumplidas: ${totalHorasCumplidas.toFixed(2)}`
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (totalHorasCumplidas === 0) {
                                return '0%';
                            }
                            let percentage = (value * 100 / totalHorasCumplidas).toFixed(2) + '%';
                            return percentage;
                        },
                        color: '#000',
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    if (isAdmin) {
        // Gráfico de Horas por Contrato (Plan vs Completado)
        const ctx3 = document.getElementById('graficoHorasTipo').getContext('2d');
        const nombresContrato = <?= json_encode($nombresContrato) ?>;
        const horasPlanificadasPorContrato = <?= json_encode($horasPlanificadasPorContrato) ?>;
        const horasCompletadasPorContrato = <?= json_encode($horasCompletadasPorContrato) ?>;

        const numContratos = nombresContrato.length;
        const alturaCanvasContratos = Math.max(200, numContratos * 50);
        ctx3.canvas.parentNode.style.height = `${alturaCanvasContratos}px`;

        new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: nombresContrato,
            datasets: [{
                label: 'Horas Planificadas',
                data: horasPlanificadasPorContrato,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                barThickness: 15
            }, {
                label: 'Horas Completadas',
                data: horasCompletadasPorContrato,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                barThickness: 15
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    stacked: false,
                },
                y: {
                    stacked: false,
                    ticks: {
                        autoSkip: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Planificadas vs. Completadas por Contrato'
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value) => {
                        return parseFloat(value).toFixed(2);
                    },
                    color: '#000'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const datasetLabel = context.dataset.label || '';
                                const value = context.raw;
                                if (datasetLabel === 'Horas Completadas') {
                                    const plan = horasPlanificadasPorContrato[context.dataIndex];
                                    const percentage = plan > 0 ? (value * 100 / plan).toFixed(2) : 0;
                                    return `${datasetLabel}: ${value.toFixed(2)} (${percentage}%)`;
                                }
                                return `${datasetLabel}: ${value.toFixed(2)}`;
                            }
                        }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    }
});
</script>
