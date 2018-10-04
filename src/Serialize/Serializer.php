<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Serialize;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;

class Serializer {
	private static $serialize_verbose = false;
	
	public static function serialize($data) {
		return json_encode($data, Config::inst()->get(__CLASS__, 'serialize_verbose') ? JSON_PRETTY_PRINT : 0);
	}
	
	public static function deserialize(string $serialized = null) {
		$data = null;
		if ($serialized) {
			$_data = json_decode($serialized, true);
			if (json_last_error() == JSON_ERROR_NONE) {
				$data = static::deserialize_data($_data);
			} else {
				// check for legacy php serialized data
				if (preg_match('/^\w:\d*:/', $serialized)) {
					if ($serialized[0] == "'" && $serialized[strlen($serialized) - 1] == "'") {
						$serialized = substr($serialized, 1, -1);
					}
					$data = @unserialize($serialized);
				}
				if (!$data) {
					throw new \InvalidArgumentException('Unable to parse serialized data');
				}
			}
		}
		return $data;
	}
	
	protected static function deserialize_data($data) {
		if (is_array($data)) {
			$class = null;
			if (isset($data['#type'])) {
				$class = $data['#type'];
				unset($data['#type']);
				if (!ClassInfo::exists($class)) {
					// TODO implement graceful failing for non-existing classes similar to SiteTree's missing page class
					throw new \Exception("Failed to deserialize: Class $class does not exist");
				} else if (ClassInfo::classImplements($class, JsonSerializable::class)) {
					throw new \Exception("Failed to deserialize: Class $class does not implement " . JsonSerializable::class);
				}
			}
			$return = [];
			foreach ($data as $key => $nestedData) {
				$return[$key] = static::deserialize_data($nestedData);
			}
			if ($class) {
				$return = $class::json_deserialize($return);
			}
			return $return;
		}
		return $data;
	}
}