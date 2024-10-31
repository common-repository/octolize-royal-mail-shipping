<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace OctolizeShippingRoyalMailVendor\Symfony\Component\Translation\Loader;

use OctolizeShippingRoyalMailVendor\Symfony\Component\Translation\Exception\InvalidResourceException;
use OctolizeShippingRoyalMailVendor\Symfony\Component\Translation\Exception\LogicException;
use OctolizeShippingRoyalMailVendor\Symfony\Component\Yaml\Exception\ParseException;
use OctolizeShippingRoyalMailVendor\Symfony\Component\Yaml\Parser as YamlParser;
use OctolizeShippingRoyalMailVendor\Symfony\Component\Yaml\Yaml;
/**
 * YamlFileLoader loads translations from Yaml files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlFileLoader extends FileLoader
{
    private $yamlParser;
    /**
     * {@inheritdoc}
     */
    protected function loadResource(string $resource)
    {
        if (null === $this->yamlParser) {
            if (!class_exists(\OctolizeShippingRoyalMailVendor\Symfony\Component\Yaml\Parser::class)) {
                throw new LogicException('Loading translations from the YAML format requires the Symfony Yaml component.');
            }
            $this->yamlParser = new YamlParser();
        }
        try {
            $messages = $this->yamlParser->parseFile($resource, Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new InvalidResourceException(sprintf('The file "%s" does not contain valid YAML: ', $resource) . $e->getMessage(), 0, $e);
        }
        if (null !== $messages && !\is_array($messages)) {
            throw new InvalidResourceException(sprintf('Unable to load file "%s".', $resource));
        }
        return $messages ?: [];
    }
}
