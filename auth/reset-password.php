<?php
require '../includes/core.php';
$raw=(string)($_POST['token']??$_GET['token']??'');
$validToken=(bool)preg_match('/^[a-f0-9]{64}$/D',$raw);
$record=null;$error='';$ok=false;
if($validToken){$s=db()->prepare('SELECT r.id,r.user_id FROM password_reset_tokens r JOIN users u ON u.id=r.user_id WHERE r.token_hash=? AND r.used_at IS NULL AND r.expires_at>NOW() AND u.is_blocked=0 LIMIT 1');$s->execute([hash('sha256',$raw)]);$record=$s->fetch();}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf();$pw=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm']??'');
 if(!$record)$error='Este link expirou ou já foi utilizado. Solicite um novo.';
 elseif(strlen($pw)<12)$error='Sua senha precisa ter pelo menos 12 caracteres.';
 elseif($pw!==$confirm)$error='As senhas digitadas não são iguais.';
 else{
  $pdo=db();try{
   $pdo->beginTransaction();
   $q=$pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE id=? AND used_at IS NULL AND expires_at>NOW()');$q->execute([$record['id']]);
   if($q->rowCount()!==1)throw new RuntimeException('Token já usado');
   $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$record['user_id']]);
   $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id=? AND id<>?')->execute([$record['user_id'],$record['id']]);
   $pdo->commit();$ok=true;unset($_SESSION['uid']);
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Não foi possível alterar a senha. Solicite outro link.';}
 }
}
layout_start('Nova senha');?>
<section class="auth-reset-shell"><div class="card auth auth-reset-card"><span class="kicker">RECUPERAÇÃO DE CONTA</span><h1>Crie uma nova senha</h1>
<?php if($ok):?><div class="notice-demo">Senha alterada com sucesso. Você já pode entrar novamente.</div><a class="btn block" href="<?=url('auth/login.php')?>">Entrar na conta</a>
<?php elseif(!$record):?><div class="error">Link inválido, expirado ou já utilizado.</div><a class="btn block" href="<?=url('auth/forgot-password.php')?>">Solicitar novo link</a>
<?php else:?><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><p class="muted">Escolha uma senha de pelo menos 12 caracteres.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="token" value="<?=h($raw)?>"><?php fld('password','Nova senha','password');?><label class="field"><span>Confirme a nova senha</span><input type="password" name="confirm" required minlength="12" autocomplete="new-password"></label><button class="btn block">Atualizar senha</button></form>
<?php endif;?></div></section><?php layout_end();?>
