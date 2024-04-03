<?php

declare(strict_types=1);

namespace TomasVotruba\Finalize\Tests\ParentClassResolver;

use PHPUnit\Framework\TestCase;
use TomasVotruba\Finalize\DependencyInjection\ContainerFactory;
use TomasVotruba\Finalize\FileSystem\PhpFilesFinder;
use TomasVotruba\Finalize\ParentClassResolver;
use TomasVotruba\Finalize\Tests\ParentClassResolver\Fixture\SomeParentClass;

final class ParentClassResolverTest extends TestCase
{
    private ParentClassResolver $parentClassResolver;

    protected function setUp(): void
    {
        $containerFactory = new ContainerFactory();
        $container = $containerFactory->create();

        $this->parentClassResolver = $container->make(ParentClassResolver::class);
    }

    public function test(): void
    {
        $phpFileInfos = PhpFilesFinder::findPhpFileInfos([__DIR__ . '/Fixture']);

        $parentClassNames = $this->parentClassResolver->resolve($phpFileInfos, function () {
        });

        $this->assertSame([
            \SomeUnknownRootNamespaceClass::class, // @phpstan-ignore-line
            SomeParentClass::class
        ], $parentClassNames);
    }
}
