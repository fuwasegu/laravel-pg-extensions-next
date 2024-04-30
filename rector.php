<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;

return RectorConfig::configure()
    ->withRules([
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withConfiguredRule(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute('test', Test::class),
    ])
    ->withConfiguredRule(AnnotationWithValueToAttributeRector::class, [
        new AnnotationWithValueToAttribute('dataProvider', DataProvider::class),
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    );
