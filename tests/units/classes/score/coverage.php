<?php

namespace mageekguy\atoum\tests\units\score;

use
	mageekguy\atoum,
	mageekguy\atoum\mock,
	mageekguy\atoum\score
;

require_once(__DIR__ . '/../../runner.php');

class coverage extends atoum\test
{
	public function test__construct()
	{
		$coverage = new score\coverage();

		$this->assert
			->object($coverage)->isInstanceOf('countable')
			->array($coverage->getMethods())->isEmpty()
		;
	}

	public function testSetReflectionClassInjector()
	{
		$coverage = new score\coverage();

		$this->mockGenerator
			->generate('reflectionClass')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};

		$reflectionClass = new \mock\reflectionClass(uniqid(), $classController);

		$this->assert
			->object($coverage->setReflectionClassInjector(function($class) use ($reflectionClass) { return $reflectionClass; }))->isIdenticalTo($coverage)
			->object($coverage->getReflectionClass(uniqid()))->isIdenticalTo($reflectionClass)
		;

		$this->assert
			->exception(function() use($coverage) {
						$coverage->setReflectionClassInjector(function() {});
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\logic\invalidArgument')
				->hasMessage('Reflection class injector must take one argument')
		;
	}

	public function testGetReflectionClass()
	{
		$coverage = new score\coverage();

		$this->assert
			->object($coverage->getReflectionClass($coverage))->isInstanceOf('reflectionClass')
		;

		$coverage->setReflectionClassInjector(function($class) { return uniqid(); });

		$this->assert
			->exception(function() use ($coverage) {
						$coverage->getReflectionClass($coverage);
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime\unexpectedValue')
				->hasMessage('Reflection class injector must return a \reflectionClass instance')
		;
	}

	public function testAddXdebugDataForTest()
	{
		$coverage = new score\coverage();

		$this->assert
			->object($coverage->addXdebugDataForTest($this, array()))->isIdenticalTo($coverage)
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->isAbstract = false;
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$coverage->setReflectionClassInjector(function($className) use ($class) { return $class; });

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => -1,
				6 => 1,
				7 => -1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$this->assert
			->object($coverage->addXdebugDataForTest($this, $xdebugData))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => 1,
							7 => -1,
							8 => -2
						)
					)
				)
			)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => 1,
							7 => -1,
							8 => -2
						)
					)
				)
			)
			->object($coverage->addXdebugDataForTest($this, $xdebugData))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => 1,
							7 => -1,
							8 => -2
						)
					)
				)
			)
		;
	}

	public function testReset()
	{
		$coverage = new score\coverage();

		$this->assert
			->array($coverage->getMethods())->isEmpty()
			->object($coverage->reset())->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEmpty()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$coverage->setReflectionClassInjector(function($className) use ($class) { return $class; });

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => 1,
				6 => 2,
				7 => 3,
				8 => 2,
				9 => 1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->array($coverage->getMethods())->isNotEmpty()
			->object($coverage->reset())->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEmpty()
		;
	}

	public function testMerge()
	{
		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => -2,
				6 => -1,
				7 => 1,
				8 => -2,
				9 =>-2
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage = new score\coverage();

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$this->assert
			->object($coverage->merge($coverage))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEmpty()
		;

		$otherCoverage = new score\coverage();

		$this->assert
			->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEmpty()
		;

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => -1,
							7 => 1,
							8 => -2
						)
					)
				)
			)
		;

		$this->assert
			->object($coverage->merge($coverage))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => -1,
							7 => 1,
							8 => -2
						)
					)
				)
			)
		;

		$otherClassController = new mock\controller();
		$otherClassController->__construct = function() {};
		$otherClassController->getName = function() use (& $otherClassName) { return $otherClassName; };
		$otherClassController->getFileName = function() use (& $otherClassFile) { return $otherClassFile; };

		$otherClass = new \mock\reflectionClass($class, $otherClassController);

		$otherMethodController = new mock\controller();
		$otherMethodController->__construct = function() {};
		$otherMethodController->getName = function() use (& $otherMethodName) { return $otherMethodName; };
		$otherMethodController->isAbstract = false;
		$otherMethodController->getFileName = function() use (& $otherClassFile) { return $otherClassFile; };
		$otherMethodController->getDeclaringClass = function() use ($otherClass) { return $otherClass; };
		$otherMethodController->getStartLine = 5;
		$otherMethodController->getEndLine = 9;

		$otherClassController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $otherMethodController));

		$otherClassFile = uniqid();
		$otherClassName = uniqid();
		$otherMethodName = uniqid();

		$otherXdebugData = array(
		  $otherClassFile =>
			 array(
				1 => -2,
				2 => -1,
				3 => 1,
				4 => 1,
				5 => -1,
				6 => 1,
				7 => 1,
				8 => -1,
				9 => -2,
				10 => 1
			),
		  uniqid() =>
			 array(
				500 => 200,
				600 => 300,
				700 => 400,
				800 => 300,
				900 => 200
			)
		);

		$otherCoverage
			->setReflectionClassInjector(function($class) use ($otherClass) { return $otherClass; })
		;

		$this->assert
			->object($coverage->merge($otherCoverage->addXdebugDataForTest($this, $otherXdebugData)))->isIdenticalTo($coverage)
			->array($coverage->getMethods())->isEqualTo(array(
					$className => array(
						$methodName => array(
							6 => -1,
							7 => 1,
							8 =>-2
						)
					),
					$otherClassName => array(
						$otherMethodName => array(
							5 => -1,
							6 => 1,
							7 => 1,
							8 => -1,
							9 => -2
						)
					)
				)
			)
		;
	}

	public function testCount()
	{
		$coverage = new score\coverage();

		$this->assert
			->sizeOf($coverage)->isZero()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 6;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				5 => 1,
				6 => 2,
				7 => 3,
				8 => 2,
				9 => 1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$this->assert
			->sizeOf($coverage->addXdebugDataForTest($this, $xdebugData))->isEqualTo(1)
		;
	}

	public function testClasses()
	{
		$coverage = new score\coverage();

		$this->assert
			->array($coverage->getClasses())->isEmpty()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() { return uniqid(); };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 4;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => -1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -2
			)
		);

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->array($coverage->getClasses())->isEqualTo(array($className => $classFile))
		;
	}

	public function testGetValue()
	{
		$coverage = new score\coverage();

		$this->assert
			->variable($coverage->getValue())->isNull()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() { return uniqid(); };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 4;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => -1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -2
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->float($coverage->getValue())->isEqualTo(0.0)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->float($coverage->getValue())->isEqualTo(1 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->float($coverage->getValue())->isEqualTo(2 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => 1,
				6 => 1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->float($coverage->getValue())->isEqualTo(1.0)
		;
	}

	public function testGetValueForClass()
	{
		$coverage = new score\coverage();

		$this->assert
			->variable($coverage->getValueForClass(uniqid()))->isNull()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class =  new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() { return uniqid(); };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 4;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => -1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -2
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForClass(uniqid()))->isNull()
			->float($coverage->getValueForClass($className))->isEqualTo(0.0)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForClass(uniqid()))->isNull()
			->float($coverage->getValueForClass($className))->isEqualTo(1 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForClass(uniqid()))->isNull()
			->float($coverage->getValueForClass($className))->isEqualTo(2 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => 1,
				6 => 1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForClass(uniqid()))->isNull()
			->float($coverage->getValueForClass($className))->isEqualTo(1.0)
		;
	}

	public function testGetValueForMethod()
	{
		$coverage = new score\coverage();

		$this->assert
			->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
		;

		$this->mockGenerator
			->generate('reflectionClass')
			->generate('reflectionMethod')
		;

		$classController = new mock\controller();
		$classController->__construct = function() {};
		$classController->getName = function() use (& $className) { return $className; };
		$classController->getFileName = function() use (& $classFile) { return $classFile; };

		$class = new \mock\reflectionClass(uniqid(), $classController);

		$methodController = new mock\controller();
		$methodController->__construct = function() {};
		$methodController->getName = function() use (& $methodName) { return $methodName; };
		$methodController->isAbstract = false;
		$methodController->getFileName = function() use (& $classFile) { return $classFile; };
		$methodController->getDeclaringClass = function() use ($class) { return $class; };
		$methodController->getStartLine = 4;
		$methodController->getEndLine = 8;

		$classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController));

		$classFile = uniqid();
		$className = uniqid();
		$methodName = uniqid();

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => -1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -2
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage
			->setReflectionClassInjector(function($className) use ($class) { return $class; })
		;

		$coverage->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
			->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
			->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(0.0)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
			->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
			->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(1 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => -1,
				6 => -1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
			->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
			->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(2 / 4)
		;

		$xdebugData = array(
		  $classFile =>
			 array(
				3 => -2,
				4 => 1,
				5 => 1,
				6 => 1,
				7 => 1,
				8 => -2,
				9 => -1
			),
		  uniqid() =>
			 array(
				5 => 2,
				6 => 3,
				7 => 4,
				8 => 3,
				9 => 2
			)
		);

		$coverage->reset()->addXdebugDataForTest($this, $xdebugData);

		$this->assert
			->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
			->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
			->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(1.0)
		;
	}
}

?>
