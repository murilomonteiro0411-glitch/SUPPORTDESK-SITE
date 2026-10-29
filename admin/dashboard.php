<?php
require '../includes/core.php';
$u=require_user(true);$pdo=db();
$users=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$active=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND plan_expires_at>NOW()")->fetchColumn();
$tickets=(int)$pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();
$pending=(int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Aberto','Em andamento')")->fetchColumn();
$urgent=(int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE priority IN ('Urgente','Alta') AND status IN ('Aberto','Em andamento')")->fetchColumn();
$unread=unread_count($u['id']);
$filter=$_GET['filter']??'pending';
if(!in_array($filter,['pending','urgent','all'],true))$filter='pending';
$where=$filter==='urgent'?"WHERE t.priority IN ('Urgente','Alta') AND t.status IN ('Aberto','Em andamento')":($filter==='pending'?"WHERE t.status IN ('Aberto','Em andamento')":"");
$stmt=$pdo->query("SELECT t.id,t.subject,t.status,t.priority,t.category,t.created_at,us.name client,us.active_plan FROM tickets t JOIN users us ON us.id=t.user_id $where ORDER BY CASE WHEN t.status IN ('Aberto','Em andamento') THEN 0 ELSE 1 END, FIELD(t.priority,'Urgente','Alta','Média','Baixa'),t.created_at DESC LIMIT 25");
$recent=$stmt->fetchAll();
$qn=$pdo->prepare('SELECT id,title,body,link_url,created_at FROM notifications WHERE user_id=? AND read_at IS NULL ORDER BY created_at DESC LIMIT 4');$qn->execute([$u['id']]);$notices=$qn->fetchAll();
layout_start('Painel de controle',$u);
?>
<section class="control-head"><div><span class="kicker">CENTRAL DE OPERAÇÕES</span><h1>Painel de controle</h1><p class="muted">Tudo que você precisa para administrar o SupportDesk em um só lugar.</p></div><a href="<?=url('admin/tickets.php')?>" class="btn">Gerenciar chamados</a></section>
<div class="control-stats"><article class="card"><span>Chamados pendentes</span><strong><?=$pending?></strong><small>Exigem acompanhamento</small></article><article class="card"><span>Alta prioridade</span><strong><?=$urgent?></strong><small>Urgentes e alta prioridade</small></article><article class="card"><span>Assinaturas ativas</span><strong><?=$active?></strong><small>De <?=$users?> clientes cadastrados</small></article><article class="card"><span>Todos os chamados</span><strong><?=$tickets?></strong><small>Histórico geral</small></article></div>
<div class="control-layout"><div class="control-primary">
<section class="card control-panel"><div class="control-section-head"><div><h2>Fila de atendimento</h2><p class="muted">Acompanhe e abra os chamados prioritários.</p></div><a class="btn secondary" href="<?=url('admin/tickets.php')?>">Ver todos</a></div>
<div class="control-filters"><a class="<?=$filter==='pending'?'selected':''?>" href="?filter=pending">Em aberto (<?=$pending?>)</a><a class="<?=$filter==='urgent'?'selected':''?>" href="?filter=urgent">Alta prioridade (<?=$urgent?>)</a><a class="<?=$filter==='all'?'selected':''?>" href="?filter=all">Todos</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Chamado</th><th>Cliente</th><th>Plano</th><th>Prioridade</th><th>Situação</th><th></th></tr></thead><tbody>
<?php foreach($recent as $t):?><tr><td><strong><?=h($t['subject'])?></strong><br><small class="muted">#<?= (int)$t['id']?> · <?=h($t['category'])?></small></td><td><?=h($t['client'])?></td><td><?=h($t['active_plan']?:'Sem plano')?></td><td><span class="pill"><?=h($t['priority'])?></span></td><td><?=status_badge($t['status'])?></td><td><a class="btn secondary" href="<?=url('admin/ticket.php?id='.$t['id'])?>">Abrir</a></td></tr><?php endforeach;?>
<?php if(!$recent):?><tr><td colspan="6" class="muted">Nenhum chamado neste filtro.</td></tr><?php endif;?></tbody></table></div></section>
</div><aside class="control-secondary"><section class="card control-panel"><div class="control-section-head"><h2>Gerenciamento</h2></div><div class="quick-links"><a href="<?=url('admin/tickets.php')?>"><span>◎</span><div><strong>Chamados</strong><small>Prioridades e respostas</small></div><b>›</b></a><a href="<?=url('admin/users.php')?>"><span>♙</span><div><strong>Usuários e assinaturas</strong><small>Bloquear e revogar acesso</small></div><b>›</b></a><a href="<?=url('admin/payments.php')?>"><span>▤</span><div><strong>Pagamentos</strong><small>Consultar transações</small></div><b>›</b></a><a href="<?=url('admin/settings.php')?>"><span>⚙</span><div><strong>Aparência do site</strong><small>Marca, textos e cores</small></div><b>›</b></a></div></section>
<section class="card control-panel"><div class="control-section-head"><h2>Notificações</h2><span class="control-count"><?=$unread?> não lidas</span></div><div class="control-notices"><?php foreach($notices as $n):?><a href="<?=url('notification-open.php?id='.$n['id'])?>"><strong><?=h($n['title'])?></strong><small><?=h($n['body'])?></small></a><?php endforeach;?><?php if(!$notices):?><p class="muted">Tudo em dia! Não há notificações pendentes.</p><?php endif;?></div><a href="<?=url('notifications.php')?>" class="btn secondary control-full">Abrir central de avisos</a></section></aside></div>
<?php layout_end();?>