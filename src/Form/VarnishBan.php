<?php

/**
 * @file
 * Contains \Drupal\varnish_purge\Form\VarnishBan
*/

namespace Drupal\varnish_purge\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class that manages content of the site.
 */
class VarnishBan extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varnish_purge_varnish_ban_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    module_load_include('inc', 'varnish_purge', 'varnish_purge');

    $form['#tree'] = TRUE;

    $form['paths'] = array(
      '#prefix' => '<div id="paths-wrapper">',
      '#suffix' => '</div>',
    );

    $title = 'Pattern';

    $num_names = $form_state->get('num_names');
    if (empty($num_names)) {
      $num_names = 1;
      $form_state->set('num_names', $num_names);
    }

    global $base_url;

    for ($i = 0; $i < $num_names; $i++) {
      $form['paths']['path'][$i] = array(
        '#type' => 'textfield',
        '#title' => $this->t('@title - @number', array('@title' => $title, '@number' => $i + 1)),
        '#field_prefix' => $base_url . '/',
      );
    }

    $form['paths']['add_path'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
      '#submit' => array(array($this, 'varnish_purge_manualpurge_form_add_one')),
      '#ajax' => array(
        'callback' => array($this, 'varnish_purge_manualpurge_form_callback'),
        'wrapper' => 'paths-wrapper',
      ),
    );

    if ($num_names > 1) {
      $form['paths']['remove_path'] = array(
        '#type' => 'submit',
        '#value' => t('Remove last item'),
        '#submit' => array(array($this, 'varnish_purge_manualpurge_form_remove_one')),
        '#ajax' => array(
          'callback' => array($this, 'varnish_purge_manualpurge_form_callback'),
          'wrapper' => 'paths-wrapper',
        ),
      );
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('I know the risks, BAN!'),
    ];

    return $form;
  }

  public function varnish_purge_manualpurge_form_callback(array &$form, FormStateInterface $form_state) {
    return $form['paths'];
  }

  public function varnish_purge_manualpurge_form_add_one(array &$form, FormStateInterface $form_state) {
    $num_names = $form_state->get('num_names');
    $num_names++;
    $form_state->set('num_names', $num_names);
    $form_state->setRebuild();
  }

  public function varnish_purge_manualpurge_form_remove_one(array &$form, FormStateInterface $form_state) {
    $num_names = $form_state->get('num_names');
    if ($num_names > 1) {
      $num_names--;
      $form_state->set('num_names', $num_names);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('paths');
    $paths = array();
    $submitted_paths = $values['path'];
    foreach ($submitted_paths as $id => $path) {
      if (empty($path)) {
        $form_state->setErrorByName('paths][path][' . $id,
          t('The path can not be empty, use &lt;front&gt; for your frontpage!'));
      }
      elseif (!is_string($path)) {
        $form_state->setErrorByName('paths][path][' . $id,
          t("The path has to be a string!"));
      }
      elseif (stristr($path, 'http:') || stristr($path, 'https:')) {
        $form_state->setErrorByName('paths][path][' . $id,
          t("You can't provide a URL, only paths!"));
      }
      elseif (preg_match('/\s/', $path)) {
        $form_state->setErrorByName('paths][path][' . $id,
          t('The path can not contain a space!'));
      }
      elseif (in_array($path, $paths)) {
        $form_state->setErrorByName('paths][path][' . $id,
          t('You have already listed this path!'));
      }

      $paths[] = $path;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    module_load_include('inc', 'varnish_purge', 'varnish_purge');

    $values = $form_state->getValue('paths');
    $submitted_paths = $values['path'];
    $messages = varnish_purge_ban_urls($submitted_paths);
    varnish_purge_show_message($messages);
  }
}
