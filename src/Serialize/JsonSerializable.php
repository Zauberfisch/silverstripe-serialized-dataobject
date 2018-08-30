<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Serialize;

interface JsonSerializable extends \JsonSerializable {
	public function jsonDeserialize(array $data = null);

	public static function json_deserialize(array $serialized = null);
}
