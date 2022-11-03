<?php

namespace brimshot\Pattr\test;

// ~ Attributes

use function brimshot\Pattr\get_attribute;

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
class FourthAttribute {}

#[\Attribute]
class ParentAttribute {}

#[\Attribute]
class ChildAttribute extends ParentAttribute
{
	public function __construct(public $id = 0) {}
}

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
class RepeatableAttribute
{
	public function __construct(public int $value) {}
}

// ~ Classes

#[FirstAttribute]
#[SecondAttribute(1)]
#[RepeatableAttribute(0)]
#[RepeatableAttribute(1)]
#[ChildAttribute(1)]
#[UndeclaredAttribute]
class ClassWithAttributes
{
	#[FirstAttribute, SecondAttribute(1)]
	const CLASS_CONSTANT = 1;

	#[SecondAttribute, ChildAttribute]
	const SECOND_CONSTANT = 2;

	#[FirstAttribute, ChildAttribute(1)]
	public $classProperty = 1;

	#[SecondAttribute(1)]
	public $secondClassProperty = 2;

	#[FirstAttribute, ThirdAttribute]
	public $thirdClassProperty = 3;

	#[FourthAttribute]
	private $privateProperty = 'private';

	#[SecondAttribute]
	public static $staticClassProperty = 'static';

	#[FirstAttribute]
	public function firstMethod()
	{
		return get_attribute($this, SecondAttribute::class);
	}

	#[SecondAttribute, ChildAttribute]
	public function secondMethod() {}

	#[FirstAttribute, SecondAttribute(1)]
	public function thirdMethod() {}
}

class ClassWithoutAttributes {}

#[ParentAttribute, ChildAttribute(4)]
class ClassWithParentAndChild {}

// ~ Functions

#[FirstAttribute]
function dummyFunction() {}
