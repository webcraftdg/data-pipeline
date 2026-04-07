<?php
/**
 * WriterContext.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\contexts
 */
namespace webcraftdg\dataPipeline\contexts;

use fractalCms\importExport\io\interfaces\Writer as WriterInterface;
use fractalCms\importExport\io\exports\writers\WriteTarget;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

final class WriterContext extends ExecutionContext
{

    /**
     * $writtenHeaders
     *
     * @var array
     */
    private array $writtenPreamble = [];

    /**
     * @param ImportConfig $config
     * @param bool $dryRun
     * @param int $rowNumber
     * @param array $params
     */
    public function __construct(
        public ImportConfig $config,
        public bool $dryRun,
        public bool $hasPreamble,
        public int $rowNumber,
        public WriterInterface $writer,
        public Writer $writerContext,
        public string $sectionName,
        public string $absolutePath,
        public string $relativePath,
        public array $preamble = [],
        public int $rowOffset = 1,
        public int $colOffset = 1,
        public array $params = []
    ) {
        parent::__construct(
            config: $config,
            stopOnError: false, // inutile à l’export
            dryRun: $dryRun,
            hasPreamble:$hasPreamble,
            rowNumber: $rowNumber,
            params: $params
        );
    }


    /**
     * @param string $sheet
     * @param array $row
     * @param int $startRow
     * @param int $startCol
     * @param string|null $style
     * @return void
     */
    public function writeRow(
        array $row,
        ?string $style = null
    ): void {
        $this->writer->write(
            new WriteTarget(
                sheet: $this->sectionName,
                rowNumber: $this->rowOffset,
                colNumber: $this->colOffset,
                style: $style
            ),
            $row
        );
    }

    /**
     * @param string $sheetName
     * @param array $headers
     * @param int $rowNumber
     * @param int $colOffset
     * @param string|null $style
     * @return void
     * @throws Exception
     */
    public function writePreambleOne(array $headers, ?string $style = null): void
    {
        try {
            $key = crc32($this->sectionName . ':' . $this->rowOffset . ':' . $this->colOffset);
            if (isset($this->writtenPreamble[$key])) {
                return;
            }
            if(empty($headers) === false) {
                $this->writeRow(
                    row: $headers,
                    style: $style
                );
                $this->writtenPreamble[$key] = true;        
            }
        
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * @param string $filePath
     * @return void*
     */
    public function finalize(WriterContext $writerContext): void
    {
        $this->writer->close($writerContext);
    }
}
