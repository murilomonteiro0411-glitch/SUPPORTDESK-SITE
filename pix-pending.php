<?php
require 'includes/core.php';require 'includes/pix.php';$u=require_user();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if(!$id)go('plans.php');
$stmt=db()->prepare('SELECT * FROM pix_orders WHERE id=? AND user_id=?');$stmt->execute([$id,$u['id']]);$order=$stmt->fetch();if(!$order)go('plans.php');
$notice='';$status=$order['status'];
if(pix_enabled())try{$status=pix_reconcile($order,(int)$u['id']);}catch(Throwable $e){$notice='Não foi possível verificar o status agora. Tente novamente.';}
layout_start('Aguardando Pix',$u);
?>
<div class="card auth" style="max-width:650px!important"><span class="kicker">PAGAMENTO VIA PIX</span><h1><?= $status==='approved'?'Pagamento confirmado!':'Aguardando pagamento' ?></h1>
<?php if($status==='approved'):?><div class="notice">Seu Pix foi confirmado pelo provedor e a assinatura foi ativada.</div><a class="btn" href="<?=url('user/dashboard.php')?>">Abrir painel</a>
<?php else: ?>
<p class="muted">Plano <?=h($order['plan_name'])?> · <?=money($order['amount'])?></p>
<?php if($notice):?><div class="error"><?=h($notice)?></div><?php endif;?>
<?php if(in_array($status,['rejected','cancelled','refunded'],true)):?><div class="error">Cobrança <?=h($status)?>. Gere outro Pix no checkout.</div><a class="btn" href="<?=url('plans.php')?>">Escolher plano</a>
<?php else: ?>
<img class="qr" style="width:280px;height:280px" alt="QR Code de cobrança Pix retornado pelo Mercado Pago" src="data:image/png;base64,<?=h($order['qr_base64'])?>">
<p>Leia o QR Code com seu aplicativo bancário ou copie o código Pix abaixo.</p>
<div class="copycode" id="pixcopy"><?=h($order['pix_code'])?></div>
<div class="actions"><button type="button" class="btn secondary" id="copyBtn">Copiar Pix</button><button type="button" class="btn" onclick="location.reload()">Verificar pagamento</button></div>
<div class="sd-status" id="paymentState">Aguardando confirmação do Mercado Pago. Não feche esta página antes de verificar o status.</div>
<script>document.getElementById('copyBtn').onclick=async()=>{try{await navigator.clipboard.writeText(document.getElementById('pixcopy').innerText);document.getElementById('copyBtn').textContent='Copiado!'}catch(e){alert('Selecione e copie o código manualmente.')}};setInterval(async()=>{try{const r=await fetch('pix-status.php?id=<?= (int)$id ?>',{credentials:'same-origin',cache:'no-store'});if(!r.ok)return;const d=await r.json();if(d.status==='approved')location.reload();else document.getElementById('paymentState').textContent='Status: '+d.status+' · verificando automaticamente';}catch(e){}},12000)</script>
<?php endif;endif; ?></div><?php layout_end(); ?>
