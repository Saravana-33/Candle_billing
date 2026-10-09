<?php
require_once __DIR__.'/config.php';
if (empty($_SESSION['user'])) { http_response_code(403); exit('Login required'); }

function jout($d){ header('Content-Type: application/json'); echo json_encode($d); exit; }

$action = $_GET['action'] ?? '';

/* customer autocomplete */
if ($action==='customers'){
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare("SELECT name, phone, address FROM customers
                         WHERE name LIKE ? ORDER BY name LIMIT 6");
    $st->execute([$q.'%']);
    jout($st->fetchAll());
}

/* save bill from New Bill page */
if ($action==='save_bill'){
    $d     = json_decode(file_get_contents('php://input'), true) ?: [];
    $name  = trim($d['name'] ?? '');
    $phone = trim($d['phone'] ?? '');
    $addr  = trim($d['address'] ?? '');
    $mode  = in_array($d['mode'] ?? '', ['Cash','Card','UPI'], true) ? $d['mode'] : 'Cash';
    $status= (($d['status'] ?? '') === 'PAID') ? 'PAID' : 'UNPAID';
    $items = is_array($d['items'] ?? null) ? $d['items'] : [];
    $items = array_values(array_filter($items,
        fn($i)=> trim($i['name'] ?? '') !== '' && (int)($i['qty'] ?? 0) > 0));

    if ($name==='' || !$items)
        jout(['ok'=>false,'msg'=>'Customer name and at least one item are required']);

    $pdo->beginTransaction();
    try{
        $st=$pdo->prepare("SELECT id FROM customers WHERE name=? LIMIT 1");
        $st->execute([$name]); $cid=$st->fetchColumn();
        if(!$cid){
            $pdo->prepare("INSERT INTO customers(name,phone,address) VALUES(?,?,?)")
                ->execute([$name,$phone,$addr]);
            $cid=$pdo->lastInsertId();
        } elseif($phone!=='' || $addr!==''){
            $pdo->prepare("UPDATE customers SET
                             phone=CASE WHEN ?<>'' THEN ? ELSE phone END,
                             address=CASE WHEN ?<>'' THEN ? ELSE address END
                           WHERE id=?")
                ->execute([$phone,$phone,$addr,$addr,$cid]);
        }

        $total=0;
        foreach($items as $i){ $total += ((int)$i['qty']) * ((float)$i['price']); }

        $pdo->prepare("INSERT INTO bills(customer_id,date,total,status,payment_mode)
                       VALUES(?,NOW(),?,?,?)")
            ->execute([$cid,$total,$status,$mode]);
        $bid=$pdo->lastInsertId();

        $insI=$pdo->prepare("INSERT INTO bill_items(bill_id,item_name,qty,price,cost,total)
                             VALUES(?,?,?,?,?,?)");
        $get =$pdo->prepare("SELECT cost FROM items WHERE name=?");
        $add =$pdo->prepare("INSERT IGNORE INTO items(name,price,cost) VALUES(?,?,0)");

        foreach($items as $i){
            $nm=trim($i['name']); $qy=(int)$i['qty']; $pr=(float)$i['price'];
            $get->execute([$nm]); $c=$get->fetchColumn();
            if($c===false){ $add->execute([$nm,$pr]); $c=0; }  /* remember custom items */
            $insI->execute([$bid,$nm,$qy,$pr,$c,$qy*$pr]);
        }
        $pdo->commit();
        jout(['ok'=>true,'id'=>$bid]);
    } catch(Exception $ex){
        $pdo->rollBack();
        jout(['ok'=>false,'msg'=>$ex->getMessage()]);
    }
}

jout(['ok'=>false,'msg'=>'Unknown action']);
