<?php

namespace brimshot\PhpAttributes\internal {

	/**
	 * @param mixed $item
	 * @return \Reflector|null
	 */
	function _reflector_factory(mixed $item) : \Reflector
	{
		try {

			if (is_object($item)) {
				return new \ReflectionClass($item);
			}

			if(is_string($item)) {
				if (class_exists($item)) {
					return new \ReflectionClass($item);
				}

				if (function_exists($item)) {
					return new \ReflectionFunction($item);
				}
			}

			if(is_array($item) && (count($item) == 2)) {

				if(method_exists(...$item)) {
					return new \ReflectionMethod(...$item);
				}

				if(property_exists(...$item)) {
					return new \ReflectionProperty(...$item);
				}

				return new \ReflectionClassConstant(...$item);
			}

		} catch (\ReflectionException $e) {
			// Fall through to null object return
		}

		// Return null object to allow execution to continue
		return new \ReflectionClass(new class {});
	}

	/**
	 * @param \ReflectionAttribute $attribute
	 * @return object|null
	 */
	function _safe_new_instance(\ReflectionAttribute $attribute) : ?object
	{
		return class_exists($attribute->getName())? $attribute->newInstance():null;
	}

	/**
	 * @param string $qualifiedClassName
	 * @return string
	 */
	function _short_class_name(string $qualifiedClassName, $returnInlowerCase = true) : string
	{
		$shortName = basename(str_replace('\\', DIRECTORY_SEPARATOR, $qualifiedClassName));
		return $returnInlowerCase? strtolower($shortName) : $shortName;
	}

	/**
	 * @param mixed $item
	 * @param string|array $attribute
	 * @param bool $matchChildAttributes
	 * @return bool
	 */
	function _has_attribute(mixed $item, string $attribute, bool $matchChildAttributes = true) : bool
	{
		if(class_exists($attribute))
			return (!! (_reflector_factory($item))->getAttributes($attribute, ($matchChildAttributes? \ReflectionAttribute::IS_INSTANCEOF:0)));

		// Attribute parameter was not a recognized class - try to match an unqualified attribute name
		return in_array(strtolower($attribute), array_map(fn($a)=> _short_class_name($a->getName()), _reflector_factory($item)->getAttributes()));
	}

}

namespace brimshot\PhpAttributes {

	use function brimshot\PhpAttributes\internal\_reflector_factory;
	use function brimshot\PhpAttributes\internal\_safe_new_instance;
	use function brimshot\PhpAttributes\internal\_has_attribute;
	use function brimshot\PhpAttributes\internal\_short_class_name;

	/**
	 * @param mixed $item
	 * @param string|array $attribute_or_array_of_attributes
	 * @param bool $matchChildAttributes
	 * @return bool
	 */
	function has_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : bool
	{
		// todo: refactor
		if(is_array($attribute_or_array_of_attributes)) {
			foreach($attribute_or_array_of_attributes as $attribute) {
				if(! _has_attribute($item, $attribute, $matchChildAttributes))
					return false;
			}

			return true;
		}

		return _has_attribute($item, $attribute_or_array_of_attributes, $matchChildAttributes);
	}

	/**
	 * @param mixed $item
	 * @param string $attribute
	 * @param callable $callback
	 * @return bool
	 */
	function has_attribute_callback(mixed $item, string $attribute, callable $callback) : bool
	{
		return ($attribute = get_attribute($item, $attribute))? (!! $callback($attribute)) : false;
	}

	/**
	 * @param mixed $item
	 * @param string|array $attribute_or_array_of_attributes
	 * @param bool $matchChildAttributes
	 * @return bool
	 */
	function does_not_have_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : bool
	{
		if(is_array($attribute_or_array_of_attributes)) {
			foreach($attribute_or_array_of_attributes as $attribute) {
				if(_has_attribute($item, $attribute, $matchChildAttributes))
					return false;
			}

			return true;
		}

		return ! _has_attribute($item, $attribute_or_array_of_attributes, $matchChildAttributes);

	}

	/**
	 * @param mixed $item
	 * @return array
	 */
	function get_attribute_names(mixed $item, bool $shortNames = false) : array
	{
		$names = array_map(fn($a) => $a->getName(), _reflector_factory($item)->getAttributes());

		return ($shortNames)? array_map(fn($n) => _short_class_name($n, false), $names) : $names;
	}

