<?php
function get_criteria($conn){
    $rows = [];
    $res = $conn->query("SELECT id, name, weight, attribute FROM criteria ORDER BY id");
    if($res){
      while($r = $res->fetch_assoc()){ $rows[] = $r; }
    }
    return $rows;
}
function get_alternatives($conn){
    $rows = [];
    $res = $conn->query("SELECT id, name FROM alternatif ORDER BY id");
    if($res){
      while($r = $res->fetch_assoc()){ $rows[] = $r; }
    }
    return $rows;
}
function get_matrix($conn){
    $M = [];
    $res = $conn->query("SELECT alternative_id, criteria_id, value FROM sub_criteria");
    if($res){
      while($r = $res->fetch_assoc()){
          $a = $r['alternative_id'];
          $c = $r['criteria_id'];
          $v = floatval($r['value']);
          if(!isset($M[$a])) $M[$a] = [];
          $M[$a][$c] = $v;
      }
    }
    return $M;
}

function saw_rank($conn){
    $criteria = get_criteria($conn);
    $alternatives = get_alternatives($conn);
    $matrix = get_matrix($conn);

    // cari max & min per kriteria
    $max = []; $min = [];
    foreach($criteria as $c){
        $cid = $c['id'];
        $values = [];
        foreach($alternatives as $a){
            $aid = $a['id'];
            $values[] = isset($matrix[$aid][$cid]) ? floatval($matrix[$aid][$cid]) : 0.0;
        }
        $max[$cid] = !empty($values) ? max($values) : 0.0;
        $min[$cid] = !empty($values) ? min($values) : 0.0;
    }

    // normalisasi & hitung skor
    $scores = [];
    foreach($alternatives as $a){
        $aid = $a['id'];
        $score = 0.0;
        foreach($criteria as $c){
            $cid = $c['id'];
            $w = floatval($c['weight']);
            $attr = strtolower($c['attribute']); // 'benefit' atau 'cost'
            $x = isset($matrix[$aid][$cid]) ? floatval($matrix[$aid][$cid]) : 0.0;
            if($attr === 'cost'){
                $r = ($x == 0) ? 0.0 : ($min[$cid] / $x);
            } else {
                $r = ($max[$cid] == 0) ? 0.0 : ($x / $max[$cid]);
            }
            $score += $w * $r;
        }
        $scores[] = ['id'=>$aid, 'name'=>$a['name'], 'score'=>$score];
    }

    // urutkan desc
    usort($scores, function($a,$b){ return ($b['score'] <=> $a['score']); });
    return $scores;
}
?>
