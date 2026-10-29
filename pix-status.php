<?php
require 'includes/core.php';require 'includes/pix.php';header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$u=me();if(!$u){http_response_code(401);echo json_encode(['error'=>'unauthorized']);exit;}
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$stmt=db()->prepare('SELECT * FROM pix_orders WHERE id=? AND user_id=?');$stmt->execute([$id?:0,$u['id']]);$order=$stmt->fetch();if(!$order){http_response_code(404);echo json_encode(['error'=>'not_found']);exit;}
try{$status=pix_reconcile($order,(int)$u['id']);echo json_encode(['status'=>$status]);}catch(Throwable $e){http_response_code(503);echo json_encode(['error'=>'provider_unavailable']);}
