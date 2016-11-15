<?php

/**
 * Class SerializedDBFieldHasMany
 *
 * @author Zauberfisch
 */
class SerializedDBFieldHasMany extends SerializedDBField {
	public function nullValue() {
		return new SerializedDataList();
	}
}
