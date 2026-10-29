<?php
// Optional Mercado Pago Pix integration: do not expose access token to browsers.
function pix_access_token(){return trim((string)(getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN')?MP_ACCESS_TOKEN:'')));}
function pix_enabled(){return pix_access_token()!=='';}
function pix_request($method,$route,$body=null,$idempotency=null){
 if(!extension_loaded('curl'))throw new RuntimeException('Ative a extensão curl do PHP no XAMPP.');
 $ch=curl_init('https://api.mercadopago.com'.$route);
 $headers=['Authorization: Bearer '.pix_access_token(),'Content-Type: application/json','Accept: application/json'];
 if($idempotency)$headers[]='X-Idempotency-Key: '.$idempotency;
 curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>20,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true]);
 if($body!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
 $response=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
 if($response===false)throw new RuntimeException('Erro de conexão com o provedor: '.$err);
 $data=json_decode($response,true);
 if($http<200||$http>=300||!is_array($data))throw new RuntimeException('O provedor não aceitou a solicitação (HTTP '.$http.'). Confira suas credenciais e os dados de cobrança.');
 return $data;
}
function pix_reconcile($order,$userId){
 if(!pix_enabled() || !$order || (int)$order['user_id']!==$userId)return 'invalid';
 if($order['status']==='approved')return 'approved';
 $response=pix_request('GET','/v1/payments/'.rawurlencode($order['provider_id']));
 if((string)($response['id']??'')!==$order['provider_id'] || (string)($response['external_reference']??'')!==$order['reference'] || ($response['payment_method_id']??'')!=='pix' || abs((float)($response['transaction_amount']??0)-(float)$order['amount'])>0.001 || ($response['currency_id']??'BRL')!=='BRL')throw new RuntimeException('Inconsistência na confirmação do pagamento.');
 $status=(string)($response['status']??'pending');
 if($status!=='approved'){if(in_array($status,['rejected','cancelled','refunded'],true))db()->prepare('UPDATE pix_orders SET status=? WHERE id=?')->execute([$status,$order['id']]);return $status;}
 $pdo=db();$pdo->beginTransaction();
 try{
  $q=$pdo->prepare('SELECT * FROM pix_orders WHERE id=? FOR UPDATE');$q->execute([$order['id']]);$locked=$q->fetch();
  if($locked['status']==='approved'){$pdo->commit();return 'approved';}
  $pdo->prepare("UPDATE subscriptions SET status='expired' WHERE user_id=? AND status='active'")->execute([$userId]);
  $s=$pdo->prepare("INSERT INTO subscriptions(user_id,plan_name,amount,status,starts_at,expires_at) VALUES(?,?,?,'active',NOW(),DATE_ADD(NOW(),INTERVAL 30 DAY))");$s->execute([$userId,$locked['plan_name'],$locked['amount']]);$sid=$pdo->lastInsertId();
  $pdo->prepare('UPDATE users SET active_plan=?,plan_expires_at=DATE_ADD(NOW(),INTERVAL 30 DAY) WHERE id=?')->execute([$locked['plan_name'],$userId]);
  $pdo->prepare("INSERT INTO payments(user_id,subscription_id,plan_name,amount,method,status,transaction_code) VALUES(?,?,?,?,'PIX','Aprovado',?)")->execute([$userId,$sid,$locked['plan_name'],$locked['amount'],$locked['reference']]);
  $pdo->prepare("UPDATE pix_orders SET status='approved' WHERE id=?")->execute([$locked['id']]);$pdo->commit();return 'approved';
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
