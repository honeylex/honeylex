<?php

namespace Honeylex\Console\Scaffold;

use Trellis\CodeGen\PluginInterface;
use Trellis\CodeGen\Schema\EntityTypeDefinition;
use Trellis\CodeGen\Schema\EntityTypeSchema;
use Trellis\Common\Options;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A plugin for the Trellis code-generator.
 * Generates an Elasticsearch type mapping for a given entity type definition.
 */
class ElasticsearchMappingPlugin implements PluginInterface
{
    const FILE_MODE = 0644;

    protected static $esTypeMap = [
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

    protected $fileSystem;

    public function __construct(Options $options)
    {
        $this->options = $options;
        $this->fileSystem = new Filesystem;
    }

    public function execute(EntityTypeSchema $typeSchema)
    {
        $typeDefinition = $typeSchema->getEntityTypeDefinition();
        $typeProperties = $this->buildTypePropertiesMapping($typeDefinition);

        $this->fileSystem->dumpFile(
            $this->options['deploy_path'],
            json_encode(
                [ 'properties' => (object)$typeProperties ],
                JSON_PRETTY_PRINT
            ),
            self::FILE_MODE
        );
    }

    protected function buildTypePropertiesMapping(EntityTypeDefinition $typeDefinition)
    {
        $typeProperties = [];
        foreach ($typeDefinition->getAttributes() as $attribute) {
            $handlerFunction = sprintf(
                'map%s',
                implode('', array_map('ucfirst', explode('-', $attribute->getShortName())))
            );
            if (is_callable(array($this, $handlerFunction))) {
                $mapping = $this->$handlerFunction($attribute);
                if (!empty($mapping)) {
                    $typeProperties[$attribute->getName()] = $mapping;
                }
            }
        }

        return $typeProperties;
    }

    protected function mapText($attribute)
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

    protected function mapEmail($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapEmailList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapUrl($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'index' => 'no' ];
    }

    protected function mapTimestamp($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapDate($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapChoice($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }

    protected function mapTextList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapTextarea($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapInteger($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapIntegerList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapFloat($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapFloatList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapBoolean($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapBooleanList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapKeyValueList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapImage($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapImageList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapEmbeddedEntityList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapEntityReferenceList($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapAsset($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'enabled' => false ];
    }

    protected function mapGeoPoint($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()] ];
    }

    protected function mapToken($attribute)
    {
        return [ 'type' => self::$esTypeMap[$attribute->getShortName()], 'index' => 'not_analyzed' ];
    }
}
