<?php

function indra_universidad_voting_ajax() {
  $nid = (int) $_POST['nid'];
  $node = node_load($nid);
  if (false !== $node) {
    $voting = (int) isset($node->field_voting['und'][0]['value']) ? $node->field_voting['und'][0]['value'] : 0;
    $node->field_voting['und'][0]['value'] = $voting + 1;
    node_save($node);
    drupal_session_start();
    $_SESSION['voting'][$nid] = $nid;
    echo $voting + 1;
    exit;
  }
  echo (int) substr($_POST['num'], 0, 20);
  exit;
}
