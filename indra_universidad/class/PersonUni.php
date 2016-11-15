<?php

/**
 * Description of PresonUni
 *
 * @author jaramos
 */
class PersonUni {

  /**
   * Comprueba si el NIF existe en LDAP
   * @param obj $node
   * @return $node | boolean
   */
  static public function nif($node) {
    $nif = $node->field_dniuni['und'][0]['value'];
    $person = Persona::buscarPorNIF($nif);
    if ($person !== NULL && $person[0] !== NULL && $person[0]->estaActivo()) {
      $node->field_correo_corporativouni['und'][0]['value'] = $person[0]->getEmail();
      $node->field_numero_global_corporativo['und'][0]['value'] = $person[0]->getNumero_Empleado();
      $node->field_alias_corporativo['und'][0]['value'] = $person[0]->getUsername();
      return $node;
    }
    else {
      return false;
    }
  }

  /**
   * Establece si el participante tiene testimonios anteriores.
   * @param obj $node
   * @return obj $node
   */
  static public function previousTestimony($node) {
    $dni = $node->field_dniuni['und'][0]['value'];
    // comprobar si el participante ya tiene otro testimonio.
    $sql = "SELECT d.field_dniuni_value AS dni, s.field_estadouni_value AS estado
              FROM node n
              INNER JOIN field_data_field_estadouni s ON (n.nid, n.vid) = (s.entity_id, s.revision_id)
              LEFT JOIN field_data_field_dniuni d ON (n.nid, n.vid) = (d.entity_id, d.revision_id)
              WHERE n.status = 1
              AND d.field_dniuni_value = :dni";
    $result = db_query($sql, array(':dni' => $dni))->fetchAll();

    // 0|No tiene, 1|Tiene publicados, 2|Tiene pero NO publicados
    if ($result) {
      $testimeny = array();
      foreach ($result as $row) {
        $testimeny[] = $row->estado;
      }
      if (in_array(3, $testimeny)) {
        $node->field_testimonios_anteriores['und'][0]['value'] = 1;
      }
      else {
        $node->field_testimonios_anteriores['und'][0]['value'] = 2;
      }
    }
    else {
      $node->field_testimonios_anteriores['und'][0]['value'] = 0;
    }

    return $node;
  }

}
