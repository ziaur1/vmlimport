<?php
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Vml\Import\Console\Command\CustomerImport;
use Vml\Import\Model\Customer\CsvImporter;
use Vml\Import\Model\Customer\JsonImporter;

class CustomerImportTest extends TestCase
{
    public function testExecuteWithCsvProfile()
    {
        //  CSV import
        $csvImporterMock = $this->createMock(CsvImporter::class);
        $csvImporterMock->expects($this->once())
            ->method('getImportData')
            ->willReturn([
                ['fname' => 'John', 'lname' => 'Doe', 'emailaddress' => 'john@example.com'],
            ]);

        // JSON import
        $jsonImporterMock = $this->createMock(JsonImporter::class);
        $jsonImporterMock->expects($this->once())
            ->method('getImportData')
            ->willReturn([
                ['fname' => 'Jane', 'lname' => 'Doe', 'emailaddress' => 'jane@example.com'],
            ]);

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();

        $customerFactoryMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($customerMock);

        $customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $command = new CustomerImport(
            $csvImporterMock,
            $jsonImporterMock,
            $customerFactoryMock,
            $customerRepositoryMock,
            $storeManagerMock
        );

        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);

        //  CSV import
        $commandTester->execute([
            'command' => $command->getName(),
            'profile' => 'sample-csv',
            'filepath' => 'var/import/sample.csv',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All customers are imported (1)', $output);

        // JSON import
        $commandTester->execute([
            'command' => $command->getName(),
            'profile' => 'sample-json',
            'filepath' => 'var/import/sample.json',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All customers are imported (1)', $output);
    }
}
