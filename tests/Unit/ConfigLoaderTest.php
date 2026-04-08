<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use webcraftdg\dataPipeline\configLoaders\JsonFileConfigReader;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\configs\TargetConfig;
use webcraftdg\dataPipeline\configs\TransformerConfig;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\mappers\ColumnMapper;
use webcraftdg\dataPipeline\pipelines\PipelineExecutor;
use webcraftdg\dataPipeline\registry\InputRegistry;
use webcraftdg\dataPipeline\registry\OutputRegistry;
use webcraftdg\dataPipeline\registry\TransformerRegistry;
use webcraftdg\dataPipeline\supports\enums\DataEndpointType;
use webcraftdg\dataPipeline\supports\enums\PipelineDataFormat;
use webcraftdg\dataPipeline\transformers\BooleanColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateXlsColumnTransformer;
use webcraftdg\dataPipeline\transformers\LowerColumnTransformer;
use webcraftdg\dataPipeline\transformers\NumberColumnTransformer;
use webcraftdg\dataPipeline\transformers\ReplaceColumnTransformer;
use webcraftdg\dataPipeline\transformers\StrPadColumnTransformer;
use webcraftdg\dataPipeline\transformers\TrimColumnTransformer;
use webcraftdg\dataPipeline\transformers\UpperColumnTransformer;
use webcraftdg\dataPipeline\validators\FileConfigJsonValidator;

class ConfigLoaderTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testPreparation()
    {
        $config = new PipelineConfig(
            'test', 
            1, 
            DataEndpointType::IMPORT, 
            true, 
            PipelineDataFormat::CSV,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, ['path' => 'input.csv']),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, ['path' => 'output.csv']),
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

        $output = (new OutputRegistry())->create($config);
        $input = (new InputRegistry())->create($config);
        $this->tester->assertInstanceOf(OutputInterface::class, $output);
        $this->tester->assertInstanceOf(InputInterface::class, $input);
    }

    public function testPipelineToJson()
    {
        $inputRows = [
            [
                'id' => '1',
                'name' => '  john doe  ',
                'email' => 'JOHN@MAIL.COM',
                'birthday' => '1980-05-10',
            ],
            [
                'id' => '2',
                'name' => '  jane doe  ',
                'email' => 'NOT-AN-EMAIL',
                'birthday' => '1991-12-01',
            ],
            [
                'id' => 'ABC',
                'name' => '  bob  ',
                'email' => 'bob@mail.com',
                'birthday' => 'wrong-date',
            ],
        ];

        $config = new PipelineConfig(
            'test', 
            1, 
            DataEndpointType::EXPORT, 
            true, 
            PipelineDataFormat::JSON,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/test_ouput.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', 'string'), 
                new ColumnMapping('name', 'Nom', 'string', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 'string', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 'date', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertTrue($config->isExport());
        $this->tester->assertFalse($config->isImport());
        $this->tester->assertEquals(4, count($config->columns));

        $input = (new InputRegistry())->create($config);
        $output = (new OutputRegistry())->create($config);
        $this->tester->assertInstanceOf(InputInterface::class, $input);
        $this->tester->assertInstanceOf(OutputInterface::class, $output);

        $transformers = [
            new BooleanColumnTransformer(),
            new DateColumnTransformer(),
            new DateXlsColumnTransformer(),
            new LowerColumnTransformer(),
            new NumberColumnTransformer(),
            new ReplaceColumnTransformer(),
            new StrPadColumnTransformer(),
            new TrimColumnTransformer(),
            new UpperColumnTransformer()
        ];
        $registryTransfromer = new TransformerRegistry($transformers);
        $columnMapper = new ColumnMapper($registryTransfromer);

        $executor = new PipelineExecutor($columnMapper);
        $report = $executor->run($config, $input, $output);
        $this->tester->assertTrue($report->success);

        $fileJson = __DIR__.'/../Support/Data/test_ouput.json';
        $content = file_get_contents($fileJson);
        $json = json_decode($content, true);
        $this->tester->assertArrayHasKey('metas', $json);
        $this->tester->assertArrayHasKey('records', $json);
        $records = ($json['records']) ?? [];
        $this->tester->assertNotEmpty($records);
        $fields = $records[0];
        $this->tester->assertArrayHasKey('fields', $fields);

    }

    public function testPipelineToXlsx()
    {
        $inputRows = [
            [
                'id' => '1',
                'name' => '  john doe  ',
                'email' => 'JOHN@MAIL.COM',
                'birthday' => '1980-05-10',
            ],
            [
                'id' => '2',
                'name' => '  jane doe  ',
                'email' => 'NOT-AN-EMAIL',
                'birthday' => '1991-12-01',
            ],
            [
                'id' => 'ABC',
                'name' => '  bob  ',
                'email' => 'bob@mail.com',
                'birthday' => 'wrong-date',
            ],
        ];

        $config = new PipelineConfig(
            'test', 
            1, 
            DataEndpointType::EXPORT, 
            true, 
            PipelineDataFormat::JSON,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::EXCEL_X, [
                    'path' => __DIR__.'/../Support/Data/test_ouput.xlsx'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', 'string'), 
                new ColumnMapping('name', 'Nom', 'string', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 'string', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 'date', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/Y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertTrue($config->isExport());
        $this->tester->assertFalse($config->isImport());
        $this->tester->assertEquals(4, count($config->columns));

        $input = (new InputRegistry())->create($config);
        $output = (new OutputRegistry())->create($config);
        $this->tester->assertInstanceOf(InputInterface::class, $input);
        $this->tester->assertInstanceOf(OutputInterface::class, $output);

        $transformers = [
            new BooleanColumnTransformer(),
            new DateColumnTransformer(),
            new DateXlsColumnTransformer(),
            new LowerColumnTransformer(),
            new NumberColumnTransformer(),
            new ReplaceColumnTransformer(),
            new StrPadColumnTransformer(),
            new TrimColumnTransformer(),
            new UpperColumnTransformer()
        ];
        $registryTransfromer = new TransformerRegistry($transformers);
        $columnMapper = new ColumnMapper($registryTransfromer);

        $executor = new PipelineExecutor($columnMapper);
        $report = $executor->run($config, $input, $output);
        $this->tester->assertTrue($report->success);

        $fileJson = __DIR__.'/../Support/Data/test_ouput.xlsx';
        $content = file_get_contents($fileJson);
        $this->tester->assertNotEmpty($content);

    }
    

}
