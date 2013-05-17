<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage MODULE_NAME
 * @copyright  2001-2012 Bitrix
 */

namespace Bitrix\Main\Data;

abstract class NosqlConnection extends Connection
{
	abstract public function get($key);
	static abstract public function set($key, $value);
}