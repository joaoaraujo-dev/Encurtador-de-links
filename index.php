<?php
require "database/connect.php";

function getIP() {
    return $_SERVER["REMOTE_ADDR"];
}

function getUserAgent() {
    return $_SERVER["HTTP_USER_AGENT"];
}

function generateShortcode() {
    return substr(md5(mt_rand()),6, 7);
}

$redirect = trim($_GET["redirect"]) ?? null;


//script de redirecionamento
if (isset($redirect)) {
   $sql = "SELECT original_url FROM links WHERE shortcode = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$redirect]);
   
   $url = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($url) {
      $redirect_from = $url["original_url"];
      header("Location: " . $redirect_from); 
      exit;
   }
}

//script responsavel por encurtar o link
if ($_SERVER["REQUEST_METHOD"] === "POST") {
   $url = trim($_POST["url"]);
   $shortcode = generateShortcode();

   if (filter_var($url, FILTER_VALIDATE_URL)) {

      try {
        $sql = "INSERT INTO links(original_url, shortcode, creator_ip, user_agent) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$url, $shortcode, getIP(), getUserAgent()]);

        $message = "Sucesso! Seu link foi encurtado.";
      } catch (PDOException $e) {
        echo $e->getMessage();
      }

   }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Encurtador de links</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet" />

    <!-- Folha de estilo principal -->
    <link rel="stylesheet" href="Assets/css/styles.css">
</head>
<body>

    <header class="navbar navbar-expand-lg fixed-top navbar-glass py-3">
        <div class="container">
            <a class="navbar-brand font-headline fw-bolder fs-4" href="#">Encurtador</a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-active fw-bold" href="#">RECURSOS</a>
                    </li>
                </ul>
                <button class="btn btn-shorten rounded-pill px-4 py-2 fw-bold" type="button"><a href="#start" class="nav-link">Começar</a></button>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-section position-relative overflow-hidden">
            <div class="container position-relative z-1 text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <h1 class="display-3 fw-bolder font-headline mb-4 text-dark">
                            Arquitete sua <span class="text-primary-shorten">Presença Digital</span>.
                        </h1>
                        <p class="fs-5 mb-5 mx-auto text-secondary-shorten">
                            Links curtos, limpos e rápidos. A estrutura que você precisa para compartilhar conteúdo com eficiência.
                        </p>
                        
                        <div class="row justify-content-center">
                            <div class="col-lg-10">

                                <form class="hero-input-wrapper d-flex flex-column flex-md-row align-items-stretch" method="POST">
                                    <div class="d-flex align-items-center flex-grow-1 px-3 py-2">
                                        <span class="material-symbols-outlined text-muted me-3 fs-4">link</span>
                                        <input type="url" name="url" class="form-control" placeholder="Cole sua URL longa aqui..." required>
                                    </div>
                                    <button type="submit" class="btn btn-shorten rounded-3 px-5 py-3 fw-bold fs-5 mt-2 mt-md-0 d-flex align-items-center justify-content-center">
                                        Encurtar <span class="material-symbols-outlined ms-2">bolt</span>
                                    </button>
                                </form>
                                
                            </div>
                        </div>

                        <?php if (!empty($message)): ?>
                            <div class="mt-4 p-4 border rounded-3 bg-white shadow-sm text-start">
                                <div class="d-flex flex-column gap-1">
                                    <span class="text-uppercase fw-bold text-primary" style="font-size: 0.75rem; letter-spacing: 0.15em;">
                                        LINK GERADO
                                    </span>
                                    
                                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span id="shortLink" class="fs-3 fw-bold text-dark font-headline">
                                                <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/Encurtador de links/<?= $shortcode ?>
                                            </span>
                                            <span class="material-symbols-outlined text-primary fs-4" style="font-variation-settings: 'FILL' 1;">
                                                verified
                                            </span>
                                        </div>

                                        <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2 px-3 py-2 rounded-pill fw-bold" 
                                                onclick="copyToClipboard()">
                                            <span class="material-symbols-outlined fs-5">content_copy</span>
                                            <span id="copyText">Copiar</span> </button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function copyToClipboard() {
                                    const url = document.getElementById('shortLink').innerText;
                                    const btnCopy = document.getElementById('copyText');

                                    if (navigator.clipboard && window.isSecureContext) {
                                        navigator.clipboard.writeText(url).then(() => {
                                            feedbackCopy(btnCopy);
                                        });
                                    } else {
                                        const textArea = document.createElement("textarea");
                                        textArea.value = url;
                                        document.body.appendChild(textArea);
                                        textArea.select();
                                        document.execCommand('copy');
                                        document.body.removeChild(textArea);
                                        feedbackCopy(btnCopy);
                                    }
                                }

                                function feedbackCopy(element) {
                                    const original = element.innerText;
                                    element.innerText = 'Copiado!';
                                    
                                    setTimeout(() => {
                                        element.innerText = original;
                                    }, 2000);
                                }
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section id="start" class="py-5 bg-surface">
            <div class="container py-5">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bolder font-headline mb-3 text-dark">A Tríade da Permanência</h2>
                    <p class="fs-5 text-secondary-shorten">O encurtador definitivo para quem busca um digital limpo e eficiente</p>
                </div>
                
                <div class="row g-4 pt-3">
                    <div class="col-md-4">
                        <div class="shorten-card h-100 p-5">
                            <div class="icon-box shadow-sm mb-4">
                                <span class="material-symbols-outlined fs-2">content_paste</span>
                            </div>
                            <h3 class="fw-bold font-headline mb-3 text-dark">01. Colar</h3>
                            <p class="text-secondary-shorten">Insira sua URL complexa na interface. Removemos o excesso e focamos na essência.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="shorten-card h-100 p-5">
                            <div class="icon-box shadow-sm mb-4">
                                <span class="material-symbols-outlined fs-2">architecture</span>
                            </div>
                            <h3 class="fw-bold font-headline mb-3 text-dark">02. Encurtar</h3>
                            <p class="text-secondary-shorten">Nossa arquitetura comprime seu link o deixando mais elegante, garantindo velocidade.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="shorten-card h-100 p-5">
                            <div class="icon-box shadow-sm mb-4">
                                <span class="material-symbols-outlined fs-2">rocket_launch</span>
                            </div>
                            <h3 class="fw-bold font-headline mb-3 text-dark">03. Compartilhar</h3>
                            <p class="text-secondary-shorten">Distribua seus links com confiança.confiável e esteticamente bonito.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dark-section py-5">
            <div class="container py-5">
                <div class="row align-items-center gx-5">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <div class="img-shorten-container shadow-lg">
                            <img alt="Arquitetura Moderna" src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1000&auto=format&fit=crop" />
                            <div class="image-overlay"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 ps-lg-5">
                        <h2 class="display-5 fw-bolder font-headline mb-4">Construído para Permanência</h2>
                        <p class="fs-5 mb-5 text-accent-shorten">Encurte seus links e facilite o compartilhamento. Simples, rápido e feito para nunca deixar você na mão.</p>
                        
                        <div class="d-flex align-items-center gap-3">
                            <div class="divider"></div>
                            <span class="fw-bolder small tracking-widest text-accent-shorten">ENCURTADOR</span>
                            <div class="divider"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-shorten py-5 border-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <div class="fs-5 fw-bold font-headline mb-1 text-dark">Encurtador</div>
                    <div class="small text-secondary-shorten">© 2026 Encurtador</div>
                </div>
                <div class="col-md-6">
                    <ul class="nav justify-content-center justify-content-md-end gap-4">
                        <li class="nav-item">
                            <a class="nav-link footer-link p-0 text-uppercase fw-semibold" href="#">Política de Privacidade</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link footer-link p-0 text-uppercase fw-semibold" href="#">Termos de Serviço</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>