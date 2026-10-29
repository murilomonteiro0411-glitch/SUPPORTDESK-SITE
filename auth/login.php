<?php
require '../includes/core.php';
if(me())go(me()['role']==='admin'?'admin/dashboard.php':'user/dashboard.php');
$error='';
$pending=$_SESSION['login_confirmation']??null;
if($pending && (!isset($pending['expires'])||$pending['expires']<time())){unset($_SESSION['login_confirmation']);$pending=null;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf();$action=(string)($_POST['action']??'login');
 if($action==='cancel'){
  unset($_SESSION['login_confirmation']);go('auth/login.php');
 }elseif($action==='confirm'){
  if($pending && hash_equals((string)$pending['nonce'],(string)($_POST['nonce']??''))){
   $s=db()->prepare('SELECT id,role,is_blocked,password FROM users WHERE id=?');$s->execute([(int)$pending['user_id']]);$account=$s->fetch();
   if($account && !$account['is_blocked'] && hash_equals((string)$pending['password_fingerprint'],hash('sha256',$account['password']))){
    session_regenerate_id(true);$_SESSION['uid']=(int)$account['id'];unset($_SESSION['login_confirmation']);go($account['role']==='admin'?'admin/dashboard.php':'user/dashboard.php');
   }
  }
  unset($_SESSION['login_confirmation']);$pending=null;$error='Confirmação expirada. Entre novamente.';
 }else{
  unset($_SESSION['login_confirmation']);$pending=null;
  $email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');
  $s=db()->prepare('SELECT id,name,email,password,role,is_blocked,avatar_file FROM users WHERE email=? LIMIT 1');$s->execute([$email]);$account=$s->fetch();
  if($account && !$account['is_blocked'] && password_verify($password,$account['password'])){
    session_regenerate_id(true);
    $_SESSION['login_confirmation']=['user_id'=>(int)$account['id'],'nonce'=>bin2hex(random_bytes(24)),'password_fingerprint'=>hash('sha256',$account['password']),'expires'=>time()+180];
    $pending=$_SESSION['login_confirmation'];
  }else $error='E-mail ou senha inválidos.';
 }
}
$preview=null;
if($pending){$s=db()->prepare('SELECT id,name,email,avatar_file,role,is_blocked FROM users WHERE id=? LIMIT 1');$s->execute([(int)$pending['user_id']]);$preview=$s->fetch();if(!$preview || $preview['is_blocked']){unset($_SESSION['login_confirmation']);$pending=null;$preview=null;}}
layout_start($preview?'Confirmar identidade':'Entrar');
?>
<section class="identity-login-wrap">
<?php if($preview):?>
<div class="card identity-card">
<span class="kicker">ÚLTIMA ETAPA</span><h1>Essa é sua conta?</h1><p class="muted">Confirme o perfil antes de acessar seu painel.</p>
<div class="identity-profile">
<div class="identity-avatar">
<?php if(!empty($preview['avatar_file']) && preg_match('/^[a-f0-9]{32}\\.(jpg|png|webp)$/',$preview['avatar_file'])):?>
<img src="<?=h(url('uploads/avatars/'.$preview['avatar_file']))?>" alt="Foto do perfil">
<?php else:?><?=h(mb_strtoupper(mb_substr($preview['name'],0,1)))?><?php endif;?>
</div>
<strong><?=h($preview['name'])?></strong><span><?=h($preview['email'])?></span>
<small><?= $preview['role']==='admin'?'Administrador':'Cliente SupportDesk' ?></small>
</div>
<form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="confirm"><input type="hidden" name="nonce" value="<?=h($pending['nonce'])?>"><button class="btn block" type="submit">Sim, essa é minha conta →</button></form>
<form method="post" class="identity-secondary"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="cancel"><button class="btn secondary block" type="submit">Não, trocar de conta</button></form>
<p class="identity-hint">Para sua segurança, a confirmação expira em 3 minutos.</p>
</div>
<?php else:?>
<div class="card auth identity-login"><span class="kicker">BEM-VINDO DE VOLTA</span><h1>Entrar na conta</h1><p class="muted">Acesse seu painel com segurança.</p>
<?php if($error):?><div class="error" role="alert"><?=h($error)?></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="login"><?php fld('email','E-mail','email');fld('password','Senha','password');?><button class="btn block">Continuar →</button></form>
<p class="auth-forgot"><a href="forgot-password.php">Esqueceu sua senha?</a></p><p class="muted">Não tem conta? <a href="register.php">Cadastre-se</a></p></div>
<?php endif; ?>
</section>
<?php layout_end(); ?>
