<?php
// Necessário configurar RESEND_API_KEY, MAIL_FROM e APP_PUBLIC_URL no config.php.
function send_recovery_email($recipient,$name,$link) {
    if (!defined('RESEND_API_KEY') || !RESEND_API_KEY || !defined('MAIL_FROM') || !MAIL_FROM || !function_exists('curl_init')) return false;
    $safeName=htmlspecialchars((string)$name,ENT_QUOTES,'UTF-8');
    $safeLink=htmlspecialchars($link,ENT_QUOTES,'UTF-8');
    $html='<div style="font-family:Arial,sans-serif;background:#081325;padding:32px;color:#eaf1ff"><div style="max-width:540px;margin:auto;background:#102340;border:1px solid #234267;border-radius:20px;padding:30px"><p style="color:#66abff;font-weight:bold">SUPPORTDESK</p><h1 style="color:white">Redefina sua senha</h1><p>Olá, '.$safeName.'. Recebemos uma solicitação para recuperar sua conta.</p><p><a style="display:inline-block;background:#1877ff;color:white;text-decoration:none;padding:15px 22px;border-radius:10px;font-weight:bold" href="'.$safeLink.'">Criar nova senha</a></p><p>O link expira em 30 minutos e só pode ser utilizado uma vez.</p><p style="font-size:13px;color:#acbfdc">Se você não solicitou a alteração, ignore este e-mail.</p></div></div>';
    $body=json_encode(['from'=>MAIL_FROM,'to'=>[$recipient],'subject'=>'Recuperação de senha · SupportDesk','html'=>$html],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $ch=curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.RESEND_API_KEY,'Content-Type: application/json','User-Agent: SupportDesk/1.0'],CURLOPT_POSTFIELDS=>$body,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15]);
    $response=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    return $response!==false && $status>=200 && $status<300;
}
