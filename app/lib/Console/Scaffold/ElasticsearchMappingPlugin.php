<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Scaffold;

use Trellis\CodeGen\PluginInterface;
use Trellis\CodeGen\Schema\EntityTypeDefinition;
use Trellis\CodeGen\Schema\EntityTypeSchema;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Image\Image;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A plugin for the trellis code-generator.
 * Generates and deploys an elasticsearch type mapping for a given entity-type-definition.
 */
class ElasticsearchMappingPlugin implements PluginInterface
{
    const FILE_MODE = 0644;

    protected static $es_type_map = [
        'asset' => 'object',
        'boolean' => 'boolean',
        'boolean-list' => 'boolean',
        'choice' => 'string',
        'date' => 'date',
        'email' => 'string',
        'email-list' => 'string',
        'embedded-entity-list' => 'object',
        'entity-reference-list' => 'object',
        'float' => 'double',
        'float-list' => 'double',
        'geo-point' => 'geo_point',
        'image' => 'object',
        'image-list' => 'object',
        'integer' => 'integer',
        'integer-list' => 'integer',
        'key-value-list' => 'object',
        'text' => 'string',
        'text-list' => 'string',
        'textarea' => 'string',
        'timestamp' => 'date',
        'token' => 'string',
        'url' => 'string',
        'uuid' => 'string'
    ];

    protected $options;

    protected $file_system;

    public function __construct(Options $options)
    {
        $this->options = $options;
        $this->file_system = new Filesystem();
    }

    public function execute(EntityTypeSchema $type_schema)
    {
        $type_definition = $type_schema->getEntityTypeDefinition();
        $type_properties = $this->buildTypePropertiesMapping($type_definition);
        $index_name = $this->options->get('index_name');
        $type_name = $this->options->get('type_name');

        $this->file_system->dumpFile(
            $this->options['deploy_path'],
            json_encode(
                [
                    'properties' => (object)$type_properties
                ],
                JSON_PRETTY_PRINT
            ),
            self::FILE_MODE
        );
    }

    protected function buildTypePropertiesMapping(EntityTypeDefinition $type_definition)
    {
        $type_properties = [];
        foreach ($type_definition->getAttributes() as $attribute) {
            $handler_function = sprintf(
                'map%s',
                implode('', array_map('ucfirst', explode('-', $attribute->getShortName())))
            );
            if (is_callable(array($this, $handler_function))) {
                $mapping = $this->$handler_function(
                        $attribute->getName(), $attribute, $type_definition
                );
                if (!empty($mapping)) {
                    $type_properties[$attribute->getName()] = $mapping;
                }
            }
        }

        return $type_properties;
    }

    protected function mapText($attribute_name, $attribute, $type_definition)
    {
        return [
            'type' => 'string',
            'fields' => [
                'sort' => [
                    'type' => 'string',
                    'analyzer' => 'IcuAnalyzer_DE',
                    'include_in_all' => false
                ],
                'filter' => [
                    'type' => 'string',
                    'index' => 'not_analyzed'
                ],
                'suggest' => [
                    'type' => 'string',
                    'analyzer' => 'AutoCompleteAnalyzer',
                    'include_in_all' => false
                ]
            ]
        ];
    }

    protected function mapEmail($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapEmailList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapUrl($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'index' => 'no' ];
    }

    protected function mapTimestamp($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapDate($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapChoice($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapTextList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapTextarea($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapInteger($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapIntegerList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapFloat($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapFloatList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapBoolean($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapBooleanList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapKeyValueList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapImage($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapImageList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapEmbeddedEntityList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapEntityReferenceList($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapAsset($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapGeoPoint($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()] ];
    }

    protected function mapToken($attribute_name, $attribute, $type_definition)
    {
        return [ 'type' => self::$es_type_map[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }
}
