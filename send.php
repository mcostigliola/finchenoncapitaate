<?php
$method = $_SERVER['REQUEST_METHOD'] ?? '';

function wants_json(): bool
{
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  $requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  return str_contains($accept, 'application/json') || strtolower($requested_with) === 'xmlhttprequest';
}

function render_page(int $status_code, string $title, string $message, bool $success): void
{
  http_response_code($status_code);
  header('Content-Type: text/html; charset=UTF-8');

  $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $safe_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
  $page_title = $safe_title . ' | Finché non capita a te';
  $badge_class = $success ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
  $badge_text = $success ? 'OK' : 'KO';
  $cta_href = 'index.html#contatti';

  echo <<<HTML
<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{$page_title}</title>
    <meta name="robots" content="noindex"/>
    <link rel="icon" href="assets/img/brand/favicon.ico" sizes="any"/>
    <link rel="icon" type="image/png" href="assets/img/brand/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png" href="assets/img/brand/favicon-16x16.png" sizes="16x16"/>
    <link rel="apple-touch-icon" href="assets/img/brand/apple-touch-icon.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="assets/css/site.css"/>
  </head>
  <body class="is-loading page-sticky">
    <div class="preloader" id="preloader" aria-hidden="true">
      <img src="assets/img/logo.webp" alt=""/>
    </div>
    <div class="page-content">
      <nav class="navbar navbar-expand-lg border-bottom sticky-top site-navbar">
        <div class="container py-2">
          <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="index.html">
            <img src="assets/img/logo.webp" alt="Finché non capita a te – logo" width="100" height="100" class="rounded-circle"/>
            <span>Finché non capita a te</span>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto gap-lg-1 align-items-lg-center">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="index.html#cosa-facciamo" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Cosa facciamo
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="index.html#eventi">Eventi</a></li>
                  <li><a class="dropdown-item" href="index.html#teatro">Teatro</a></li>
                  <li><a class="dropdown-item" href="index.html#convenzioni">Convenzioni</a></li>
                  <li><a class="dropdown-item" href="index.html#video">Video</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.html#chi-siamo">Chi siamo</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="news.html">News</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.html#contatti">Contatti</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="diventa-volontario.html">Volontari</a>
              </li>
              <li class="nav-item ms-lg-2">
                <a class="btn btn-outline-secondary" href="index.html#sostienici">Sostienici</a>
              </li>
              <li class="nav-item">
                <a class="btn btn-primary" href="index.html#contatti">Chiedi aiuto</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>

      <main class="container py-5">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="p-4 p-lg-5 bg-white shadow-sm rounded-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle {$badge_class}" style="width:56px;height:56px;font-weight:700;">
                  {$badge_text}
                </div>
                <div>
                  <h1 class="h3 mb-1">{$safe_title}</h1>
                  <p class="mb-0 text-muted">{$safe_message}</p>
                </div>
              </div>
              <a class="btn btn-primary" href="{$cta_href}">Torna ai contatti</a>
            </div>
          </div>
        </div>
      </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/site.js"></script>
  </body>
</html>
HTML;
  exit;
}

function respond(int $status_code, string $title, string $message, bool $success): void
{
  if (wants_json()) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
      ['success' => $success, 'message' => $message],
      JSON_UNESCAPED_UNICODE
    );
    exit;
  }

  render_page($status_code, $title, $message, $success);
}

if ($method === 'GET') {
  respond(200, 'Invio non eseguito', 'Torna al modulo contatti e compila i campi richiesti.', false);
}

if ($method !== 'POST') {
  respond(405, 'Metodo non consentito', 'Metodo non consentito.', false);
}

$name = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW));
$email = trim((string) filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$message = trim((string) filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW));
$privacy_consent = filter_input(INPUT_POST, 'privacyConsent', FILTER_DEFAULT);

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $privacy_consent !== 'on') {
  respond(400, 'Invio non riuscito', 'Dati non validi. Controlla i campi e riprova.', false);
}

$to = 'info@finchenoncapitaate.it';
$subject = 'Nuovo contatto dal sito';

$plain_message = "Nome: {$name}\nEmail: {$email}\n\nMessaggio:\n{$message}\n";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/plain; charset=UTF-8';
$headers[] = 'From: Finche non capita a te <info@finchenoncapitaate.it>';
$safe_reply_to = str_replace(["\r", "\n"], '', $email);
$headers[] = 'Reply-To: ' . $safe_reply_to;
$headers[] = 'X-Content-Type-Options: nosniff';

$mail_sent = mail($to, $subject, $plain_message, implode("\r\n", $headers));

$reply_subject = 'Abbiamo ricevuto il tuo messaggio';
$reply_html = '<!doctype html>'
  . '<html lang="it"><head><meta charset="utf-8"/></head>'
  . '<body style="margin:0;padding:0;background:#f5f6f7;font-family:Arial,Helvetica,sans-serif;color:#1f2a37;">'
  . '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f5f6f7;padding:24px 0;">'
  . '<tr><td align="center">'
  . '<table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.06);">'
  . '<tr><td style="padding:24px 24px 0;">'
  . '<img src="https://www.finchenoncapitaate.it/assets/img/logo.webp" alt="Finche non capita a te" width="90" height="90" style="display:block;border-radius:45px;"/>'
  . '</td></tr>'
  . '<tr><td style="padding:16px 24px 24px;">'
  . '<h1 style="margin:0 0 12px;font-size:20px;">Ciao ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h1>'
  . '<p style="margin:0 0 12px;line-height:1.5;">Grazie per averci scritto. Abbiamo ricevuto il tuo messaggio e ti risponderemo al piu presto.</p>'
  . '<p style="margin:0;line-height:1.5;">Nel frattempo, se sei in pericolo, chiama subito il 112 o il 1522.</p>'
  . '<div style="margin-top:20px;font-size:13px;color:#6b7280;">Finche non capita a te - Bologna</div>'
  . '</td></tr>'
  . '</table>'
  . '</td></tr>'
  . '</table>'
  . '</body></html>';

$reply_headers = [];
$reply_headers[] = 'MIME-Version: 1.0';
$reply_headers[] = 'Content-type: text/html; charset=UTF-8';
$reply_headers[] = 'From: Finche non capita a te <info@finchenoncapitaate.it>';
$reply_headers[] = 'X-Content-Type-Options: nosniff';

$reply_sent = mail($email, $reply_subject, $reply_html, implode("\r\n", $reply_headers));

if (!$mail_sent || !$reply_sent) {
  respond(500, 'Invio non riuscito', 'Si e verificato un problema durante l\'invio. Riprova piu tardi.', false);
}

respond(200, 'Messaggio inviato', 'Il messaggio e stato correttamente inviato. Ti risponderemo al piu presto.', true);
