<?php

namespace brimshot\Pattr\test;

require_once __DIR__ . "/../src/Pattr.php";
require_once "sampledata.php";

use PHPUnit\Framework\TestCase;
use function brimshot\Pattr\get_attributes_callback;
use function brimshot\Pattr\get_class_methods_with_attribute;
use function brimshot\Pattr\get_class_methods_with_attribute_callback;
use function brimshot\Pattr\get_object_properties_with_attribute;
use function brimshot\Pattr\get_class_properties_with_attribute;
use function brimshot\Pattr\get_object_properties_with_attribute_callback;
use function brimshot\Pattr\get_class_properties_with_attribute_callback;
use function brimshot\Pattr\has_attribute;
use function brimshot\Pattr\get_attribute;
use function brimshot\Pattr\get_attributes;
use function brimshot\Pattr\get_attribute_names;
use function brimshot\Pattr\has_attribute_callback;
use function brimshot\Pattr\does_not_have_attribute;
use function brimshot\Pattr\get_class_constants_with_attribute;
use function brimshot\Pattr\get_class_constants_with_attribute_callback;


final class PattrTest extends TestCase
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
	public function has_attribute_matches_children_by_default()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, ParentAttribute::class));
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
	public function has_attribute_works_on_objects()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function has_attribute_works_on_classes()
	{
		$this->assertTrue(has_attribute(ClassWithAttributes::class, FirstAttribute::class));
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

	/**
	 * @test
	 */
	public function has_attribute_returns_true_on_undeclared_attribute_as_string()
	{
		$this->assertTrue(has_attribute($this->ClassWithAttributes, 'UndeclaredAttribute'));
	}

	#endregion


	#region has_attribute_callback() tests

	/**
	 * @test
	 */
	public function has_attribute_callback_matches_on_callback()
	{
		$this->assertTrue(has_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function has_attribute_callback_returns_false_when_callback_returns_false()
	{
		$this->assertFalse(has_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 2));
	}

	/**
	 * @test
	 */
	public function has_attribute_callback_returns_false_on_unknown_attribute()
	{
		$this->assertFalse(has_attribute_callback($this->ClassWithAttributes, UnusedAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function has_attribute_callback_matches_child_attributes()
	{
		$this->assertTrue(has_attribute_callback($this->ClassWithAttributes, ParentAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function has_attribute_callback_does_not_match_child_attributes_in_strict_mode()
	{
		$this->assertTrue(has_attribute_callback($this->ClassWithAttributes, ParentAttribute::class, fn($a) => $a->id == 1), false);
	}

	#endregion


	#region does_not_have_attribute() tests

	/**
	 * @test
	 */
	public function does_not_have_attribute_returns_true_when_item_does_not_have_provided_attribute()
	{
		$this->assertTrue(does_not_have_attribute($this->ClassWithAttributes, UnusedAttribute::class));
	}

	/**
	 * @test
	 */
	public function does_not_have_attribute_returns_true_when_item_has_attribute()
	{
		$this->assertFalse(does_not_have_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function does_not_have_attribute_returns_false_when_item_has_some_of_provided_list()
	{
		$this->assertFalse(does_not_have_attribute($this->ClassWithAttributes, [UnusedAttribute::class, FirstAttribute::class]));
	}

	/**
	 * @test
	 */
	public function does_not_have_attribute_returns_true_when_no_attributes_in_provided_list_are_on_item()
	{
		$this->assertTrue(does_not_have_attribute($this->ClassWithAttributes, [UnusedAttribute::class, ThirdAttribute::class]));
	}

	/**
	 * @test
	 */
	public function does_not_have_attribute_matches_child_attributes()
	{
		$this->assertFalse(does_not_have_attribute($this->ClassWithAttributes, ParentAttribute::class));
	}

	/**
	 * @test
	 */
	public function does_not_have_attribute_strict_mode_for_child_attributes()
	{
		$this->assertTrue(does_not_have_attribute($this->ClassWithAttributes, ParentAttribute::class, false));
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
	public function get_attribute_works_on_this()
	{
		$this->assertEquals(new SecondAttribute(1), $this->ClassWithAttributes->firstMethod());
	}

	/**
	 * @test
	 */
	public function get_attribute_matches_child_attributes_by_default()
	{
		$this->assertInstanceOf(ChildAttribute::class, get_attribute($this->ClassWithAttributes, ParentAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_attribute_uses_strict_mode_when_match_child_attributes_is_off()
	{
		$this->assertNull(get_attribute($this->ClassWithAttributes, ParentAttribute::class, false));
	}

	/**
	 * @test
	 */
	public function get_attribute_matches_attribute_when_match_child_is_off()
	{
		$this->assertInstanceOf(FirstAttribute::class, get_attribute($this->ClassWithAttributes, FirstAttribute::class, false));
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
		$this->assertEquals(0, get_attribute($this->ClassWithAttributes, RepeatableAttribute::class, false)->value);
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_correct_instance_when_index_provided()
	{
		$this->assertEquals(1, get_attribute($this->ClassWithAttributes, RepeatableAttribute::class, true, 1)->value);
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_correct_instance_when_index_provided_and_attribute_short_name()
	{
		$this->assertEquals(1, get_attribute($this->ClassWithAttributes, 'RepeatableAttribute', true, 1)->value);
	}

	/**
	 * @test
	 */
	public function get_attribute_returns_null_when_provided_index_out_of_range()
	{
		$this->assertNull(get_attribute($this->ClassWithAttributes, FirstAttribute::class, true, 10));
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

	/**
	 * @test
	 */
	public function get_attribute_works_when_parent_and_child_attributes_on_same_item()
	{
		$this->assertInstanceOf(ParentAttribute::class, get_attribute(ClassWithParentAndChild::class, ParentAttribute::class, true, 1));
	}

	#endregion get_attribute() tests


	#region get_attributes() tests

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
			new SecondAttribute(1),
			new RepeatableAttribute(0),
			new RepeatableAttribute(1),
			new ChildAttribute(1)
		];

		$this->assertEquals($expectedAttributeInstances, get_attributes($this->ClassWithAttributes));
	}

	/**
	 * @test
	 */
	public function get_attributes_filters_by_passed_in_attribute_list()
	{
		$expectedResult = [
			new FirstAttribute()
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, [FirstAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_attributes_filters_on_multiple_attributes()
	{
		$expectedResult = [
			new FirstAttribute(),
			new SecondAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, [FirstAttribute::class, SecondAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_attributes_filters_on_repeated_attributes_and_includes_all_instances()
	{
		$expectedResult = [
			new RepeatableAttribute(0),
			new RepeatableAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, [RepeatableAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_attributes_filters_by_passed_in_attribute_when_attribute_are_strings()
	{
		$expectedResult = [
			new FirstAttribute()
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, ['FirstAttribute']));
	}

	/**
	 * @test
	 */
	public function get_attributes_works_on_class_path_not_instantiated_object()
	{
		$expectedResult = [
			new FirstAttribute()
		];

		$this->assertEquals($expectedResult, get_attributes(ClassWithAttributes::class, ['FirstAttribute']));
	}


	/**
	 * @test
	 */
	public function get_attributes_ignores_unrecognized_attributes_in_filter_list()
	{
		$expectedResult = [
			new FirstAttribute(),
			new SecondAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, ['FirstAttribute', SecondAttribute::class, UnusedAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_attributes_matches_child_attributes_by_default()
	{
		$expectedResult = [
			new FirstAttribute(),
			new ChildAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, [FirstAttribute::class, ParentAttribute::class]));
	}


	/**
	 * @test
	 */
	public function get_attributes_does_not_match_child_attributes_when_include_children_is_false()
	{
		$expectedResult = [
			new FirstAttribute()
		];

		$this->assertEquals($expectedResult, get_attributes($this->ClassWithAttributes, [FirstAttribute::class, ParentAttribute::class], false));
	}

	/**
	 * @test
	 */
	public function get_attributes_matches_both_parents_and_children_when_filtering()
	{
		$expectedResult = [
			new ParentAttribute(),
			new ChildAttribute(4)
		];

		$this->assertEquals($expectedResult, get_attributes(ClassWithParentAndChild::class, [ParentAttribute::class]));
	}


	#endregion get_attributes() tests


	#region get_attributes_callback() tests

	/**
	 * @test
	 */
	public function get_attributes_callback_returns_correct_array_when_callback_passes()
	{
		$expectedResult = [
			new SecondAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function get_attributes_callback_returns_empty_array_when_callback_returns_false()
	{
		$this->assertEquals([], get_attributes_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 123));
	}

	/**
	 * @test
	 */
	public function get_attributes_callback_returns_only_matching_on_repeated_attributes()
	{
		$expectedResult = [
			new RepeatableAttribute(1)
		];

		$this->assertEquals($expectedResult, get_attributes_callback($this->ClassWithAttributes, RepeatableAttribute::class, fn($a) => $a->value == 1));
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
			ChildAttribute::class,
			'brimshot\Pattr\test\UndeclaredAttribute'
		];

		$this->assertEquals($expectedAttributeNames, get_attribute_names($this->ClassWithAttributes));
	}

	/**
	 * @test
	 */
	public function get_attribute_names_returns_short_names()
	{
		$expectedAttributeNames = [
			'FirstAttribute',
			'SecondAttribute',
			'RepeatableAttribute',
			'RepeatableAttribute',
			'ChildAttribute',
			'UndeclaredAttribute'
		];

		$this->assertEquals($expectedAttributeNames, get_attribute_names($this->ClassWithAttributes, true));
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

	/**
	 * @test
	 */
	public function get_class_methods_with_attribute_matches_child_attributes()
	{
		$expectedMethodNames = [
			'secondMethod'
		];

		$this->assertEquals($expectedMethodNames, get_class_methods_with_attribute($this->ClassWithAttributes, [SecondAttribute::class, ParentAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_class_methods_with_attribute_does_not_match_child_attributes_in_strict_more()
	{
		$this->assertEquals([], get_class_methods_with_attribute($this->ClassWithAttributes, [SecondAttribute::class, ParentAttribute::class], false));
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


	#region get_object_properties_with_attribute() tests

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
	public function get_object_properties_does_not_work_on_private_properties()
	{
		$this->assertEquals([], get_object_properties_with_attribute($this->ClassWithAttributes, FourthAttribute::class));
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
	public function get_object_properties_with_attribute_matches_all_provided_attributes_when_given_as_strings()
	{
		$expectedResult = [
			'thirdClassProperty'=> 3
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute($this->ClassWithAttributes, ['firstattribute', 'thirdattribute']));
	}

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_matches_child_attributes_by_default()
	{
		$expectedResult = [
			'classProperty'=> 1
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute($this->ClassWithAttributes, [FirstAttribute::class, ParentAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_does_not_match_child_attributes_when_in_strict_mode()
	{
		$this->assertEquals([], get_object_properties_with_attribute($this->ClassWithAttributes, [FirstAttribute::class, ParentAttribute::class], false));
	}


	#endregion


	#region get_object_properties_with_attribute_callback() tests

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_callback_filters_on_callback()
	{
		$expectedResult = [
			'secondClassProperty'=> 2
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function get_object_properties_with_attribute_callback_matches_child_attributes()
	{
		$expectedResult = [
			'classProperty'=> 1
		];

		$this->assertEquals($expectedResult, get_object_properties_with_attribute_callback($this->ClassWithAttributes, ParentAttribute::class, fn($a) => $a->id == 1));
	}

	#endregion get_object_properties_with_attribute_callback() tests


	#region get_class_properties_with_attribute() tests

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
	public function get_class_properties_with_attribute_works_on_static_properties()
	{
		$expectedResult = [
			'secondClassProperty',
			'staticClassProperty'
		];

		$this->assertEquals($expectedResult, get_class_properties_with_attribute(ClassWithAttributes::class, SecondAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_class_properties_does_not_work_on_private_properties()
	{
		$this->assertEquals([], get_class_properties_with_attribute(ClassWithAttributes::class, FourthAttribute::class));
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

	/**
	 * @test
	 */
	public function get_class_properties_with_attribute_matches_child_attributes()
	{
		$expectedResult = [
			'classProperty'
		];

		$this->assertEquals($expectedResult, get_class_properties_with_attribute(ClassWithAttributes::class, [FirstAttribute::class, ParentAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_class_properties_with_attribute_does_not_match_child_attributes_in_strict_mode()
	{
		$this->assertEquals([], get_class_properties_with_attribute(ClassWithAttributes::class, [FirstAttribute::class, ParentAttribute::class], false));
	}


	#endregion get_class_properties_with_attribute() tests


	#region get_class_properties_with_attribute_callback() tests

	/**
	 * @test
	 */
	public function get_class_properties_callback_returns_empty_array_on_class_with_no_attributes()
	{
		$this->assertEquals([], get_class_properties_with_attribute_callback(ClassWithoutAttributes::class, FirstAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function get_class_properties_callback_returns_expected()
	{
		$expectedResult = [
			'secondClassProperty'
		];

		$this->assertEquals($expectedResult, get_class_properties_with_attribute_callback(ClassWithAttributes::class, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	/**
	 * @test
	 */
	public function get_class_properties_callback_returns_empty_array_when_callback_does_not_pass()
	{
		$this->assertEquals([], get_class_properties_with_attribute_callback(ClassWithAttributes::class, SecondAttribute::class, fn($a) => $a->id == 1000));
	}

	#endregion get_class_properties_with_attribute_callback() tests


	#region get_class_constants_with_attribute() tests

	/**
	 * @test
	 */
	public function get_class_constants_with_attribute_returns_empty_array_on_no_matches()
	{
		$this->assertEquals([], get_class_constants_with_attribute($this->ClassWithAttributes, UnusedAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_class_constants_with_attribute_matches_single_attribute()
	{
		$expectedResult = [
			'CLASS_CONSTANT'=> 1
		];

		$this->assertEquals($expectedResult, get_class_constants_with_attribute($this->ClassWithAttributes, FirstAttribute::class));
	}

	/**
	 * @test
	 */
	public function get_class_constants_with_attribute_matches_array_of_attributes()
	{
		$expectedResult = [
			'CLASS_CONSTANT'=> 1
		];

		$this->assertEquals($expectedResult, get_class_constants_with_attribute($this->ClassWithAttributes, [FirstAttribute::class, SecondAttribute::class]));
	}

	/**
	 * @test
	 */
	public function get_class_constants_matches_child_attributes()
	{
		$expectedResult = [
			'SECOND_CONSTANT'=> 2
		];

		$this->assertEquals($expectedResult, get_class_constants_with_attribute($this->ClassWithAttributes, ParentAttribute::class));
	}

	#endregion get_class_constants_with_attribute() tests


	#region get_class_constants_with_attribute_callback() tests

	/**
	 * @test
	 */
	public function get_class_constants_with_attribute_callback_returns_empty_array_when_no_results_pass_callback()
	{
		$this->assertEquals([], get_class_constants_with_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 100));
	}

	/**
	 * @test
	 */
	public function get_class_constants_with_attribute_callback_returns_constants_that_pass_callback()
	{
		$expectedResult = [
			'CLASS_CONSTANT'=> 1
		];

		$this->assertEquals($expectedResult, get_class_constants_with_attribute_callback($this->ClassWithAttributes, SecondAttribute::class, fn($a) => $a->id == 1));
	}

	#endregion get_class_constants_with_attribute_callback() tests
}