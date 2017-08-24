<?php

namespace zauberfisch\SerializedDataObject;

class ArrayList extends AbstractList {
	public function validateRecord($item) {
		return is_a($item, AbstractDataObject::class);
	}
}
