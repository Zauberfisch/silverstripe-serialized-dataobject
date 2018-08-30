<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

class ArrayList extends AbstractList {
	public function validateRecord($item) {
		return is_a($item, AbstractDataObject::class);
	}
}
