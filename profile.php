<?php
require 'includes/core.php';
$u=require_logged_in(); $pdo=db(); $error='';
$q=$pdo->prepare('SELECT name,email,password,avatar_file,role,active_plan FROM users WHERE id=?');$q->execute([$u['id']]);$profile=$q->fetch();
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf(); $action=$_POST['action']??'';
 if($action==='details'){
  $name=trim((string)($_POST['name']??'')); $email=strtolower(trim((string)($_POST['email']??'')));
  if(mb_strlen($name)<2||mb_strlen($name)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>160)$error='Informe nome e e-mail válidos.';
  else {try{$stmt=$pdo->prepare('UPDATE users SET name=?,email=? WHERE id=?');$stmt->execute([$name,$email,$u['id']]);notify('Dados do perfil atualizados.');go('profile.php');}catch(PDOException $e){$error='Esse e-mail já está em uso.';}}
 }elseif($action==='password'){
  $old=(string)($_POST['current_password']??'');$new=(string)($_POST['new_password']??'');$confirm=(string)($_POST['confirm_password']??'');
  if(!password_verify($old,$profile['password']))$error='Senha atual incorreta.';
  elseif(strlen($new)<10||strlen($new)>128)$error='Use uma senha entre 10 e 128 caracteres.';
  elseif($new!==$confirm)$error='As senhas novas são diferentes.';
  else{$stmt=$pdo->prepare('UPDATE users SET password=? WHERE id=?');$stmt->execute([password_hash($new,PASSWORD_DEFAULT),$u['id']]);session_regenerate_id(true);notify('Senha alterada com sucesso.');go('profile.php');}
 }elseif($action==='avatar'){
  $file=$_FILES['avatar']??null;
  if(!$file||$file['error']!==UPLOAD_ERR_OK)$error='Selecione uma foto válida.';
  elseif($file['size']>2*1024*1024)$error='A foto deve ter no máximo 2 MB.';
  elseif(!is_uploaded_file($file['tmp_name']))$error='Envio inválido.';
  else {
   $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
   $map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
   $info=@getimagesize($file['tmp_name']);
   if(!isset($map[$mime])||!$info||$info[0]>4000||$info[1]>4000)$error='Envie uma imagem JPG, PNG ou WebP válida (até 4000px).';
   else {
    $dir=__DIR__.'/uploads/avatars';if(!is_dir($dir)&&!mkdir($dir,0755,true))$error='Não foi possível criar a pasta de fotos.';
    if(!$error){$newFile=bin2hex(random_bytes(16)).'.'.$map[$mime];$dest=$dir.'/'.$newFile;
     if(!move_uploaded_file($file['tmp_name'],$dest))$error='Não foi possível salvar a imagem.';
     else {try{$stmt=$pdo->prepare('UPDATE users SET avatar_file=? WHERE id=?');$stmt->execute([$newFile,$u['id']]);
       if(!empty($profile['avatar_file'])){$oldFile=$dir.'/'.$profile['avatar_file'];if(is_file($oldFile))@unlink($oldFile);}
       notify('Foto de perfil atualizada.');go('profile.php');
      }catch(Throwable $e){@unlink($dest);$error='Erro ao atualizar sua foto.';}}
    }
   }
  }
 }elseif($action==='remove_avatar'){
  $pdo->prepare('UPDATE users SET avatar_file=NULL WHERE id=?')->execute([$u['id']]);
  if(!empty($profile['avatar_file'])){$oldFile=__DIR__.'/uploads/avatars/'.$profile['avatar_file'];if(is_file($oldFile))@unlink($oldFile);}
  notify('Foto removida.');go('profile.php');
 }
}
$avatar=!empty($profile['avatar_file'])?url('uploads/avatars/'.$profile['avatar_file']):null;
layout_start('Meu perfil',$u);
?>
<section class="profile-hero"><div class="profile-avatar-large"><?php if($avatar):?><img src="<?=h($avatar)?>" alt="Sua foto de perfil"><?php else:?><?=h(mb_strtoupper(mb_substr($profile['name'],0,1)))?><?php endif;?></div><div><span class="kicker">MINHA CONTA</span><h1><?=h($profile['name'])?></h1><p><?=h($profile['email'])?></p><span class="profile-role"><?= $profile['role']==='admin'?'Administrador':h($profile['active_plan']?:'Cliente') ?></span></div></section>
<?php if($error):?><div class="error" role="alert"><?=h($error)?></div><?php endif;?>
<div class="profile-grid">
<section class="card profile-card"><h2>Dados pessoais</h2><p class="muted">Atualize as informações da sua conta.</p><form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="details"><label class="field"><span>Nome completo</span><input name="name" maxlength="120" required value="<?=h($profile['name'])?>"></label><label class="field"><span>E-mail</span><input type="email" name="email" maxlength="160" required value="<?=h($profile['email'])?>"></label><button class="btn">Salvar alterações</button></form></section>
<section class="card profile-card"><h2>Foto de perfil</h2><p class="muted">JPG, PNG ou WebP, até 2 MB.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="avatar"><label class="profile-file"><span>Selecionar imagem</span><input id="avatarInput" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" required></label><img id="avatarPreview" class="profile-preview" alt="Prévia da nova foto" hidden><button class="btn">Atualizar foto</button></form><?php if($avatar):?><form method="post" style="margin-top:12px"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="remove_avatar"><button class="btn secondary">Remover foto</button></form><?php endif;?></section>
<section class="card profile-card"><h2>Segurança</h2><p class="muted">Troque sua senha sem sair da conta.</p><form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><input type="hidden" name="action" value="password"><label class="field"><span>Senha atual</span><input type="password" name="current_password" autocomplete="current-password" required></label><label class="field"><span>Nova senha (mínimo 10 caracteres)</span><input type="password" name="new_password" minlength="10" maxlength="128" autocomplete="new-password" required></label><label class="field"><span>Confirmar nova senha</span><input type="password" name="confirm_password" minlength="10" maxlength="128" autocomplete="new-password" required></label><button class="btn">Alterar senha</button></form></section>
<section class="card profile-card profile-info"><h2>Sua área</h2><p class="muted">Acesse seu painel e gerencie seus atendimentos.</p><a class="btn block" href="<?=url($u['role']==='admin'?'admin/dashboard.php':'user/dashboard.php')?>">Voltar ao painel</a><a class="btn secondary block" href="<?=url('notifications.php')?>">Minhas notificações</a></section>
</div>
<script>document.getElementById('avatarInput')?.addEventListener('change',function(){let f=this.files[0],img=document.getElementById('avatarPreview');if(!f){img.hidden=true;return;}let url=URL.createObjectURL(f);img.src=url;img.hidden=false;img.onload=()=>URL.revokeObjectURL(url);});</script>
<?php layout_end(); ?>
