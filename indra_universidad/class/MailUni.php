<?php

/**
 * Description of MailUni
 *
 * @author jaramos
 */
class MailUni {

  protected $node;
  protected $image;
  protected $iniciative;
  protected $urlRules;
  protected $urlPublications;
  protected $name;

  public function __construct($node) {
    $this->node = $node;
    $this->image = '<img src="' . path_to_theme() . '/images/universidad/teaser_cabeceara_' . $node->language . '.png" alt="image">';
    $this->iniciative = 'Learnig make us better.';
    $this->urlRules = 'http://www.indracompany.com/' . $node->language . '/aprendernoshacemejores/bases-concurso';
    $this->urlPublications = 'http://www.indracompany.com/' . $node->language . '/aprendernoshacemejores';
    $name_lang = $this->node->field_nombreuni[$this->node->language][0]['value'] ? $this->node->language : 'und';
    $this->name = $this->node->field_nombreuni[$name_lang][0]['value'];
  }

  /**
   * Prepara y envía el correo
   * @param string $body
   * @return boolean
   */
  public function mail($body = '') {
    $to = '';
    // destinatario
    $to = isset($this->node->field_mailuni[$this->node->language][0]['value']) ? $this->node->field_mailuni[$this->node->language][0]['value'] : $this->node->field_mailuni['und'][0]['value'];

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
      drupal_set_message(t('An error occurred and processing did not complete. The recipient is invalid.'), 'error');
      return false;
    }
    // cuerpo del mensaje
    $params = array('body' => $body);

    // título del mensaje
    $params['subject'] = t('Learnig make us better.', array(), array('langcode' => $this->node->language));

    $params['reply-to'] = 'aprendernoshacemejores@indra.es';
    // envío
    $message = drupal_mail('indra_universidad', 'indra_universidad_mail_send', $to, $this->node->language, $params);
    if ($message['result']) {
      watchdog('mail', 'Info indra_universidad_mail_send sending e-mail (from %from to %to).', array('%from' => $message['from'], '%to' => $message['to']), WATCHDOG_INFO);
    }
    return $message['result'];
  }

  /**
   * Determina el tipo de correo a enviar y cambia el estado del nodo.
   * @return boolean
   */
  public function mailAndStatus() {
    $language = isset($this->node->field_estadouni[$this->node->language][0]['value']) ? $this->node->language : 'und';
    $status = (int) $this->node->field_estadouni[$language][0]['value'];
    $dni = $this->node->field_dniuni[$language][0]['value'];

    // estados: 1= aprobado, 2= rechazado, 3 = publicado, 4 = anteriormente rechazado, 5 = pendiente 
    if (1 == $status) {
      // enviar mail inicando que el testimonio ha sido publicado
      $body_text = '<p>Hi @name!</p>
                    <p>Your story has been published. You can view it <a href="@domain">here</a> along with all the other publications</p>
                    <p>Thanks for participating and good luck with the draw! </p>';

      $body_options = array(
        '@name' => $this->name,
        '@domain' => $this->urlPublications,
      );

      $body = $this->image . t($body_text, $body_options, array('langcode' => $this->node->language));
      $send = $this->mail($body);

      // marcar el nodo como publicado
      if ($send) {
        $this->node->field_estadouni[$language][0]['value'] = '3';
        return $this->node;
      }
    }
    elseif (2 == $status) {
      // comprobar si el participante ya tiene otro testimonio publicado.
      $sql = "SELECT count(n.nid) AS amount
              FROM node n
              INNER JOIN field_data_field_estadouni s ON (n.nid, n.vid) = (s.entity_id, s.revision_id)
              LEFT JOIN field_data_field_dniuni d ON (n.nid, n.vid) = (d.entity_id, d.revision_id)
              WHERE n.status = 1
              AND d.field_dniuni_value = :dni
              AND s.field_estadouni_value = 3";
      $amount = db_query($sql, array(':dni' => $dni))->fetchField();
      if (0 < $amount) {
        // se le indica que ya tine otro testimonio publicado
        $body_text = '<p>Hi @name!</p>
                      <p>Thanks for participating but according to our records you have already published a story. We can only publish one message per participant.</p>
                      <p>Click <a href="@domain">here</a> to check the initiative rules.</p>';
        $body_options = array(
          '@name' => $this->name,
          '@domain' => $this->urlRules,
        );
        $body = $this->image . t($body_text, $body_options, array('langcode' => $this->node->language));
        $send = $this->mail($body);
      }
      else {
        // se le indica las bases del concurso
        $body_text = '<p>Hi @name!</p>
                      <p>Please note that your story has not been published because it does not conform to the rules of the @initiative</p>
                      <p>Click <a href="@domain">here</a> for further details.</p>
                      <p>Do not be put off, try again!</p>';
        $body_options = array(
          '@name' => $this->name,
          '@initiative' => $this->iniciative,
          '@domain' => $this->urlRules,
        );
        $body = $this->image . t($body_text, $body_options, array('langcode' => $this->node->language));
        $send = $this->mail($body);
      }
      if ($send) {
        $this->node->field_estadouni[$language][0]['value'] = 4;
        return $this->node;
      }
    }
    else {
      return false;
    }
  }

}
