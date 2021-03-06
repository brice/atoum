<?php

namespace mageekguy\atoum\tests\units\report\fields\runner\duration;

use
	mageekguy\atoum,
	mageekguy\atoum\locale,
	mageekguy\atoum\cli\prompt,
	mageekguy\atoum\cli\colorizer,
	mageekguy\atoum\report\fields\runner\duration,
	mageekguy\atoum\tests\units
;

require_once(__DIR__ . '/../../../../../runner.php');

class cli extends units\report\fields\runner
{
	public function testClass()
	{
		$this->assert
			->class($this->getTestedClassName())->isSubclassOf('mageekguy\atoum\report\fields\runner')
		;
	}

	public function test__construct()
	{
		$field = new duration\cli();

		$this->assert
			->object($field->getPrompt())->isEqualTo(new prompt())
			->object($field->getTitleColorizer())->isEqualTo(new colorizer())
			->object($field->getDurationColorizer())->isEqualTo(new colorizer())
			->object($field->getLocale())->isEqualTo(new locale())
			->variable($field->getValue())->isNull()
		;

		$field = new duration\cli(null, null, null, null);

		$this->assert
			->object($field->getPrompt())->isEqualTo(new prompt())
			->object($field->getTitleColorizer())->isEqualTo(new colorizer())
			->object($field->getDurationColorizer())->isEqualTo(new colorizer())
			->object($field->getLocale())->isEqualTo(new locale())
			->variable($field->getValue())->isNull()
		;

		$field = new duration\cli($prompt = new prompt(), $titleColorizer = new colorizer(), $durationColorizer = new colorizer(), $locale = new locale());

		$this->assert
			->object($field->getPrompt())->isIdenticalTo($prompt)
			->object($field->getTitleColorizer())->isIdenticalTo($titleColorizer)
			->object($field->getDurationColorizer())->isIdenticalTo($durationColorizer)
			->object($field->getLocale())->isIdenticalTo($locale)
			->variable($field->getValue())->isNull()
		;
	}

	public function testSetPrompt()
	{
		$field = new duration\cli();

		$this->assert
			->object($field->setPrompt($prompt = new prompt(uniqid())))->isIdenticalTo($field)
			->object($field->getPrompt())->isIdenticalTo($prompt)
		;
	}

	public function testSetTitleColorizer()
	{
		$field = new duration\cli();

		$this->assert
			->object($field->setTitleColorizer($colorizer = new colorizer()))->isIdenticalTo($field)
			->object($field->getTitleColorizer())->isIdenticalTo($colorizer)
		;
	}

	public function testSetDurationColorizer()
	{
		$field = new duration\cli();

		$this->assert
			->object($field->setDurationColorizer($colorizer = new colorizer()))->isIdenticalTo($field)
			->object($field->getDurationColorizer())->isIdenticalTo($colorizer)
		;
	}

	public function testSetWithRunner()
	{
		$field = new duration\cli();

		$this->mockGenerator
			->generate('mageekguy\atoum\runner')
		;

		$runner = new \mock\mageekguy\atoum\runner();
		$runner->getMockController()->getRunningDuration = $runningDuration = rand(0, PHP_INT_MAX);

		$this->assert
			->variable($field->getValue())->isNull()
			->object($field->setWithRunner($runner))->isIdenticalTo($field)
			->variable($field->getValue())->isNull()
			->object($field->setWithRunner($runner, atoum\runner::runStart))->isIdenticalTo($field)
			->variable($field->getValue())->isNull()
			->object($field->setWithRunner($runner, atoum\runner::runStop))->isIdenticalTo($field)
			->integer($field->getValue())->isEqualTo($runningDuration)
		;
	}

	public function test__toString()
	{
		$this->mockGenerator
			->generate('mageekguy\atoum\runner')
			->generate('mageekguy\atoum\locale')
			->generate('mageekguy\atoum\cli\prompt')
			->generate('mageekguy\atoum\cli\colorizer')
		;

		$runner = new \mock\mageekguy\atoum\runner();
		$runner->getMockController()->getRunningDuration = 1;

		$prompt = new \mock\mageekguy\atoum\cli\prompt();
		$prompt->getMockController()->__toString = $promptString = uniqid();

		$titleColorizer = new \mock\mageekguy\atoum\cli\colorizer();
		$titleColorizer->getMockController()->colorize = $colorizedTitle = uniqid();

		$durationColorizer = new \mock\mageekguy\atoum\cli\colorizer();
		$durationColorizer->getMockController()->colorize = $colorizedDuration = uniqid();

		$locale = new \mock\mageekguy\atoum\locale();
		$locale->getMockController()->_ = function($string) {
			return $string;
		};

		$field = new duration\cli($prompt, $titleColorizer, $durationColorizer, $locale);

		$this->assert
			->castToString($field)->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner, atoum\runner::runStart))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner, atoum\runner::runStop))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('__')->withArguments('%4.2f second', '%4.2f seconds', 1)->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('1.00 second')->once()
		;

		$runner->getMockController()->getRunningDuration = $runningDuration = rand(2, PHP_INT_MAX);

		$field = new duration\cli($prompt, $titleColorizer, $durationColorizer, $locale);

		$this->assert
			->castToString($field)->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner, atoum\runner::runStart))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('_')->withArguments('unknown')->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments('unknown')->once()
		;

		$this->assert
			->castToString($field->setWithRunner($runner, atoum\runner::runStop))->isEqualTo($promptString . $colorizedTitle . ': ' . $colorizedDuration . '.' . PHP_EOL)
			->mock($locale)
				->call('_')->withArguments('Running duration')->once()
				->call('__')->withArguments('%4.2f second', '%4.2f seconds', $runningDuration)->once()
				->call('_')->withArguments('%1$s: %2$s.')->once()
			->mock($titleColorizer)
				->call('colorize')->withArguments('Running duration')->once()
			->mock($durationColorizer)
				->call('colorize')->withArguments(sprintf('%4.2f', $runningDuration) . ' seconds')->once()
		;
	}
}

?>
