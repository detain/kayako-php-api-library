<?php
/**
 * List of objects on steroids.
 *
 * Class used for wrapping list of objects into result set with filtering,
 * ordering and paging capabilities.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @package Common
 */
class kyResultSet implements Iterator, Countable, ArrayAccess {

	/**
	 * Prefix for filter methods.
	 * @var string
	 */
	const FILTER_PREFIX = "filterBy";

	/**
	 * Prefix for order methods.
	 * @var string
	 */
	const ORDER_PREFIX = "orderBy";

	/**
	 * Prefix for collect methods.
	 * @var string
	 */
	const COLLECT_PREFIX = "collect";

	/**
	 * List of valid filter operators.
	 * @var string[]
	 */
	static private $operators = array("~", ">", ">=", "<", "<=", "!=");

	/**
	 * Class name of objects in this result set.
	 * @var string
	 */
	private $class_name;

	/**
	 * List of objects in this result set.
	 * @var kyObjectBase[]
	 */
	private $objects;

	/**
	 * Cached list of $objects array keys to avoid resetting internal pointer of $objects.
	 * @var mixed[]
	 */
	private $object_keys;

	/**
	 * Optional result set which was the base of filtering operation producing this result set.
	 * @var kyResultSet
	 */
	private $previous_result_set;

	/**
	 * Constructs a result set.
	 *
	 * @param kyObjectBase[]|kyResultSet $objects List of objects in this result set.
	 * @param string $class_name Optional. Class name of objects in this result set.
	 * @param kyResultSet $previous_result_set Optional result set which was the base of filtering operation producing this result set.
	 */
	function __construct($objects, $class_name = null, $previous_result_set = null) {
		if ($objects === null) {
			$objects = array();
		}

		if ($objects instanceof kyResultSet) {
			$objects = $objects->getRawArray();
		}

		if (strlen($class_name) > 0) {
			$this->class_name = $class_name;
		} elseif (count($objects) > 0) {
			//get class name of first object
			$this->class_name = get_class(reset($objects));
		}

		$this->objects = $objects;
		$this->object_keys = array_keys($objects);
		$this->previous_result_set = $previous_result_set;
	}

	/**
	 * Returns class name of objects in result set.
	 *
	 * @return string
	 */
	public function getObjectsClassName() {
		return $this->class_name;
	}

	/**
	 * Iterator implementation.
	 */
	public function rewind() {
		reset($this->objects);
	}

	/**
	 * Iterator implementation.
	 */
	public function current() {
		return current($this->objects);
	}

	/**
	 * Iterator implementation.
	 */
	public function key() {
		return key($this->objects);
	}

	/**
	 * Iterator implementation.
	 */
	public function next() {
		next($this->objects);
	}

	/**
	 * Iterator implementation.
	 */
	public function valid() {
		return current($this->objects) !== false;
	}

	/**
	 * Countable implementation.
	 */
	public function count() {
		return count($this->object_keys);
	}

	/**
	 * ArrayAccess implementation.
	 */
	public function offsetExists($offset) {
		return in_array($offset, $this->object_keys);
	}

	/**
	 * ArrayAccess implementation.
	 */
	public function offsetGet($offset) {
		if (!in_array($offset, $this->object_keys))
			return null;

		return $this->objects[$offset];
	}

	/**
	 * ArrayAccess implementation.
	 */
	public function offsetSet($offset, $value) {
		if (!is_object($value) || (strlen($this->class_name) > 0 && get_class($value) !== $this->class_name))
			throw new DomainException(sprintf('The result set can only hold objects of type "%s"', $this->class_name));

		$this->objects[$offset] = $value;
		$this->object_keys = array_keys($this->objects);
	}

	/**
	 * ArrayAccess implementation.
	 */
	public function offsetUnset($offset) {
		if (!in_array($offset, $this->object_keys))
			return;

		unset($this->objects[$offset]);
		$this->object_keys = array_keys($this->objects);
	}

	/**
	 * Returns PHP array of objects from this result set.
	 *
	 * @return kyObjectBase[]
	 */
	public function getRawArray() {
		return $this->objects;
	}

