<?php
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Input\InputInterface;
use Vml\Import\Model\Customer\CsvImporter;

class CsvImporterTest extends TestCase
{
    public function testGetImportData()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getArgument')->willReturn('/path/to/sample.csv');
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')->willReturn(true);
        $csvMock = $this->createMock(Csv::class);
        $csvMock->method('setDelimiter')->willReturnSelf();
        $csvMock->method('getData')->willReturn([
            ['fname', 'lname', 'emailaddress'],
            ['John', 'Doe', 'john@example.com'],
        ]);

        $csvImporter = new CsvImporter($fileMock, $csvMock);
        $importData = $csvImporter->getImportData($inputMock);
        $this->assertIsArray($importData);
        $this->assertCount(1, $importData);
        $this->assertArrayHasKey('fname', $importData[0]);
        $this->assertArrayHasKey('lname', $importData[0]);
        $this->assertArrayHasKey('emailaddress', $importData[0]);
        $this->assertEquals('John', $importData[0]['fname']);
        $this->assertEquals('Doe', $importData[0]['lname']);
        $this->assertEquals('john@example.com', $importData[0]['emailaddress']);

    }

    public function testReadDataWithNonExistentFile()
    {
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')
            ->willReturn(false);
        $csvMock = $this->getMockBuilder(Csv::class)
            ->setConstructorArgs([$fileMock])
            ->getMock();
        $csvImporter = new CsvImporter($fileMock, $csvMock);
        $this->expectException(LocalizedException::class);
        $csvImporter->readData('non_existent_file.csv');
    }

    public function testReadDataWithFileSystemException()
    {
        $fileMock = $this->createMock(File::class);
        $fileMock->method('isExists')
            ->willThrowException(new FileSystemException(new \Magento\Framework\Phrase('File system exception')));
        $csvMock = $this->getMockBuilder(Csv::class)
            ->setConstructorArgs([$fileMock])
            ->getMock();
        $csvImporter = new CsvImporter($fileMock, $csvMock);
        $this->expectException(LocalizedException::class);
        $csvImporter->readData('var/import/sample.csv');
    }

    public function testFormatData()
    {
        $fileMock = $this->createMock(File::class);
        $csvMock = $this->createMock(Csv::class);
        $csvImporter = new CsvImporter($fileMock, $csvMock);
        $data = [
            ['fname', 'lname', 'emailaddress'],
            ['Jane', 'Doe', 'jane@example.com'],
        ];
        $formattedData = $csvImporter->formatData($data);
        $this->assertIsArray($formattedData);
        $this->assertCount(1, $formattedData);
        $this->assertArrayHasKey('fname', $formattedData[0]);
        $this->assertArrayHasKey('lname', $formattedData[0]);
        $this->assertArrayHasKey('emailaddress', $formattedData[0]);
        $this->assertEquals('Jane', $formattedData[0]['fname']);
        $this->assertEquals('Doe', $formattedData[0]['lname']);
        $this->assertEquals('jane@example.com', $formattedData[0]['emailaddress']);
    }
}
