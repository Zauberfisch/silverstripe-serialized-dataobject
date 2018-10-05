<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\DBField;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBField;
use zauberfisch\SerializedDataObject\AbstractDataObject;
use zauberfisch\SerializedDataObject\AbstractList;
use zauberfisch\SerializedDataObject\Serialize\Serializer;

/**
 * @author Zauberfisch
 */
abstract class AbstractField extends DBField {
	protected $isChanged = false;
	
	/**
	 * @return AbstractList|AbstractField|null
	 */
	function getValue() {
		if (!$this->value) {
			$this->value = $this->nullValue();
		}
		return $this->value;
	}
	
	/**
	 * @param AbstractList|AbstractDataObject|AbstractField|null|string $value
	 * @param null $record
	 * @param bool|true $markAsChanged
	 */
	public function setValue($value, $record = null, $markAsChanged = true) {
		$this->isChanged = $this->isChanged || $markAsChanged;
		if (is_a($value, __CLASS__)) {
			$value = $value->getValue();
		}
		if (is_string($value)) {
			$value = Serializer::deserialize($value);
		}
		parent::setValue($value, $record);
	}
	
	public function isChanged() {
		return $this->isChanged;
	}
	
	public function prepValueForDB($value) {
		if (is_a($value, __CLASS__)) {
			$value = $value->getValue();
		}
		if (is_object($value)) {
			$value = Serializer::serialize($value);
		}
		return parent::prepValueForDB($value);
	}
	
	public function requireField() {
		DB::require_field($this->tableName, $this->name, [
			'type' => 'text',
			'parts' => [
				'datatype' => 'mediumtext',
				'character set' => 'utf8',
				'collate' => 'utf8_general_ci',
				'arrayValue' => $this->arrayValue,
			],
		]);
	}
	
	public function __toString() {
		return $this->prepValueForDB($this->getValue());
	}
}
