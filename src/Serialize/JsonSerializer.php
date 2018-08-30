<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Serialize;

trait JsonSerializer {
	public function jsonSerialize() {
		return [
			'#type' => get_class($this),
		];
	}

	public static function json_deserialize(array $serialized = null) {
		$class = get_called_class();
		/** @var JsonSerializable $obj */
		$obj = new $class();
		$obj->jsonDeserialize($serialized);
		return $obj;
	}
}