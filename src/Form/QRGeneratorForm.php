<?php

namespace Drupal\qr_generator\Form;

use Drupal\Core\Url;
use Endroid\QrCode\QrCode;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements An QR Generator Form.
 */
class QRGeneratorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'qr_generator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['node_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Node ID to generate QR Code:'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $node = $form_state->getValue('node_id');
    if ($node !== '' && (!is_numeric($node) || intval($node) != $node || $node <= 0)) {
      $form_state->setErrorByName('node_id', $this->t('Error: Node ID must be a positive integer!'));
    }
    else {
      $node_obj = Node::load($node);
      if ($node_obj == NULL) {
        $form_state->setErrorByName('node_id', $this->t('Error: Given Node doesn\'t exists!'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $options = ['absolute' => TRUE];
    $url_string = Url::fromRoute('entity.node.canonical', ['node' => $form_state->getValue('node_id')], $options)->toString();
    $qrCode = new QrCode($url_string);
    header('Content-Type: ' . $qrCode->getContentType());
    $file = file_save_data($qrCode->writeString(), file_default_scheme() . '://pictures/qr_codes/' . $form_state->getValue('node_id') . '.png', FILE_EXISTS_REPLACE);
    $form_state->setResponse(new RedirectResponse($file->url()));
  }

}
