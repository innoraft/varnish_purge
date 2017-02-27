<?php

/**
 * @file
 * Contains \Drupal\varnish_purge\Form\AddDomain
*/

namespace Drupal\varnish_purge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class that manages content of the site.
 */
class AddDomain extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varnish_purge_add_domain_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['varnish_purge_add_domain.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('varnish_purge_add_domain.settings');

    $form['add_domain'] = array(
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => t('DOMAINS TO PURGE PATHS ON'),
      '#description' => t('Enter Domain to purge. You can enter multiple domains
      separated by comma.'),
      '#default_value' => !empty($config->get('add_domain')) ? $config->get('add_domain') : NULL,
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('varnish_purge_add_domain.settings');

    // Lets save the settings.
    //if ($form_state->getValue('add_domain')) {
      $config->set('add_domain', $form_state->getValue('add_domain'))->save();
    //}
  }
}
