<?php

/**
 * @file
 * Provide all the functions related to varnish purge.
 */

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;

/**
 * Get all the domains we need to purge.
 *
 * @return array|string
 *   Array of Domains.
 */
function varnish_purge_get_domains() {
  $config = \Drupal::service('config.factory')->getEditable('varnish_purge_add_domain.settings');
  $domains = !empty($config->get('add_domain')) ? $config->get('add_domain') : array();
  if (!empty($domains)) {
    $domains = explode(',', $domains);
  }

  return $domains;
}

/**
 * Clear all cache for the selected hosts.
 *
 * @param array $hosts
 *   Clear cache for these hosts.
 *
 * @return array
 *   Array of successful messages.
 */
function varnish_purge_clear_all_cache(array $hosts = array()) {
  global $base_url;
  $messages = array();
  $url = parse_url($base_url);
  $host = $url['host'];
  $hosts[] = $host;
  foreach ($hosts as $host) {
    $header = array(
      'x-ban-host:' . $host,
    );
    $http_response = varnish_purge_ban_url($header);
    if ($http_response == 200) {
      $messages[] = t('@host all cache cleared!', array('@host' => $host));
    }
  }
  return $messages;
}

/**
 * Sends Ban request to varnish.
 *
 * @param array $header
 *   Header array for ban requests.
 *
 * @return string
 *   HTTP response for ban request.
 */
function varnish_purge_ban_url(array $header = array(), $base_url = '') {
  if ($base_url == '') {
    global $base_url;
  }

  $curl = curl_init($base_url);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "BAN");
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_exec($curl);
  $http_response = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  return $http_response;
}

/**
 * Show messages after process.
 *
 * @param array $messages
 *   Array of messages to shown.
 */
function varnish_purge_show_message(array $messages = array()) {
  $message = implode('<br />', $messages);
  // For allowing br tag.
  $message = Xss::filter($message, array('br'));
  drupal_set_message($message);
}

/**
 * Ban URL Patterns.
 *
 * @param array $urls
 *   Array of url patterns
 *
 * @return array
 *   Array of messages.
 */
function varnish_purge_ban_urls(array $urls = array()) {
  global $base_url;
  $messages = array();
  if (!empty($urls)) {
    $url = parse_url($base_url);
    $host = $url['host'];
    foreach ($urls as $url_pattern) {
      $header = array(
        'x-ban-url:' . $url_pattern,
        'x-ban-host:' . $host,
      );
      $http_response = varnish_purge_ban_url($header);
      if ($http_response == 200) {
        $messages[] = t('@url is Banned!', array('@url' => $url_pattern));
      }
    }
  }
  return $messages;
}

/**
 * Purges the url given.
 *
 * @param array $urls
 *   Array of urls to be purged.
 *
 * @return array
 *   Array of messages for purged urls.
 */
function varnish_purge_clear_cache(array $urls = array()) {
  if (!empty($urls)) {
    global $base_url;
    $messages = array();
    foreach ($urls as $url) {
      $url =Url::fromUserInput('/' . $url,  array('absolute' => 'true'))->toString();
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_exec($curl);
      $http_response = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($http_response == 200) {
        $messages[] = t('@url is Purged.', array('@url' => $url));
      }
    }
  }
  return $messages;
}
