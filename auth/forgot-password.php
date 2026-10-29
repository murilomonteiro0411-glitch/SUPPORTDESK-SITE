<?php
require '../includes/core.php';require '../includes/mailer.php';
$message='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf();$email=strtolower(trim((string)($_POST['email']??'')));
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$error='Digite um e-mail válido.';}
 else{
  $message='Se existir uma conta com esse endereço, enviaremos um link para recuperação. Verifique também o spam.';
  // Proteção básica por sessão + no máximo uma mensagem por conta a cada 5 minutos.
  $now=time();if(($now-(int)($_SESSION['last_password_reset_request']??0))>=60){
   $_SESSION['last_password_reset_request']=$now;
   $pdo=db();$s=$pdo->prepare('SELECT id,name,email FROM users WHERE email=? AND is_blocked=0 LIMIT 1');$s->execute([$email]);$account=$s->fetch();
   if($account){
    $q=$pdo->prepare('SELECT created_at FROM password_reset_tokens WHERE user_id=? ORDER BY created_at DESC LIMIT 1');$q->execute([$account['id']]);$last=$q->fetchColumn();
    if(!$last || strtotime($last)<=time()-300){
     $base=defined('APP_PUBLIC_URL')?rtrim(APP_PUBLIC_URL,'/'):'';
     if(preg_match('~^https://[^/]+~i',$base) || preg_match('~^http://(?:localhost|127\.0\.0\.1)(?::[0-9]+)?(?:/|$)~i',$base)){
      $raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);
      $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id=?')->execute([$account['id']]);
      $pdo->prepare('INSERT INTO password_reset_tokens(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))')->execute([$account['id'],$hash]);
      $resetUrl=$base.'/auth/reset-password.php?token='.urlencode($raw);
      if(!send_recovery_email($account['email'],$account['name'],$resetUrl)){
       // Evita manter token válido se o provedor rejeitar o envio.
       $pdo->prepare('DELETE FROM password_reset_tokens WHERE token_hash=?')->execute([$hash]);
       error_log('SupportDesk: envio de recuperação falhou. Verificar API/MAIL_FROM/HTTPS.');
      }
     }else{error_log('SupportDesk: APP_PUBLIC_URL deve ser HTTPS público ou localhost para desenvolvimento.');}
    }
   }
  }
 }
}
layout_start('Recuperar senha');?>
<section class="auth-reset-shell"><div class="card auth auth-reset-card"><span class="kicker">SEGURANÇA DA CONTA</span><h1>Esqueceu sua senha?</h1><p class="muted">Informe o e-mail cadastrado. Enviaremos um link de recuperação válido por 30 minutos.</p>
<?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?>
<?php if($message):?><div class="notice-demo"><?=h($message)?></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><?php fld('email','Seu e-mail','email');?><button class="btn block">Enviar link de recuperação</button></form><p><a href="<?=url('auth/login.php')?>">← Voltar ao login</a></p></div></section>
<?php layout_end();?>
