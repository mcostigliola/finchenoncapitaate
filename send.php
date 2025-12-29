<?php
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'GET') {
  http_response_code(200);
  echo 'Invio non eseguito. Torna al modulo contatti e compila i campi richiesti.';
  exit;
}
if ($method !== 'POST') {
  http_response_code(405);
  echo 'Metodo non consentito.';
  exit;
}

$name = trim((string) filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$email = trim((string) filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$message = trim((string) filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$privacy_consent = filter_input(INPUT_POST, 'privacyConsent', FILTER_DEFAULT);

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $privacy_consent !== 'on') {
  http_response_code(400);
  echo 'Dati non validi. Torna indietro e controlla i campi.';
  exit;
}

$to = 'info@finchenoncapitaate.it';
$subject = 'Nuovo contatto dal sito';

$plain_message = "Nome: {$name}\nEmail: {$email}\n\nMessaggio:\n{$message}\n";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/plain; charset=UTF-8';
$headers[] = 'From: Finche non capita a te <info@finchenoncapitaate.it>';
$headers[] = 'Reply-To: ' . $email;
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
  http_response_code(500);
  echo 'Si e verificato un problema durante l\'invio. Riprova piu tardi.';
  exit;
}

header('Location: index.html#contatti');
exit;
?>
