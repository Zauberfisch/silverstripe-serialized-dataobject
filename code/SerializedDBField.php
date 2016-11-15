<?php

/**
 * Class SerializedDBField
 *
 * @author Zauberfisch
 */
abstract class SerializedDBField extends DBField {
	protected $isChanged = false;

	/**
	 * @return SerializedDataList|SerializedDataObject|null
	 */
	function getValue() {
		if ($this->value && is_string($this->value) && $this->value[0] == "'" && $this->value[strlen($this->value) - 1] == "'") {
			$this->value = substr($this->value, 1, -1);
		}
		if (!$this->value) {
			$this->value = $this->nullValue();
		} elseif (is_string($this->value)) {
			$this->value = unserialize($this->value);
		}
		return $this->value;
	}

	/**
	 * @param SerializedDataList|SerializedDataObject|SerializedDBField|array|null|string $value
	 * @param null $record
	 * @param bool|true $markAsChanged
	 */
	public function setValue($value, $record = null, $markAsChanged = true) {
		$this->isChanged = $this->isChanged || $markAsChanged;
		if (is_a($value, __CLASS__)) {
			$value = $value->getValue();
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
		if (is_a($value, 'Serializable')) {
			$value = serialize($value);
		}
		if (is_array($value)) {
			$value = serialize($value);
		}
		return parent::prepValueForDB($value);
	}

	public function requireField() {
		// keep using deprecated DB::requireField() for 3.1 compatibility
		/** @noinspection PhpDeprecationInspection */
		DB::requireField($this->tableName, $this->name, [
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
