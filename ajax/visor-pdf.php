<?php
include '../conexion.php';
include '../funciones.php';
session_start();

$user_id = $_SESSION["login_user"];

$sql = "SELECT usca_id FROM usuarios WHERE usua_id = $user_id";
$usuario = mysql_fetch_assoc(mysql_query($sql));
$usca_id = $usuario['usca_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Documentos</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #e9ecef;
            --accent-color: #007bff;
            --text-color: #212529;
            --light-text: #f8f9fa;
            --border-radius: 4px;
            --shadow: 0 2px 5px rgba(0,0,0,0.15);
            --transition: all 0.3s ease;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
        }

        #viewerContainer {
            width: 100%;
            height: calc(100vh - 60px);
            overflow-y: auto;
            position: relative;
            background-color: var(--secondary-color);
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-container {
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            background-color: white;
            position: relative;
        }

        .page-container:last-child {
            margin-bottom: 40px;
        }

        .page-number {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.5);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            opacity: 0.7;
        }

        #loadingMessage {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Segoe UI', sans-serif;
            color: var(--primary-color);
            background-color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 100;
        }

        #loadingMessage:before {
            content: "";
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #toolbar {
            background-color: white;
            color: var(--text-color);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .toolButton {
            background-color: white;
            color: var(--text-color);
            border: 1px solid #dee2e6;
            padding: 6px 12px;
            margin: 0 3px;
            cursor: pointer;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 32px;
        }

        .toolButton:hover {
            background-color: var(--secondary-color);
            border-color: #ced4da;
        }

        .toolButton:active {
            background-color: #dee2e6;
        }

        .toolButton i {
            margin-right: 5px;
        }

        #documentTitle {
            font-weight: 500;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 50%;
            text-align: center;
        }

        #zoomControls {
            display: flex;
            align-items: center;
        }

        #jumpToPage {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            padding: 4px;
            width: 140px;
            height: 32px;
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        #jumpToPage select {
            flex: 1;
            border: none;
            outline: none;
            font-size: 14px;
            padding: 0 5px;
        }

        #scrollToTop {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: white;
            color: var(--text-color);
            border: 1px solid #dee2e6;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #scrollToTop.visible {
            opacity: 0.8;
        }

        #scrollToTop:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
<?php
if (!isset($_GET['archivo']) || empty($_GET['archivo'])) {
    echo '<div id="loadingMessage">Error: No se especificó ningún archivo.</div>';
    exit;
}
$archivo = basename($_GET['archivo']);
$rutaArchivo = 'https://giraglogicdesa.girag.aero/manuales-uso/' . $archivo;
$nombreArchivo = pathinfo($archivo, PATHINFO_FILENAME);
?>

<div id="toolbar">
    <div id="jumpToPage">
        <select id="pageSelector">
            <option value="">Ir a página...</option>
        </select>
    </div>
    <div id="documentTitle">
        <?php echo htmlspecialchars($nombreArchivo); ?>
    </div>
    <div id="zoomControls">
        <button class="toolButton" id="zoomOut"><i class="fas fa-search-minus"></i></button>
        <button class="toolButton" id="zoomIn"><i class="fas fa-search-plus"></i></button>
        <button class="toolButton" id="fullscreen"><i class="fas fa-expand"></i></button>
        <?php if (in_array($usca_id, [2, 3, 4])): ?>
            <button class="toolButton" id="printBtn" title="Imprimir"><i class="fas fa-print"></i></button>
            <button class="toolButton" id="downloadBtn" title="Descargar"><i class="fas fa-download"></i></button>
        <?php endif; ?>
    </div>
</div>

<div id="viewerContainer">
    <div id="loadingMessage">Cargando documento...</div>
</div>

<div id="scrollToTop" title="Volver arriba">
    <i class="fas fa-arrow-up"></i>
</div>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    let pdfDoc = null;
    let currentScale = 1.0;
    const viewerContainer = document.getElementById('viewerContainer');
    const loadingMessage = document.getElementById('loadingMessage');
    const pageSelector = document.getElementById('pageSelector');
    const scrollToTopBtn = document.getElementById('scrollToTop');
    const pdfUrl = '<?php echo $rutaArchivo; ?>';
    
    // Botones zoom y pantalla completa
    const zoomInBtn = document.getElementById('zoomIn');
    const zoomOutBtn = document.getElementById('zoomOut');
    const fullscreenBtn = document.getElementById('fullscreen');

    // Cargar documento PDF
    pdfjsLib.getDocument(pdfUrl).promise.then(async function(pdf) {
        pdfDoc = pdf;
        const numPages = pdf.numPages;
        for (let i = 1; i <= numPages; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Página ${i} de ${numPages}`;
            pageSelector.appendChild(option);
        }
        await renderAllPages();
        loadingMessage.style.display = 'none';
    }).catch(function(error) {
        loadingMessage.textContent = 'Error al cargar el documento: ' + error.message;
    });

    // Renderizar todas las páginas
    async function renderAllPages() {
        if (!pdfDoc) return;
        viewerContainer.innerHTML = '';
        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const page = await pdfDoc.getPage(pageNum);
            await renderPage(page, pageNum);
        }
    }

    // Renderizar una página individual
    async function renderPage(page, pageNum) {
        const viewport = page.getViewport({ scale: currentScale });
        const pageContainer = document.createElement('div');
        pageContainer.className = 'page-container';
        pageContainer.id = `page-${pageNum}`;
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        pageContainer.appendChild(canvas);
        const pageIndicator = document.createElement('div');
        pageIndicator.className = 'page-number';
        pageIndicator.textContent = `Página ${pageNum}`;
        pageContainer.appendChild(pageIndicator);
        viewerContainer.appendChild(pageContainer);
        await page.render({ canvasContext: context, viewport: viewport }).promise;
    }
    
    // Funcionalidad de Zoom In
    zoomInBtn.addEventListener('click', async function() {
        if (currentScale >= 3.0) return; // Limitar zoom máximo
        currentScale += 0.25;
        await renderAllPages();
    });
    
    // Funcionalidad de Zoom Out
    zoomOutBtn.addEventListener('click', async function() {
        if (currentScale <= 0.5) return; // Limitar zoom mínimo
        currentScale -= 0.25;
        await renderAllPages();
    });
    
    // Funcionalidad de Pantalla Completa
    fullscreenBtn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log(`Error al intentar pantalla completa: ${err.message}`);
            });
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            }
        }
    });
    
    // Cambio de página mediante el selector
    pageSelector.addEventListener('change', function() {
        const pageNum = parseInt(this.value);
        if (pageNum) {
            const pageElement = document.getElementById(`page-${pageNum}`);
            if (pageElement) {
                pageElement.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
    
    // Botón para volver arriba
    window.addEventListener('scroll', function() {
        if (document.documentElement.scrollTop > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });
    
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Evento para detectar cambios en pantalla completa
    document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement) {
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        }
    });
        // Funcionalidad de Descargar PDF
        const downloadBtn = document.getElementById('downloadBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            const a = document.createElement('a');
            a.href = pdfUrl;
            a.download = '<?php echo basename($rutaArchivo); ?>'; 
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    // Funcionalidad de Imprimir PDF
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            const win = window.open(pdfUrl, '_blank');
            if (win) {
                win.focus();
                // Esperamos a que cargue antes de intentar imprimir
                win.onload = function () {
                    win.print();
                };
            } else {
                alert('Por favor permite las ventanas emergentes en tu navegador para imprimir el documento.');
            }
        });
    }

</script>
</body>
</html>