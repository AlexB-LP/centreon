<?php
namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonRemote\Infrastructure\Export\ExportParserJson;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use DateTime;

/**
 * @group CentreonRemote
 */
class ExportManifestTest extends TestCase
{

    use TestCaseExtensionTrait;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    protected $commitment;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportManifest
     */
    protected $manifest;

    /**
     * @var string
     */
    protected $version = '18.10';

    /**
     * @var array
     */
    protected $dumpData = [];

    /**
     * Set up datasets and mocks
     */
    protected function setUp()
    {
        $parser = $this->getMockBuilder(ExportParserJson::class)
            ->setMethods([
                'parse',
                'dump',
            ])
            ->getMock()
        ;
        $parser->method('parse')
            ->will($this->returnCallback(function () {
                    $args = func_get_args();
                    $file = $args[0];

                    return [];
            }))
        ;
        $parser->method('dump')
            ->will($this->returnCallback(function () {
                    $args = func_get_args();

                    $this->dumpData[$args[1]] = $args[0];
            }))
        ;

        $this->commitment = new ExportCommitment(1, [2, 3], null, $parser);
        $this->manifest = $this
            ->getMockBuilder(ExportManifest::class)
            ->setMethods([
                'getFile',
            ])
            ->setConstructorArgs([
                $this->commitment,
                $this->version
            ])
            ->getMock()
        ;
        $this->manifest
            ->method('getFile')
            ->will($this->returnCallback(function () {
                    return __FILE__;
            }))
        ;
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::__construct
     */
    public function testConstruct()
    {
        $this->assertAttributeInstanceOf(ExportCommitment::class, 'commitment', $this->manifest);
        $this->assertAttributeEquals($this->version, 'version', $this->manifest);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::get
     */
    public function testGet()
    {
        $this->assertNull($this->manifest->get('missing-data'));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::validate
     * @expectedException \Exception
     */
    public function testValidate()
    {
        $this->manifest->validate();

        // chech $this->files
        $this->assertAttributeEquals([], 'data', $this->manifest);
    }

    /**
     * @ covers \CentreonRemote\Infrastructure\Export\ExportManifest::dump
     */
    public function testDump()
    {
        $this->manifest->dump();

        $this->assertEquals([
            $this->manifest->getFile() => [
                'version' => $this->version,
                'date' => date('l jS \of F Y h:i:s A'),
                'remote_server' => $this->commitment->getRemote(),
                'pollers' => $this->commitment->getPollers(),
                'import' => null,
            ],
            ], $this->dumpData);
    }
}
