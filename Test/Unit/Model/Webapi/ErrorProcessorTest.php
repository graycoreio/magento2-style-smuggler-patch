<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Model\Webapi;

use Graycore\StyleSmugglerPatch\Model\Webapi\ErrorProcessor;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\ErrorProcessor as WebapiErrorProcessor;
use PHPUnit\Framework\TestCase;

class ErrorProcessorTest extends TestCase
{
    /**
     * @var ErrorProcessor
     */
    private $errorProcessor;

    /**
     * @var WriteInterface
     */
    private $directoryWrite;

    /**
     * The parent constructor registers a shutdown function and resolves the object manager, so the
     * instance is assembled by hand from the two dependencies the report writer actually uses.
     */
    protected function setUp(): void
    {
        $this->errorProcessor = (new \ReflectionClass(ErrorProcessor::class))->newInstanceWithoutConstructor();
        $this->directoryWrite = $this->createStub(WriteInterface::class);

        $serializer = $this->createStub(Json::class);
        $serializer->method('serialize')->willReturnCallback(
            function ($data) {
                return json_encode($data);
            }
        );

        $this->setParentProperty('directoryWrite', $this->directoryWrite);
        $this->setParentProperty('serializer', $serializer);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function setParentProperty($name, $value)
    {
        $property = new \ReflectionProperty(WebapiErrorProcessor::class, $name);
        $property->setValue($this->errorProcessor, $value);
    }

    /**
     * @param string $reportData
     * @return string
     */
    private function saveFatalErrorReport($reportData)
    {
        $method = new \ReflectionMethod(ErrorProcessor::class, '_saveFatalErrorReport');
        return $method->invoke($this->errorProcessor, $reportData);
    }

    public function testItNeutralizesPhpOpenTagsInTheReport()
    {
        $written = null;
        $this->directoryWrite->method('writeFile')->willReturnCallback(
            function ($path, $contents) use (&$written) {
                $written = $contents;
                return strlen($contents);
            }
        );

        $this->saveFatalErrorReport("Fatal Error: '<?php system(\$_GET[0]); ?>' in '/x.php' on line 1");

        $this->assertStringNotContainsString('<?', $written);
        $this->assertStringContainsString('< ?php system', $written);
    }

    public function testItLeavesAnOrdinaryReportIntact()
    {
        $written = null;
        $this->directoryWrite->method('writeFile')->willReturnCallback(
            function ($path, $contents) use (&$written) {
                $written = $contents;
                return strlen($contents);
            }
        );

        $this->saveFatalErrorReport('Fatal Error: allowed memory size exhausted');

        $this->assertSame(json_encode('Fatal Error: allowed memory size exhausted'), $written);
    }

    public function testItStillWritesToTheApiReportDirectory()
    {
        $directoryWrite = $this->createMock(WriteInterface::class);
        $directoryWrite->expects($this->once())->method('create')->with('report/api');
        $directoryWrite->expects($this->once())->method('writeFile');
        $this->setParentProperty('directoryWrite', $directoryWrite);

        $this->assertNotEmpty($this->saveFatalErrorReport('Fatal Error: something'));
    }
}
