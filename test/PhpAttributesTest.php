<?php

namespace brimshot\PhpAttributes\Test;

require_once __DIR__ . "/../src/PhpAttributes.php";

use PHPUnit\Framework\TestCase;
use function brimshot\PhpAttributes\get_class_methods_with_attribute;
use function brimshot\PhpAttributes\get_class_methods_with_attribute_callback;
use function brimshot\PhpAttributes\get_object_properties_with_attribute;
use function brimshot\PhpAttributes\get_class_properties_with_attribute;
use function brimshot\PhpAttributes\has_attribute;
use function brimshot\PhpAttributes\get_attribute;
use function brimshot\PhpAttributes\get_attributes;
use function brimshot\PhpAttributes\get_attribute_names;

#region dummy test data

// ~ Attributes

#[\Attribute]
class UnusedAttribute {}

#[\Attribute]
class FirstAttribute {}

#[\Attribute]
class SecondAttribute
{
	public function __construct(public $id = 0) {}
}

#[\Attribute]
class ThirdAttribute {}

#[\Attribute]
class ParentAttribute {}

#[\Attribute]
class ChildAttribute extends ParentAttribute {}

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
class RepeatableAttribute
{
	public function __construct(public int $value) {}
}

// ~ Classes

#[FirstAttribute]
#[SecondAttribute]
#[RepeatableAttribute(0)]
#[RepeatableAttribute(1)]
#[ChildAttribute]
class ClassWithAttributes
{
	#[FirstAttribute]
	const CLASS_CONSTANT = '1';

	#[FirstAttribute]
	public $classProperty = 1;

	#[SecondAttribute]
	public $secondClassProperty = 2;

	#[FirstAttribute, ThirdAttribute]
	public $thirdClassProperty = 3;

	#[SecondAttribute]
	public static $staticClassProperty = 'static';

	#[FirstAttribute]
	public function firstMethod() {}

	#[SecondAttribute]
	public function secondMethod() {}

	#[FirstAttribute, SecondAttribute(1)]
	public function thirdMethod() {}

}

class ClassWithoutAttributes {}

// ~ Functions

#[FirstAttribute]
function dummyFunction() {}

#endregion


#region unit tests

/**** Main test class ****/

final class PhpAttributesTest extends TestCase
{
	private $ClassWithAttributes;
	private $NoAttributesClass;

	public function setUp() : void
	{
		$this->ClassWithAttributes = new ClassWithAttributes();
		$this->NoAttributesClass = new ClassWithoutAttributes();
	}



