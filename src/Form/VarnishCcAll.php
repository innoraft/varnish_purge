<?php

/**
 * @file
 * Contains \Drupal\varnish_purge\Form\VarnishCcAll
*/

namespace Drupal\varnish_purge\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class that manages content of the site.
 */
class VarnishCcAll extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varnish_purge_varnish_cc_all_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    module_load_include('inc', 'varnish_purge', 'varnish_purge');
    $domains = varnish_purge_get_domains();

    $form['description'] = array(
      '#markup' => t('<p>This form allows you to purge all cache from AWS Varnish
      Cloud. This form is not intended for day-to-day use and only meant for
      site administrators, for instance in emergency cases when a outdated copy of
      a page remains being served.</p>'),
    );

    if (!empty($domains)) {
      // Build up table rows with domains on the left and examples on the right.
      $rows = array();
      $rowsc = count($domains);

      for ($i = 0; $i < $rowsc; $i++) {
        $row = array();
        $row[] = isset($domains[$i]) ? $domains[$i] : '';
        $rows[] = $row;
      }

      // Add the guidance table to help the user understand.
      $form['guidancetable'] = array(
        '#theme' => 'table',
        '#header' => array(t('Domains to purge cache')),
        '#rows' => $rows,
      );
    }
    else {
      $url = new Url('varnish_purge.add_domain');
      $empty_text = \Drupal::l(t('Add Domains to Purge'), $url);
      $form['varnish_purge_domains'] = array(
        '#markup' => $empty_text . '<br /><br />',
      );
    }

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
    $domains = varnish_purge_get_domains();
    $messages = varnish_purge_clear_all_cache($domains);
    varnish_purge_show_message($messages);
  }
}
