<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use webcraftdg\dataPipeline\configLoaders\JsonFileConfigReader;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\configs\TargetConfig;
use webcraftdg\dataPipeline\configs\TransformerConfig;
use webcraftdg\dataPipeline\supports\enums\DataEndpointType;
use webcraftdg\dataPipeline\supports\enums\PipelineFileFormat;
use webcraftdg\dataPipeline\validators\FileConfigJsonValidator;

class ConfigLoaderTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $config = new PipelineConfig(
            'test', 
            1, 
            DataEndpointType::IMPORT, 
            true, 
            PipelineFileFormat::CSV,
            new SourceConfig(DataEndpointType::FILE, ['path' => '', 'filename' => 'input.csv']),
            new TargetConfig(DataEndpointType::FILE, ['path' => '', 'filename' => 'output.csv']),
            [
                new ColumnMapping('id', 'Identifiant', 'string'), 
                new ColumnMapping('name', 'Nom', 'string', 
                    [new TransformerConfig(name: 'upper')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 'date', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d H:i:s', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );

        $this->tester->assertTrue($config->isImport());
        $this->tester->assertFalse($config->isExport());

        $path = __DIR__.'/../Support/Data/import_agent_v2_test.json';
        $validator = new FileConfigJsonValidator($path);
        $errorColector = $validator->validate();
        $this->tester->assertFalse( $errorColector->hasErrors());
        $config = JsonFileConfigReader::load($path);
        $this->tester->assertNotNull($config);
        $this->tester->assertEquals(19, count($config->columns));
    }
}