	#region has_attribute() tests

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_item_is_null()
	{
		$this->assertFalse(has_attribute(null, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_item_is_invalid_array()
	{
		$this->assertFalse(has_attribute([1,2,3,4], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_item_unrecognized()
	{
		$this->assertFalse(has_attribute('lorem ipsum dolor sit amet', FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_item_is_two_entry_array_but_does_not_resolve_to_anything()
	{
		$this->assertFalse(has_attribute(['lorem ipsum dolor sit amet', 'consectetuer'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_true_when_attribute_passed_as_class_path()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_class_has_no_attributes()
	{
		$this->assertFalse(has_attribute($this->NoAttributesClass, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_attribute_not_present_and_attribute_passed_as_class_path()
	{
		$this->assertFalse(has_attribute($this->ClassWithAttributes, UnusedAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_true_when_attribute_name_passed_as_string()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, "FirstAttribute"));
	}

	/**
	 * @test
	 */
	public function has_attribute__returns_true_when_attribute_name_passed_as_string_case_insensitive()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, "firstattribute"));
	}

	/**
	 * @test
	 */
	public function has_attribute__returns_false_when_bad_attribute_name_passed_as_string()
	{
		$this->assertFalse(has_attribute($this->ClassWithAttributes, "FakeAttribute"));
	}

	/**
	 * @test
	 */
	public function has_attribute_does_not_match_children_by_default()
	{
		$this->assertFalse(has_attribute($this->ClassWithAttributes, ParentAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_matches_children_when_flag_provided()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, ParentAttribute::class, true));
	}

	/**
	 * @test
	 */
	public function has_attribute_matches_multiple_when_array_provided()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, [FirstAttribute::class, SecondAttribute::class]));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_functions()
	{
		$this->assertTrue(has_attribute(__NAMESPACE__ . '\dummyFunction', FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_class_constants()
	{
		$this->assertTrue(has_attribute([$this->ClassWithAttributes, 'CLASS_CONSTANT'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_class_properties()
	{
		$this->assertTrue(has_attribute([$this->ClassWithAttributes, 'classProperty'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_static_class_properties()
	{
		$this->assertTrue(has_attribute([$this->ClassWithAttributes, 'staticClassProperty'], SecondAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_class_methods_given_class_path()
	{
		$this->assertTrue(has_attribute([ClassWithAttributes::class, 'firstMethod'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_class_methods_with_instantiated_object()
	{
		$this->assertTrue(has_attribute([$this->ClassWithAttributes, 'firstMethod'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_returns_false_when_class_member_not_defined()
	{
		$this->assertFalse(has_attribute([ClassWithAttributes::class, 'BAD_PROP_NAME'], FirstAttribute::class));
	}

	#endregion


	#region get_attribute() tests

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_item_is_null()
	{
		$this->assertNull(get_attribute(null, 'lorem_ipsum'));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_item_not_recognized()
	{
		$this->assertNull(get_attribute('lorem ipsum dolor', FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_item_is_two_entry_array_that_does_not_resolve()
	{
		$this->assertNull(get_attribute(['lorem ipsom', 'dolor'], FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_instance_when_attribute_class_name_provided()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_attribute_not_recognized()
	{
		$this->assertNull(get_attribute($this->ClassWithAttributes, UnusedAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_class_has_no_attributes()
	{
		$this->assertNull(get_attribute($this->NoAttributesClass, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_instance_when_attribute_name_passed_as_unqualified_string()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute($this->ClassWithAttributes, "FirstAttribute"));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_instance_when_attribute_name_passed_as_unqualified_string_case_insensitive()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute($this->ClassWithAttributes, "fIrstAtTriBute"));
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_first_of_repeated_attributes_by_default()
	{
		$this->assertEquals(0, get_attribute($this->ClassWithAttributes, RepeatableAttribute::class)->value);
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_correct_instance_when_index_provided()
	{
		$this->assertEquals(1, get_attribute($this->ClassWithAttributes, RepeatableAttribute::class, 1)->value);
	}

	/**
	 * @test
	 */
	public function get_attribute_works_on_class_methods()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute([$this->ClassWithAttributes, 'firstMethod'], FirstAttribute::class))	;
	}

	/**
	 * @test
	 */
	public function get_attribute_works_on_class_methods_with_only_attribute_name()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute([$this->ClassWithAttributes, 'firstMethod'], 'FirstAttribute'))	;
	}


	#endregion


	#region get_attributes() test

	/**
	 * @test
	 */
	public function get_attributes_returns_empty_array_when_class_has_no_attributes()
	{
		$this->assertEquals([], get_attributes($this->NoAttributesClass));
	}

	/**
	 * @test
	 */
	public function get_attributes_returns_expected_instances_on_class_with_attributes()
	{
		$expectedAttributeInstances = [
			new FirstAttribute(),
			new SecondAttribute(),
			new RepeatableAttribute(0),
			new RepeatableAttribute(1),
			new ChildAttribute()
		];

		$this->assertEquals($expectedAttributeInstances, get_attributes($this->ClassWithAttributes));
	}

	/**
	 * @test
	 */
	public function get_attributes_filters_by_passed_in_attribute_list()
	{
		$expectedAttributeInstances = [
			new FirstAttribute()
		];

		$this->assertEquals($expectedAttributeInstances, get_attributes($this->ClassWithAttributes, [FirstAttribute::class]));
	}

	#endregion

	#region get_attribute_names() tests

	/**
	 * @test
	 */
	public function get_attribute_names_returns_expected_list()
	{
		$expectedAttributeNames = [
			FirstAttribute::class,
			SecondAttribute::class,
			RepeatableAttribute::class,
			RepeatableAttribute::class,
			ChildAttribute::class
		];

		$this->assertEquals($expectedAttributeNames, get_attribute_names($this->ClassWithAttributes));
	}

	#endregion

	#region get_class_methods_with_attribute() tests

	/**
	 * @test
	 */
	public function get_class_methods_with_attribute_matches_single_attribute()
	{
		$expectedMethodNames = [
			'firstMethod',
			'thirdMethod'
		];

		$this->assertEquals($expectedMethodNames, get_class_methods_with_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_class_methods_with_attribute_matches_multiple_attributes()
	{
		$expectedMethodNames = [
			'thirdMethod'
		];

		$this->assertEquals($expectedMethodNames, get_class_methods_with_attribute($this->ClassWithAttributes, [FirstAttribute::class, SecondAttribute::class]));
	}


	#endregion



	#region get_class_methods_with_attribute_callback() tests

	/**
	 * @test
	 */
	public function get_class_methods_with_attribute_callback_filters_on_callback()
	{
		$expectedMethodNames = [
			'thirdMethod'
		];

		$this->assertEquals($expectedMethodNames, get_class_methods_with_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	#endregion


	#region get_class_properties_with_attribute() tests

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_returns_matching_properties_on_instantiated_class()
	{
		$expectedResult = [
			'classProperty'=> 1,
			'thirdClassProperty'=> 3
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_filter_on_array_of_attributes_on_instantiated_class()
	{
		$expectedResult = [
			'thirdClassProperty'=> 3
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute($this->ClassWithAttributes, [FirstAttribute::class, ThirdAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_filter_on_array_of_attribute_names()
	{
		$expectedResult = [
			'thirdClassProperty'=> 3
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute($this->ClassWithAttributes, ['firstattribute', 'thirdattribute']));
	}

	/**
	 * @test
	 */
	public function get_class_properties_with_attribute_returns_matching_properties_on_class_path()
	{
		$expectedResult = [
			'classProperty',
			'thirdClassProperty'
		];

		$this->assertEquals($expectedResult, get_class_properties_with_attribute(ClassWithAttributes::class, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_class_properties_with_attribute_filter_on_array_of_attributes_on_class_path()
	{
		$expectedResult = [
			'thirdClassProperty'
		];

		$this->assertEquals($expectedResult, get_class_properties_with_attribute(ClassWithAttributes::class, [FirstAttribute::class, ThirdAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_class_properties_returns_empty_array_on_class_with_no_attributes()
	{
		$this->assertEquals([], get_class_properties_with_attribute(ClassWithoutAttributes::class, FirstAttribute::class));
	}

	#endregion
}

#endregion