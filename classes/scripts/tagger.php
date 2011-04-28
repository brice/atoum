<?php

namespace mageekguy\atoum\scripts;

use
	\mageekguy\atoum,
	\mageekguy\atoum\exceptions
;

class tagger extends atoum\script
{
	const versionTag = '<tagger:version />';

	protected $srcDirectory = null;
	protected $destinationDirectory = null;
	protected $srcIteratorInjector = null;

	public function getSrcDirectory()
	{
		return $this->srcDirectory;
	}

	public function setSrcDirectory($directory)
	{
		$this->srcDirectory = (string) $directory;

		if ($this->destinationDirectory === null)
		{
			$this->destinationDirectory = $this->srcDirectory;
		}

		return $this;
	}

	public function getDestinationDirectory()
	{
		return $this->destinationDirectory;
	}

	public function setDestinationDirectory($directory)
	{
		$this->destinationDirectory = (string) $directory;

		return $this;
	}

	public function setSrcIteratorInjector(\closure $srcIteratorInjector)
	{
		$closure = new \reflectionMethod($srcIteratorInjector, '__invoke');

		if ($closure->getNumberOfParameters() != 1)
		{
			throw new exceptions\logic('Src iterator injector must take one argument');
		}

		$this->srcIteratorInjector = $srcIteratorInjector;

		return $this;
	}

	public function getSrcIterator()
	{
		if ($this->srcDirectory === null)
		{
			throw new exceptions\logic('Unable to get files iterator, source directory is undefined');
		}

		if ($this->srcIteratorInjector === null)
		{
			$this->setSrcIteratorInjector(function ($directory) { return new \recursiveIteratorIterator(new atoum\src\iterator\filter(new \recursiveDirectoryIterator($directory))); });
		}

		return $this->srcIteratorInjector->__invoke($this->srcDirectory);
	}

	public function tagVersion($version)
	{
		if ($this->srcDirectory === null)
		{
			throw new exceptions\logic('Unable to tag, src directory is undefined');
		}

		$srcIterator = $this->getSrcIterator();

		if ($srcIterator instanceof \iterator === false)
		{
			throw new exceptions\logic('Unable to tag, src iterator injector does not return an iterator');
		}

		foreach ($srcIterator as $path)
		{
			$this->adapter->file_put_contents($path, str_replace(self::versionTag, $version, $this->adapter->file_get_contents($path)));
		}

		return $this;
	}
}

?>