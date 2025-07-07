<?php

declare(strict_types=1);

namespace Ricklab\Location\Psalm;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;
use Symfony\Component\Finder\Finder;

final class Plugin implements PluginEntryPointInterface
{
    private const STUB_DIR = __DIR__.'/Stub';

    public function __invoke(RegistrationInterface $psalm, ?SimpleXMLElement $config = null): void
    {
        $stubs = $this->getStubFiles();

        foreach ($stubs as $file) {
            $psalm->addStubFile(self::STUB_DIR.'/'.$file->getRelativePathname());
        }
    }

    /**
     * @return \Traversable<array-key,SplFileInfo>
     */
    private function getStubFiles(): \Traversable
    {
        return Finder::create()
            ->in(self::STUB_DIR)
            ->name('*.phpstub')
            ;
    }
}
