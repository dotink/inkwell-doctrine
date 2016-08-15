<?php namespace Inkwell\Doctrine
{
	trait JsonEntity
	{
		/**
		 *
		 */
		public function jsonSerialize()
		{
			$data = array();

			foreach (get_class_vars(get_parent_class($this)) as $name => $value) {
				$value = $this->$name;

				if (is_object($value)) {
					if (isset(static::$jsonObjectConfig[$name])) {
						$obj_method  = static::$jsonObjectConfig[$name];
						$data[$name] = [$value, $obj_method]();
					}

				} else {
					$data[$name] = $this->$name;
				}
			}

			return $data;
		}
	}
}
