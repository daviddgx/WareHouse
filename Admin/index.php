<?php
session_start();
if (empty($_SESSION['Usuario'])) {
    header('Location: ../Innet/505.html');
    exit;
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/Sertero/LogoCBP.png">
    <title>Henkel CBP / AdminFIFO</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <link rel="stylesheet" href="../dist/css/Custom/PreLoaderStyle.css">
    <link href="../dist/css/Custom/adminContainer.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../dist/css/Custom/ConEst.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        Select {
            height: 10px !important;
        }

        .nav-tabs.nav-bordered li a.active {
            border-bottom: 2px solid #ed3131;
        }

        a {
            color: #ed3131;
            background-color: transparent;
        }

        .btn-Sertero {
            color: #fff;
            background-color: #ed3131;
            border-color: #ed3737;
        }

        .page-item.active .page-link {
            z-index: 1;
            color: #fff;
            background-color: #ed3131;
            border-color: #ed3131;
        }

        .bg-light {
            background-color: #e8eaec00 !important;
        }

        .tab-content {
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .zmdi-upload {
            padding: 0px 15px 0px 0px;
        }

        .zmdi-upload:hover {
            color: black;
            transition: color 0.2s linear 0.2s;
        }

        .file-input__label {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            font-size: 14px;
            padding: 10px 12px;
            background-color: #ff0000;
            box-shadow: 0px 0px 2px rgb(0, 0, 0);
        }

        .btn-enviar {
            color: #fff;
            font-weight: 600;
            padding: 10px 45px;
            background-color: #767676;
            border: none;
            border-radius: 2px;
        }

        .btn-enviar:hover {
            color: #b3b3b3;
        }

        .loading-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6fa;
            padding: 2rem 1rem;
        }

        .loading-skeleton {
            width: 100%;
            max-width: 1200px;
        }

        .skeleton-card {
            background: linear-gradient(90deg, #f4f6fa 25%, #e6e9ef 37%, #f4f6fa 63%);
            background-size: 400% 100%;
            animation: shimmer 1.6s infinite;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .skeleton-title,
        .skeleton-line,
        .skeleton-badge {
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            color: transparent;
            position: relative;
            overflow: hidden;
        }

        .skeleton-title::before,
        .skeleton-line::before,
        .skeleton-badge::before {
            content: "";
            position: absolute;
            top: 0;
            left: -150px;
            height: 100%;
            width: 150px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
            animation: shimmer 1.6s infinite;
        }

        .skeleton-title {
            height: 28px;
            width: 45%;
            margin-bottom: 1.5rem;
        }

        .skeleton-line {
            height: 18px;
            width: 100%;
            margin-bottom: 0.9rem;
        }

        .skeleton-badge {
            display: inline-block;
            height: 20px;
            width: 120px;
            margin-right: 0.5rem;
            margin-top: 0.5rem;
        }

        .skeleton-hint {
            display: inline-block;
            color: #c0c4d0;
            font-size: 0.95rem;
            margin-top: 0.75rem;
        }

        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }

            100% {
                background-position: 468px 0;
            }
        }
    </style>
</head>

<body>
<div id="dashboard-root" class="loading-wrapper">
    <div class="loading-skeleton">
        <div class="skeleton-card">
            <div class="skeleton-title"></div>
            <div class="skeleton-line"></div>
            <div class="skeleton-line" style="width: 70%"></div>
            <span class="skeleton-hint">Espere...</span>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-title"></div>
            <div class="skeleton-line"></div>
            <div class="skeleton-line" style="width: 80%"></div>
            <div class="skeleton-line" style="width: 60%"></div>
            <span class="skeleton-hint">Espere...</span>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-title"></div>
            <div class="skeleton-line"></div>
            <div class="skeleton-line" style="width: 65%"></div>
            <div class="skeleton-line" style="width: 85%"></div>
            <span class="skeleton-hint">Espere...</span>
        </div>
    </div>
</div>

<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../dist/js/app-style-switcher.js"></script>
<script src="../dist/js/feather.min.js"></script>
<script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="../dist/js/sidebarmenu.js"></script>
<script src="../dist/js/custom.min.js"></script>
<script src="../assets/extra-libs/c3/d3.min.js"></script>
<script src="../assets/extra-libs/c3/c3.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
<script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>
<script src="../dist/js/OnLine.js"></script>
<script src="../assets/libs/chart.js/dist/Chart.min.js"></script>
<script>
    const dashboardRoot = document.getElementById('dashboard-root');

    function showSkeleton() {
        dashboardRoot.classList.add('loading-wrapper');
        dashboardRoot.innerHTML = `
            <div class="loading-skeleton">
                <div class="skeleton-card">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line" style="width: 70%"></div>
                    <span class="skeleton-hint">Espere...</span>
                </div>
                <div class="skeleton-card">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line" style="width: 80%"></div>
                    <div class="skeleton-line" style="width: 60%"></div>
                    <span class="skeleton-hint">Espere...</span>
                </div>
                <div class="skeleton-card">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line" style="width: 65%"></div>
                    <div class="skeleton-line" style="width: 85%"></div>
                    <span class="skeleton-hint">Espere...</span>
                </div>
            </div>`;
    }

    function executeInlineScripts(scriptContents) {
        document.querySelectorAll('script[data-dynamic-script]').forEach(function (script) {
            script.remove();
        });

        scriptContents.forEach(function (content) {
            const dynamicScript = document.createElement('script');
            dynamicScript.type = 'text/javascript';
            dynamicScript.setAttribute('data-dynamic-script', 'true');
            dynamicScript.text = content;
            document.body.appendChild(dynamicScript);
        });
    }

    function attachFormHandler() {
        const forms = dashboardRoot.querySelectorAll('form');
        if (!forms.length) {
            return;
        }
        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(form);
                const submitButton = form.querySelector('[name="accion"]');
                if (submitButton && !formData.has('accion')) {
                    formData.append('accion', submitButton.value);
                }
                loadDashboardData(formData);
            });
        });
    }

    function loadDashboardData(formData) {
        showSkeleton();
        const fetchOptions = formData ? { method: 'POST', body: formData } : { method: 'GET' };

        fetch('index-data.php', fetchOptions)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Error al obtener los datos');
                }
                return response.text();
            })
            .then(function (html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const mainWrapper = doc.getElementById('main-wrapper');
                if (!mainWrapper) {
                    throw new Error('No se encontró el contenido principal en la respuesta.');
                }

                const scripts = Array.from(doc.querySelectorAll('script'));
                const inlineScripts = scripts.filter(function (script) {
                    return !script.src;
                }).map(function (script) {
                    return script.textContent;
                });

                scripts.forEach(function (script) {
                    if (script.parentNode) {
                        script.parentNode.removeChild(script);
                    }
                });

                dashboardRoot.classList.remove('loading-wrapper');
                dashboardRoot.innerHTML = '';
                dashboardRoot.appendChild(mainWrapper);

                executeInlineScripts(inlineScripts);
                attachFormHandler();
            })
            .catch(function (error) {
                console.error(error);
                dashboardRoot.innerHTML = '<div class="skeleton-card" style="text-align:center">' +
                    '<strong style="color:#a0a4b8">No se pudo cargar la información. Inténtelo de nuevo.</strong>' +
                    '<div class="skeleton-hint">Espere...</div>' +
                    '</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardData();
    });
</script>
</body>

</html>