	/**
	 * Filters objects in this result set and returns new result set.
	 *
	 * Filtering is done by calling defined get method on all objects and comparing its result with provided filter values.
	 * Filter value can be any scalar value (integer, float, string or boolean) or array for special cases.
	 * Special cases for filter value:
	 * - array('~' "Perl regexp pattern") - running Perl regexp match against object value
	 * - array('>', filter_value) - values greater than provided
	 * = array('<', filter_value) - values lesser than provided
	 * - array('>=', filter_value) - values greater or equal than provided
	 * - array('<=', filter_value) - values lesser or equal than provided
	 * - array('!=', filter_value) - values other than provided
	 *
	 * NOTICE: This method shouldn't be used directly in most cases. Use kyResultSet->filterByXXX(filter_value1, filter_value2) instead. Call kyObjectType::getAvailableFilterMethods() for possible filter methods.
	 *
	 * Additional method arguments will be passed as arguments to the get method.
	 *
	 * @param string $get_method_name Name of get method to call on every object for comparing its value with filter values.
	 * @param array $filter_values List of filter values.
	 * @return kyResultSet
	 */
	public function filterBy($get_method_name, $filter_values) {
		if (!is_array($filter_values)) {
			$filter_values = array($filter_values);
		}
		//pack single filter with operator into additional array
		elseif (count($filter_values) === 2) {
			$operator_or_not = reset($filter_values);
			if (is_string($operator_or_not) && in_array($operator_or_not, self::$operators)) {
				$filter_values = array($filter_values);
			}
		}

		$get_method_arguments = array();
		if (func_num_args() > 2) {
			$get_method_arguments = array_splice(func_get_args(), 2);
		}

		$filtered_objects = array();
		foreach ($this->object_keys as $key) {
			$object = $this->objects[$key];
			$object_values = call_user_func_array(array($object, $get_method_name), $get_method_arguments);
			if (!is_array($object_values)) {
				$object_values = array($object_values);
			}

			if (self::filterObject($filter_values, $object_values)) {
				$filtered_objects[] = $object;
			}
		}

		return new kyResultSet($filtered_objects, $this->getObjectsClassName(), $this);
	}

	/**
	 * Sorts objects in this result set.
	 * Sorting is done by calling defined get method on all objects and using its result to sort objects.
	 * NOTICE: This method shouldn't be used directly in most cases. Use kyResultSet->orderByXXX() instead. Call kyObjectType::getAvailableOrderMethods() for possible order methods.
	 * WARNING: Calling this method resets internal pointer of objects array.
	 *
	 * @param string $get_method_name Name of get method to call on every object to order based on its value.
	 * @param bool $asc True (default) to sort ascending. False to sort descending.
	 * @return kyResultSet
	 */
	public function orderBy($get_method_name, $asc = true) {
		usort($this->objects, ky_usort_comparison(array("kyResultSet", "compareObjects"), array($get_method_name, $asc)));
		$this->object_keys = array_keys($this->objects);
		return $this;
	}

	/**
	 * Collects results of method call on all objects in result set into an array.
	 *
	 * NOTICE: This method shouldn't be used directly in most cases. Use kyResultSet->collectXXX() instead.
	 *
	 * Additional method arguments will be passed as arguments to the get method.
	 *
	 * @param string $get_method_name Name of the method to call on object.
	 * @return array
	 */
	public function collect($get_method_name) {
		$get_method_arguments = array();
		if (func_num_args() > 1) {
			$get_method_arguments = array_splice(func_get_args(), 1);
		}

		$collect_result = array();
		foreach ($this->object_keys as $key) {
			$collect_result[] = call_user_func_array(array($this->objects[$key], $get_method_name), $get_method_arguments);
		}
		return $collect_result;
	}

	/**
	 * Returns first object in this result set.
	 *
	 * @return kyObjectBase
	 */
	public function first() {
		if (count($this->object_keys) === 0)
			return null;

		//looks strange, but avoids resetting internal pointer of $this->objects
		return $this->objects[reset($this->object_keys)];
	}

	/**
	 * Removes all filters and returns the original result set.
	 *
	 * @return kyResultSet
	 */
	public function removeFilters() {
		if ($this->previous_result_set === null)
			return $this;

		return $this->previous_result_set->removeFilters();
	}

	/**
	 * Removes last (or more) filters in order they were applied.
	 *
	 * @param int $depth How many filters to remove.
	 * @return kyResultSet
	 */
	public function removeFilter($depth = 1) {
		if ($depth <= 0 || $this->previous_result_set === null)
			return $this;

		return $this->previous_result_set->removeFilter($depth - 1);
	}

	/**
	 * Helper for paging results.
	 *
	 * Pass page number and maximum items count per page and it will return
	 * new result with items on specified page.
	 *
	 * NOTICE: Use the same maximum number of items per page for each page to get the proper behaviour.
	 *
	 * @param int $page_number Page number, starting from 1.
	 * @param int $items_per_page Maximum number of items per page (default 20).
	 * @return kyResultSet
	 */
	public function getPage($page_number, $items_per_page = 20) {
		if ($items_per_page <= 0)
			$items_per_page = 20;

		//avoid resetting internal pointer of $this->objects
		$page_object_keys = array_slice($this->object_keys, ($page_number - 1) * $items_per_page, $items_per_page, true);
		$page_objects = array();
		foreach ($page_object_keys as $object_key) {
			$page_objects[] = $this->objects[$object_key];
		}
		return new kyResultSet($page_objects, $this->getObjectsClassName());
	}

	/**
	 * Returns number of pages to display all objects within this result set.
	 *
	 * @param int $items_per_page Maximum number of items per page (default 20).
	 * @return int
	 */
	public function getPageCount($items_per_page = 20) {
		if ($items_per_page <= 0)
			$items_per_page = 20;

		return ceil(count($this->object_keys) / $items_per_page);
	}

