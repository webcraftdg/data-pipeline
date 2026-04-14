<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use webcraftdg\dataPipeline\configLoaders\JsonFileConfigReader;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\ProcessorConfig;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\configs\TargetConfig;
use webcraftdg\dataPipeline\configs\TransformerConfig;
use webcraftdg\dataPipeline\exceptions\ProcessorResult;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\interfaces\ProcessorInterface;
use webcraftdg\dataPipeline\pipelines\PipelineExecutor;
use webcraftdg\dataPipeline\processors\ValidateEmailProcessor;
use webcraftdg\dataPipeline\registry\InputRegistry;
use webcraftdg\dataPipeline\registry\OutputRegistry;
use webcraftdg\dataPipeline\registry\ProcessorRegistry;
use webcraftdg\dataPipeline\registry\TransformerRegistry;
use webcraftdg\dataPipeline\runtimes\PipelineRuntime;
use webcraftdg\dataPipeline\runtimes\PipelineRuntimeFactory;
use webcraftdg\dataPipeline\supports\enums\DataEndpointType;
use webcraftdg\dataPipeline\supports\enums\PipelineDataFormat;
use webcraftdg\dataPipeline\transformers\BooleanColumnTransformer;
use webcraftdg\dataPipeline\transformers\ConvertColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateXlsColumnTransformer;
use webcraftdg\dataPipeline\transformers\LowerColumnTransformer;
use webcraftdg\dataPipeline\transformers\NumberColumnTransformer;
use webcraftdg\dataPipeline\transformers\ReplaceColumnTransformer;
use webcraftdg\dataPipeline\transformers\StrPadColumnTransformer;
use webcraftdg\dataPipeline\transformers\TrimColumnTransformer;
use webcraftdg\dataPipeline\transformers\UpperColumnTransformer;
use webcraftdg\dataPipeline\validators\FileConfigJsonValidator;
use webcraftdg\dataPipeline\validators\OptionsValidator;
use webcraftdg\dataPipeline\validators\PipelineConfigValidator;

class UserImportProcessor implements ProcessorInterface
{
  
    public function getName() : string
    {
        return 'user-import';
    }

    public function process(array $row, array $options = []): ProcessorResult
    {
        // ignorer si email vide
        if (empty($row['email'])) {
            return new ProcessorResult(handled: true);
        }

        $mappedRow = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'birthday' => $row['birthday']
        ];

        // on indique que la ligne est déjà traitée
        return new ProcessorResult(attributes: $mappedRow, handled:false);
    }
}


class ConfigLoaderTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testPreparation()
    {
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

    public function testFormatter()
    {
        $inputRows = [
            [
                'id' => '1',
                'name' => '  john doe  ',
                'adult' => 1,
                'email' => 'JOHN@MAIL.COM',
                'birthday' => '1980-05-10',
            ],
            [
                'id' => '2',
                'name' => '  jane doe  ',
                'adult' => 0,
                'email' => 'NOT-AN-EMAIL',
                'birthday' => '1991-12-01',
            ],
            [
                'id' => '3',
                'name' => '  bob  ',
                'adult' => 1,
                'email' => 'bob@mail.com',
                'birthday' => 'wrong-date',
            ],
             [
                'id' => '4',
                'name' => '  Pierre thomas ',
                'adult' => 0,
                'email' => 'pierre@free.fr',
                'birthday' => '2026-04-09',
            ],
        ];

        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'search' => 'NOT-AN-EMAIL',
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(5, count($config->columns));

        $transformers = [
            'boolean' => BooleanColumnTransformer::class,
            'convert' => ConvertColumnTransformer::class,
            'date' => DateColumnTransformer::class,
            'date-xls' => DateXlsColumnTransformer::class,
            'lower' => LowerColumnTransformer::class,
            'number' => NumberColumnTransformer::class,
            'replace' => ReplaceColumnTransformer::class,
            'str-pad' => StrPadColumnTransformer::class,
            'trim' => TrimColumnTransformer::class,
            'upper' => UpperColumnTransformer::class
        ];
        $registryTransfromer = new TransformerRegistry($transformers);



        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            $registryTransfromer
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);

        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);



        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $fileJson = __DIR__.'/../Support/Data/testFormatter.json';
        $content = file_get_contents($fileJson);
        $json = json_decode($content, true);
        $this->tester->assertArrayHasKey('metas', $json);
        $this->tester->assertArrayHasKey('records', $json);
        $records = ($json['records']) ?? [];
        $this->tester->assertNotEmpty($records);
        $fields = $records[0];
        $this->tester->assertArrayHasKey('record', $fields);

    }


    public function testProcessor()
    {
        $inputRows = [
            [
                'id' => '1',
                'name' => '  john doe  ',
                'adult' => 1,
                'email' => 'JOHN@MAIL.COM',
                'birthday' => '1980-05-10',
            ],
            [
                'id' => '2',
                'name' => '  jane doe  ',
                'adult' => 0,
                'email' => 'NOT-AN-EMAIL',
                'birthday' => '1991-12-01',
            ],
            [
                'id' => '3',
                'name' => '  bob  ',
                'adult' => 1,
                'email' => 'bob@mail.com',
                'birthday' => 'wrong-date',
            ],
             [
                'id' => '4',
                'name' => '  Pierre thomas ',
                'adult' => 0,
                'email' => 'pierre@free.fr',
                'birthday' => '2026-04-09',
            ],
        ];

        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email'), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
            new ProcessorConfig('validate-email')
        );
        $this->tester->assertEquals(5, count($config->columns));

       

        $processors = [
            'validate-email' => ValidateEmailProcessor::class
        ];

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(),
            new OutputRegistry(),
            new ProcessorRegistry($processors),
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);
        $this->tester->assertInstanceOf(ProcessorInterface::class, $pipelinRuntime->processor);


        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertFalse($report->success);

        $fileJson = __DIR__.'/../Support/Data/testFormatter.json';
        $content = file_get_contents($fileJson);
        $json = json_decode($content, true);
        $this->tester->assertArrayHasKey('metas', $json);
        $this->tester->assertArrayHasKey('records', $json);
        $records = ($json['records']) ?? [];
        $this->tester->assertNotEmpty($records);
        $fields = $records[0];
        $this->tester->assertArrayHasKey('record', $fields);

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
            true,
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                'rows' => $inputRows,
                'batchSize' => 2
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testPipelineToJson.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant'), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(4, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(),
            new OutputRegistry(),
            new ProcessorRegistry(),
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $fileJson = __DIR__.'/../Support/Data/testPipelineToJson.json';
        $content = file_get_contents($fileJson);
        $json = json_decode($content, true);
        $this->tester->assertArrayHasKey('metas', $json);
        $this->tester->assertArrayHasKey('records', $json);
        $records = ($json['records']) ?? [];
        $this->tester->assertNotEmpty($records);
        $fields = $records[0];
        $this->tester->assertArrayHasKey('record', $fields);

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
            true,
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::EXCEL_X, [
                    'path' => __DIR__.'/../Support/Data/test_ouput.xlsx'
                ]),
            [
                new ColumnMapping('id', 'Identifiant'), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/Y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(4, count($config->columns));

        
        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $fileJson = __DIR__.'/../Support/Data/test_ouput.xlsx';
        $content = file_get_contents($fileJson);
        $this->tester->assertNotEmpty($content);

    }

    public function testPipelineToXml()
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
            true, 
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::XML, [
                    'path' => __DIR__.'/../Support/Data/test_ouput.xml'
                ]),
            [
                new ColumnMapping('id', 'Identifiant'), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/Y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(4, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/test_ouput.xml';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);
    }


    public function testPipelineToNdJson()
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
            true, 
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::NDJSON, [
                    'path' => __DIR__.'/../Support/Data/test_ouput_nd.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant'), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/Y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(4, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/test_ouput_nd.json';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);

    }
    

    public function testPipelineToCsv()
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
            true, 
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => $inputRows
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, [
                    'path' => __DIR__.'/../Support/Data/test_ouput.csv'
                ]),
            [
                new ColumnMapping('id', 'Identifiant'), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [new TransformerConfig(name: 'lower')]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/Y'
                        ]
                    )]
                )
            ],
        );
        $this->tester->assertEquals(4, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);
        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/test_ouput.csv';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);

    }

    public function testPipelineFromXlsx()
    {
  
        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::EXCEL_X, [
                    'path' => __DIR__.'/../Support/Data/test_input.xlsx',
                    'batchSize' => 2,
                    'maxColumns' => 12
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::NDJSON, [
                    'path' => __DIR__.'/../Support/Data/testPipelineFromXlsx.json'
                ]),
            [
                new ColumnMapping('region', 'reg'), 
                new ColumnMapping('département', 'country'),
                new ColumnMapping('code postal', 'zipcode'), 
                new ColumnMapping('Nom', 'name', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('téléphone', 'phoneNumber'),
                new ColumnMapping('adresse', 'adresse'),
                new ColumnMapping('ville', 'City'), 
                new ColumnMapping('age', 'age'), 
    
                new ColumnMapping('date de naissance', 'birthday', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]
                    ),
                      new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('date d\'inscription', 'subscribe', 
                    [
                        new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]),
                        new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('salaire', 'salaire', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]),
                  new ColumnMapping('patrimoine', 'patrimoine', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]), 
            ],
        );
        $this->tester->assertEquals(12, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineFromXlsx.json';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);

    }

    public function testPipelineFromCsv()
    {
  
        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, [
                    'path' => __DIR__.'/../Support/Data/test_input.csv',
                    'batchSize' => 2
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testPipelineFromCsv.json'
                ]),
            [
                new ColumnMapping('region', 'reg'), 
                new ColumnMapping('département', 'country'),
                new ColumnMapping('code postal', 'zipcode'), 
                new ColumnMapping('Nom', 'name', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('téléphone', 'phoneNumber'),
                new ColumnMapping('adresse', 'adresse'),
                new ColumnMapping('ville', 'City', [
                    new TransformerConfig(name:'convert', options: [
                        'from' => 'UTF-8', 'to' => 'ISO-8859-1'
                    ])
                ]), 
                new ColumnMapping('age', 'age'), 
                new ColumnMapping('date de naissance', 'birthday', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]
                    ),
                      new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('date d\'inscription', 'subscribe', 
                    [
                        new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]),
                        new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('salaire', 'salaire', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]),
                  new ColumnMapping('patrimoine', 'patrimoine', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]), 
            ],
        );
        $this->tester->assertEquals(12, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineFromCsv.json';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);

    }

     public function testPipelineFromNdJson()
    {
  
        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::NDJSON, [
                    'path' => __DIR__.'/../Support/Data/test_input_nd.json',
                    'batchSize' => 2
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testPipelineFromNdJson.json'
                ]),
            [
                new ColumnMapping('region', 'reg'), 
                new ColumnMapping('département', 'country'),
                new ColumnMapping('code postal', 'zipcode'), 
                new ColumnMapping('Nom', 'name', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('téléphone', 'phoneNumber'),
                new ColumnMapping('adresse', 'adresse'),
                new ColumnMapping('ville', 'City'), 
                new ColumnMapping('age', 'age'), 
    
                new ColumnMapping('date de naissance', 'birthday', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]
                    ),
                      new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('date d\'inscription', 'subscribe', 
                    [
                        new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]),
                        new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('salaire', 'salaire', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]),
                  new ColumnMapping('patrimoine', 'patrimoine', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]), 
            ],
        );
        $this->tester->assertEquals(12, count($config->columns));

         $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(),
            new OutputRegistry(),
            new ProcessorRegistry(),
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineFromNdJson.json';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);
    }

    public function testPipelineFromJson()
    {
  
        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/test_input.json',
                    'batchSize' => 2
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::XML, [
                    'path' => __DIR__.'/../Support/Data/testPipelineFromJson.xml'
                ]),
            [
                new ColumnMapping('region', 'reg'), 
                new ColumnMapping('département', 'country'),
                new ColumnMapping('code postal', 'zipcode'), 
                new ColumnMapping('Nom', 'name', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('téléphone', 'phoneNumber'),
                new ColumnMapping('adresse', 'adresse'),
                new ColumnMapping('ville', 'City'), 
                new ColumnMapping('age', 'age'), 
    
                new ColumnMapping('date de naissance', 'birthday', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]
                    ),
                      new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('date d\'inscription', 'subscribe', 
                    [
                        new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]),
                        new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('salaire', 'salaire', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]),
                  new ColumnMapping('patrimoine', 'patrimoine', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]), 
            ],
        );
        $this->tester->assertEquals(12, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineFromJson.xml';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);
    }

    public function testPipelineFromXML()
    {
  
        $config = new PipelineConfig(
            'test',
            1,
            true,
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::XML, [
                    'path' => __DIR__.'/../Support/Data/test_input.xml',
                    'batchSize' => 2
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::XML, [
                    'path' => __DIR__.'/../Support/Data/testPipelineFromXML.xml'
                ]),
            [
                new ColumnMapping('region', 'reg'),
                new ColumnMapping('département', 'country'),
                new ColumnMapping('code postal', 'zipcode'),
                new ColumnMapping('Nom', 'name',
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('téléphone', 'phoneNumber'),
                new ColumnMapping('adresse', 'adresse'),
                new ColumnMapping('ville', 'City'), 
                new ColumnMapping('age', 'age'), 
                new ColumnMapping('date de naissance', 'birthday', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]
                    ),
                      new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('date d\'inscription', 'subscribe', 
                    [
                        new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'd/m/Y', 'to' => 'Y-m-d'
                        ]),
                        new TransformerConfig(name:'date-xls', options: [
                            'to' => 'Y-m-d'
                        ]),
                    ]
                ),
                new ColumnMapping('salaire', 'salaire', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]),
                new ColumnMapping('patrimoine', 'patrimoine', [
                    new TransformerConfig(name:'number', options: [
                        'decimals' => 3
                    ])
                ]), 
            ],
        );
        $this->tester->assertEquals(12, count($config->columns));

        $pipelinRuntime = (new PipelineRuntimeFactory(
            new InputRegistry(), 
            new OutputRegistry(), 
            new ProcessorRegistry(), 
            new TransformerRegistry()
        ))->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);

        $executor = new PipelineExecutor();
        $report = $executor->run($config, $pipelinRuntime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineFromXML.xml';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);
    }

    public function testPipelineValidation()
    {
        $config = new PipelineConfig(
            'test', 
            1, 
            true, 
            new SourceConfig(DataEndpointType::ARRAY, PipelineDataFormat::ARRAY, [
                "rows" => []
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.json'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]),
                 new ColumnMapping('salaire', 'salaire', 
                    [
                        new TransformerConfig(name: 'number', options: [
                            'decimals' => 3,
                        ]),
                    ]
                ), 
                new ColumnMapping('address', 'address', 
                    [
                        new TransformerConfig(name: 'convert', options: [
                            'from' => 'ISO-8856-1', 'to' => 'UTF-8'
                        ]),
                    ]
                ), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trim'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'search' => 'NOT-AN-EMAIL',
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );

        $processors = [
            'validate-email' => ValidateEmailProcessor::class
        ];

        $pipelineFactory = new PipelineRuntimeFactory(
            inputRegistry: new InputRegistry(), 
            outputRegistry: new OutputRegistry(), 
            processorRegistry: new ProcessorRegistry($processors), 
            transformerRegistry: new TransformerRegistry()
        );
        $pipelinRuntime = $pipelineFactory->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);



        $optionValidation = new OptionsValidator();

        $pipelineValidation = new PipelineConfigValidator(
            inputRegistry: $pipelineFactory->inputRegistry,
            outputRegistry: $pipelineFactory->outputRegistry,
            transformerRegistry: $pipelineFactory->transformerRegistry,
            processorRegistry: $pipelineFactory->processorRegistry,
            optionsValidator: $optionValidation);

        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertFalse($errorCollector->hasErrors());


        $config = new PipelineConfig(
            'test', 
            1, 
            true, 
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::NDJSON, [
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::XML, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.xml'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]), 
                   new ColumnMapping('adult', 'Adulte'), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trimop'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertTrue($errorCollector->hasErrors());
        $this->tester->assertEquals(4, count($errorCollector->all()));


         $config = new PipelineConfig(
            '', 
            -1, 
            true, 
            new SourceConfig('', PipelineDataFormat::EXCEL_X, [
            ]),
            new TargetConfig('', PipelineDataFormat::NDJSON, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.json'
                ]),
            [
                new ColumnMapping('', '', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]), 
                   new ColumnMapping('adult', 'Adulte'), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trimop'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertTrue($errorCollector->hasErrors());
        $this->tester->assertEquals(8, count($errorCollector->all()));



          $config = new PipelineConfig(
            'test', 
            1, 
            true, 
            new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
            ]),
            new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.csv'
                ]),
            [
                new ColumnMapping('id', 'Identifiant', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => STR_PAD_LEFT
                        ])
                ]), 
                   new ColumnMapping('adult', 'Adulte'), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 'OUI',
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trimop'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertTrue($errorCollector->hasErrors());
        $this->tester->assertEquals(4, count($errorCollector->all()));


         $config = new PipelineConfig(
            'test', 
            3, 
             
            true, 
            new SourceConfig('', PipelineDataFormat::XML, [
                'path' => 8
            ]),
            new TargetConfig('', PipelineDataFormat::EXCEL_X, [
                    'path' => __DIR__.'/../Support/Data/testFormatter.xlsx'
                ]),
            [
                new ColumnMapping('', '', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => 'pad'
                        ])
                ]), 
                   new ColumnMapping('adult', 'Adulte'), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 15020,
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trimop'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: 'lower')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
        );
        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertTrue($errorCollector->hasErrors());
        $this->tester->assertEquals(8, count($errorCollector->all()));


        $config = new PipelineConfig(
            'test', 
            3, 
            true, 
            new SourceConfig('', 'machin', [
                'rows' => []
            ]),
            new TargetConfig('', 'mzchin', [
                    'path' => __DIR__.'/../Support/Data/testFormatter.json'
                ]),
            [
                new ColumnMapping('', '', [
                        new TransformerConfig(name:'str-pad', options: [
                            'length' => 8,
                            'string' => '0',
                            'type' => 'pad'
                        ])
                ]), 
                   new ColumnMapping('adult', 'Adulte'), 
                 new ColumnMapping('adult', 'Adulte', 
                    [
                        new TransformerConfig(name: 'boolean', options: [
                            'true' => 15020,
                            'false' => 'NON'
                        ]),
                    ]
                ), 
                new ColumnMapping('name', 'Nom', 
                    [
                        new TransformerConfig(name: 'upper'),
                        new TransformerConfig(name: 'trimop'),
                    ]
                ), 
                new ColumnMapping('email', 'Email', 
                    [
                        new TransformerConfig(name:'replace', options: [
                            'replace' => 'GENERIC@email.fr'
                        ]),
                        new TransformerConfig(name: '')
                    ]
                ), 
                new ColumnMapping('birthday', 'Date anniversaire', 
                    [new TransformerConfig(name: 'date', options: 
                        [
                            'from' => 'Y-m-d', 'to' => 'd/m/y'
                        ]
                    )]
                )
            ],
            new ProcessorConfig('validate')
        );
        $errorCollector = $pipelineValidation->validate($config);

        $this->tester->assertTrue($errorCollector->hasErrors());
        $this->tester->assertEquals(11, count($errorCollector->all()));

        $array = $errorCollector->toCsvRows();
        $this->tester->assertEquals(11, count($array));

    }
    
    public function testPipelineExemple()
    {
       $config = new PipelineConfig(
        name: 'import-users',
        version: 1,
        stopOnError: true,

        source: new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, [
            'path' => __DIR__ . '/../Support/Data/users_input.csv',
            'delimiter' => ';'
        ]),

        target: new TargetConfig(DataEndpointType::FILE, PipelineDataFormat::JSON, [
             'path' => __DIR__ . '/../Support/Data/testPipelineExemple.json',
        ]),

        columns: [
            new ColumnMapping('id', 'id'),

            new ColumnMapping('name', 'name', [
                new TransformerConfig('upper')
            ]),

            new ColumnMapping('email', 'email'),

            new ColumnMapping('birthday', 'birthday', [
                new TransformerConfig('date', [
                    'from' => 'Y-m-d',
                    'to' => 'Y-m-d'
                ])
            ])
        ],
        processor: new ProcessorConfig('user-import')
    );

        $processors = [
            'user-import' => UserImportProcessor::class
        ];

        $pipelineFactory = new PipelineRuntimeFactory(
            inputRegistry: new InputRegistry(), 
            outputRegistry: new OutputRegistry(), 
            processorRegistry: new ProcessorRegistry($processors), 
            transformerRegistry: new TransformerRegistry()
        );
        $optionValidation = new OptionsValidator();

        $pipelineValidation = new PipelineConfigValidator(
            inputRegistry: $pipelineFactory->inputRegistry,
            outputRegistry: $pipelineFactory->outputRegistry,
            transformerRegistry: $pipelineFactory->transformerRegistry,
            processorRegistry: $pipelineFactory->processorRegistry,
            optionsValidator: $optionValidation);

        $errorCollector = $pipelineValidation->validate($config);
        $this->tester->assertFalse($errorCollector->hasErrors());

        $pipelinRuntime = $pipelineFactory->create($config);

        $this->tester->assertInstanceOf(PipelineRuntime::class, $pipelinRuntime);
        $this->tester->assertInstanceOf(InputInterface::class, $pipelinRuntime->input);
        $this->tester->assertInstanceOf(OutputInterface::class, $pipelinRuntime->output);
    
        $runtime = $pipelineFactory->create($config);
        $executor = new PipelineExecutor();
        $report = $executor->run($config, $runtime);
        $this->tester->assertTrue($report->success);

        $file = __DIR__.'/../Support/Data/testPipelineExemple.json';
        $content = file_get_contents($file);
        $this->tester->assertNotEmpty($content);


    }
}
