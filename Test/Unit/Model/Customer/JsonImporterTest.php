<?php
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Vml\Import\Model\Customer\JsonImporter;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;

class JsonImporterTest extends TestCase
{
    public function testGetImportData()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getArgument')->willReturn('/var/import/sample.json');
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')->willReturn(true);
        $fileMock->method('fileGetContents')->willReturn('{"customers":[{"fname":"John","lname":"Doe","emailaddress":"john@example.com"}]}');
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMock();
        $serializerMock->method('unserialize')->willReturn([
            'customers' => [
                ['fname' => 'John', 'lname' => 'Doe', 'emailaddress' => 'john@example.com']
            ]
        ]);
        $jsonImporter = new JsonImporter($fileMock, $serializerMock);
        $importData = $jsonImporter->getImportData($inputMock);
        $this->assertIsArray($importData);
        $this->assertArrayHasKey('customers', $importData);
        $this->assertCount(1, $importData['customers']);
        $this->assertEquals('John', $importData['customers'][0]['fname']);
        $this->assertEquals('Doe', $importData['customers'][0]['lname']);
        $this->assertEquals('john@example.com', $importData['customers'][0]['emailaddress']);
    }

    public function testReadDataWithNonExistentFile()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getArgument')->willReturn('/path/to/nonexistent.json');
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')->willReturn(false);
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMock();
        $jsonImporter = new JsonImporter($fileMock, $serializerMock);
        $this->expectException(LocalizedException::class);
        $jsonImporter->readData('/path/to/nonexistent.json');
    }

    public function testReadDataWithFileSystemException()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getArgument')->willReturn('/path/to/sample.json');
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')->willReturn(true);
        $fileMock->method('fileGetContents')->willThrowException(new FileSystemException(__('Unable to read file')));
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMock();
        $jsonImporter = new JsonImporter($fileMock, $serializerMock);
        $this->expectException(LocalizedException::class);
        $jsonImporter->readData('var/import/sample.json');
    }

    public function testFormatData()
    {
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->getMock();
        $serializerMock->method('unserialize')->willReturn([
            'customers' => [
                ['fname' => 'John', 'lname' => 'Doe', 'emailaddress' => 'john@example.com']
            ]
        ]);
        $jsonImporter = new JsonImporter(null, $serializerMock);
        $formattedData = $jsonImporter->formatData('{"customers":[{"fname":"John","lname":"Doe","emailaddress":"john@example.com"}]}');
        $this->assertIsArray($formattedData);
        $this->assertArrayHasKey('customers', $formattedData);
        $this->assertCount(1, $formattedData['customers']);
        $this->assertEquals('John', $formattedData['customers'][0]['fname']);
        $this->assertEquals('Doe', $formattedData['customers'][0]['lname']);
        $this->assertEquals('john@example.com', $formattedData['customers'][0]['emailaddress']);
    }
}