	/**
	 * Intercepts filtering and ordering method calls, prepares data and runs filtering or ordering.
	 * To get available filter methods, call kyObjectType::getAvailableFilterMethods().
	 * To get available order methods, call kyObjectType::getAvailableOrderMethods().
	 *
	 * @param string $name Method name.
	 * @param array $arguments Method arguments.
	 * @return kyResultSet
	 */
	public function __call($name, $arguments) {
		if (stripos($name, self::FILTER_PREFIX) === 0) {
			if (count($this->object_keys) === 0)
				return new kyResultSet($this->objects, $this->getObjectsClassName());

			$filter_name = strtolower($name);

			$class_name = $this->class_name;
			/** @noinspection PhpUndefinedMethodInspection */
			$available_filtering = array_change_key_case($class_name::getAvailableFilterMethods(false));

			if (array_key_exists($filter_name, $available_filtering)) {
				$get_method_name = $available_filtering[$filter_name];
				array_unshift($arguments, $get_method_name);
				return call_user_func_array(array($this, 'filterBy'), $arguments);
			}
		} elseif (stripos($name, self::ORDER_PREFIX) === 0) {
			if (count($this->object_keys) === 0)
				return $this;

			$asc = true;
			if (count($arguments) === 1) {
				$first_arg = reset($arguments);
				if ($first_arg === false) {
					$asc = false;
				}
			}

			$class_name = $this->class_name;
			/** @noinspection PhpUndefinedMethodInspection */
			$available_ordering = array_change_key_case($class_name::getAvailableOrderMethods(false));

			$order_name = strtolower($name);
			if (array_key_exists($order_name, $available_ordering)) {
				$get_method_name = $available_ordering[$order_name];
				$this->orderBy($get_method_name, $asc);
				return $this;
			}
		} elseif (stripos($name, self::COLLECT_PREFIX) === 0) {
			$get_method_name = preg_replace(sprintf('/^%s/', self::COLLECT_PREFIX), 'get', $name);
			array_unshift($arguments, $get_method_name);
			return call_user_func_array(array($this, 'collect'), $arguments);
		}

		trigger_error(sprintf('Call to undefined method %s::%s()', get_class($this), $name), E_USER_ERROR);
		return $this;
	}

	/**
	 * Compare two objects using values returned by calling indicated get method.
	 * NOTICE: Internal helper method for sorting.
	 *
	 * @param kyObjectBase $object1 First object to compare.
	 * @param kyObjectBase $object2 Second object to compare.
	 * @param string $get_method_name Name of get method to call on each object to compare its values.
	 * @param bool $asc True (default) to sort ascending. False to sort descending.
	 * @return int
	 */
	static public function compareObjects($object1, $object2, $get_method_name, $asc) {
		$value1 = $object1->$get_method_name();
		if (is_array($value1)) {
			$value1 = reset($value1);
		}
		$value2 = $object2->$get_method_name();
		if (is_array($value2)) {
			$value2 = reset($value2);
		}
		return ($asc ? 1 : -1) * strcasecmp($value1, $value2);
	}

	/**
	 * Checks if object values match filter values.
	 *
	 * @param array $filter_values Filter values.
	 * @param array $object_values Object values.
	 * @return bool
	 */
	static private function filterObject(&$filter_values, $object_values) {
		foreach ($object_values as $object_value) {
			foreach (array_keys($filter_values) as $key) {
				$filter_value = $filter_values[$key];

				if (is_array($filter_value) && count($filter_value) === 2) {
					$adv_filter_operator = reset($filter_value);
					$adv_filter_value = end($filter_value);
					switch ($adv_filter_operator) {
						case '~':
							$preg_result = preg_match($adv_filter_value, $object_value);

							//pattern ok and matched
							if ($preg_result === 1)
								return true;
							break;
						case '<':
						case '>':
						case '<=':
						case '>=':
							//making types the same
							settype($adv_filter_value, gettype($object_value));
							$result = eval('return $object_value ' . $adv_filter_operator . ' $adv_filter_value;');
							if ($result === true)
								return true;
							break;
						case '!=':
							if (strcasecmp($adv_filter_value, $object_value) !== 0)
								return true;
							break;
						default:
							trigger_error(sprintf("Unknown filtering operator '%s'", $adv_filter_operator), E_USER_WARNING);
							//remove offending filter value
							unset($filter_values[$key]);
							break 2;
					}
				} else {
					if (strcasecmp($filter_value, $object_value) === 0)
						return true;
				}
			}
		}
		return false;
	}

	/**
	 * Deletes from server all objects in this result sets.
	 * WARNING: Use carefully!
	 */
	public function deleteAll() {
		foreach ($this->object_keys as $key) {
			$this->objects[$key]->delete();
		}
	}

	/**
	 * Returns formatted list of objects in this result set.
	 * Calls __toString method of every object.
	 */
	public function __toString() {
		$result = '';
		$count = 1;
		foreach ($this->object_keys as $key) {
			$result .= sprintf("%d. %s", $count++, $this->objects[$key]);
		}
		return $result;
	}
}