	/**
	 * @param mixed $item
	 * @param string $attribute
	 * @param int $index
	 * @return \object|null
	 */
	function get_attribute(mixed $item, string $attribute, int $index = 0, $matchChildAttributes = true) : ?object
	{
		// todo: what happens when you pass in index 2 and there's only one attribute
		// todo: refactor maybe
		foreach((_reflector_factory($item))->getAttributes() as $a) {
			if(((strtolower($attribute) == _short_class_name($a->getName())) || ($matchChildAttributes && (_safe_new_instance($a) instanceof $attribute)))) {
				if(! $index)
					return _safe_new_instance($a);
				$index--;
			}
		}

		return null;
	}

	/**
	 * @param mixed $item
	 * @return array
	 */
	function get_attributes(mixed $item, array $attribute_list = []) : array
	{
		// todo: this needs to account for children

		$allAttributes = array_filter(array_map(fn($a) => _safe_new_instance($a), _reflector_factory($item)->getAttributes()), fn($a)=> !is_null($a));

		return empty($attribute_list)? $allAttributes : array_filter($allAttributes, fn($a) => in_array(get_class($a), $attribute_list) || in_array(_short_class_name(get_class($a)), $attribute_list));
	}

	/**
	 * @param mixed $item
	 * @param callable $callback
	 * @return array
	 */
	function get_attributes_callback(mixed $item, string $attribute, callable $callback) : array
	{
		return array_values(array_filter(get_attributes($item, [$attribute]), $callback));
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @return array
	 */
	function get_class_methods_with_attribute(object|string $object_or_class, string|array $attribute_or_array_of_attributes) : array
	{
		return array_values(array_filter(get_class_methods($object_or_class), fn($m) => has_attribute([$object_or_class, $m], $attribute_or_array_of_attributes)));
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @param callable $callback
	 * @return array
	 */
	function get_class_methods_with_attribute_callback(object|string $object_or_class, string $attribute, callable $callback) : array
	{
		// todo: should accept an array
		return array_values(array_filter(get_class_methods_with_attribute($object_or_class, $attribute), fn($m) => $callback(get_attribute([$object_or_class, $m], $attribute))));
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @return array
	 */
	function get_object_properties_with_attribute(object $object, string|array $attribute_or_array_of_attributes) : array
	{
			return array_reduce(
				array_filter(array_keys(get_object_vars($object)), fn($p) => has_attribute([$object, $p], $attribute_or_array_of_attributes)),
				fn($accum, $p) => $accum + [$p => $object->$p],
				[]
			);
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @param callable $callback
	 * @return array
	 */
	function get_object_properties_with_attribute_callback(object $object, string $attribute, callable $callback) : array
	{
		return array_reduce(
			array_filter(array_keys(get_object_vars($object)), fn($p) => has_attribute_callback([$object, $p], $attribute, $callback)),
			fn($accum, $p) => $accum + [$p => $object->$p],
			[]
		);
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @return array
	 */
	function get_class_properties_with_attribute(string $class, string|array $attribute_or_array_of_attributes) : array
	{
		return array_reduce(
			array_filter(array_keys(get_class_vars($class)), fn($p) => has_attribute([$class, $p], $attribute_or_array_of_attributes)),
			fn($accum, $p) => array_merge($accum, [$p]),
			[]
		);
	}

	/**
	 * @param object|string $object_or_class
	 * @param string $attribute
	 * @param callable $callback
	 * @return array
	 */
	function get_class_properties_with_attribute_callback(string $class, string $attribute, callable $callback) : array
	{
		return array_reduce(
			array_filter(array_keys(get_class_vars($class)), fn($p) => has_attribute_callback([$class, $p], $attribute, $callback)),
			fn($accum, $p) => array_merge($accum, [$p]),
			[]
		);
	}

	/**
	 * @param string $class_or_object
	 * @param string|array $attribute_or_array_of_attributes
	 * @return array
	 */
	function get_class_constants_with_attribute(string|object $class_or_object, string|array $attribute_or_array_of_attributes) : array
	{
		$reflection = _reflector_factory($class_or_object);

		return array_reduce(
			array_filter(array_keys($reflection->getConstants()), fn($constName) => has_attribute([$class_or_object, $constName], $attribute_or_array_of_attributes)),
			fn($accum, $constName) => $accum + [$constName => $reflection->getConstant($constName)],
			[]
		);
	}

	/**
	 * @param string|object $class_or_object
	 * @param string $attribute
	 * @param callable $callback
	 * @return array
	 */
	function get_class_constants_with_attribute_callback(string|object $class_or_object, string $attribute, callable $callback) : array
	{
		$reflection = _reflector_factory($class_or_object);

		return array_reduce(
			array_filter(array_keys($reflection->getConstants()), fn($constName) => has_attribute_callback([$class_or_object, $constName], $attribute, $callback)),
			fn($accum, $constName) => $accum + [$constName => $reflection->getConstant($constName)],
			[]
		);
	}



}