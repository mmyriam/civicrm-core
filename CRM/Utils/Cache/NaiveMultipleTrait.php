<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 * The traditional CRM_Utils_Cache_Interface did not support multiple-key
 * operations. To get drop-in compliance with PSR-16, we use a naive adapter.
 * An operation like `getMultiple()` just calls `get()` multiple times.
 *
 * Ideally, these should be replaced with more performant/native versions.
 */
trait CRM_Utils_Cache_NaiveMultipleTrait {

  /**
   * Obtains multiple cache items by their unique keys.
   *
   * @param iterable $keys A list of keys that can obtained in a single operation.
   * @param mixed $default Default value to return for keys that do not exist.
   *
   * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
   *
   * @throws \Psr\SimpleCache\InvalidArgumentException
   *   MUST be thrown if $keys is neither an array nor a Traversable,
   *   or if any of the $keys are not a legal value.
   */
  public function getMultiple($keys, $default = NULL) {
    $this->assertIterable('getMultiple', $keys);

    $result = [];
    foreach ($keys as $key) {
      $result[$key] = $this->get($key, $default);
    }
    return $result;
  }

  /**
   * Persists a set of key => value pairs in the cache, with an optional TTL.
   *
   * @param iterable $values A list of key => value pairs for a multiple-set operation.
   * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
   *                                       the driver supports TTL then the library may set a default value
   *                                       for it or let the driver take care of that.
   *
   * @return bool True on success and false on failure.
   *
   * @throws \Psr\SimpleCache\InvalidArgumentException
   *   MUST be thrown if $values is neither an array nor a Traversable,
   *   or if any of the $values are not a legal value.
   */
  public function setMultiple($values, $ttl = NULL) {
    $this->assertIterable('setMultiple', $values);

    $result = TRUE;
    foreach ($values as $key => $value) {
      if (is_int($key)) {
        $key = (string) $key;
      }
      $result = $this->set($key, $value, $ttl) || $result;
    }
    return $result;
  }

  /**
   * Deletes multiple cache items in a single operation.
   *
   * @param iterable $keys A list of string-based keys to be deleted.
   *
   * @return bool True if the items were successfully removed. False if there was an error.
   *
   * @throws \Psr\SimpleCache\InvalidArgumentException
   *   MUST be thrown if $keys is neither an array nor a Traversable,
   *   or if any of the $keys are not a legal value.
   */
  public function deleteMultiple($keys) {
    $this->assertIterable('deleteMultiple', $keys);

    $result = TRUE;
    foreach ($keys as $key) {
      $result = $this->delete($key) || $result;
    }
    return $result;
  }

  /**
   * @param $func
   * @param $keys
   * @throws \CRM_Utils_Cache_InvalidArgumentException
   */
  private function assertIterable($func, $keys) {
    if (!is_array($keys) && !($keys instanceof Traversable)) {
      throw new CRM_Utils_Cache_InvalidArgumentException("$func expects iterable input");
    }
  }

}
