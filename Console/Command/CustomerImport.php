<?php
declare(strict_types=1);

namespace Vml\Import\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\InputMismatchException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vml\Import\Api\Data\ImportInterface;
use Vml\Import\Model\Customer\CsvImporter;
use Vml\Import\Model\Customer\JsonImporter;

class CustomerImport extends Command
{
    protected $csvImporter;
    protected $jsonImporter;
    protected $storeManager;
    private $customerInterfaceFactory;
    private $customerRepository;

    /**
     * CustomerImport constructor.
     * @param CsvImporter $csvImporter
     * @param JsonImporter $jsonImporter
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CsvImporter                 $csvImporter,
        JsonImporter                $jsonImporter,
        CustomerInterfaceFactory    $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface       $storeManager
    )
    {
        parent::__construct();
        $this->csvImporter = $csvImporter;
        $this->jsonImporter = $jsonImporter;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $profileType = $input->getArgument(ImportInterface::PROFILE_NAME);
        $filePath = $input->getArgument(ImportInterface::FILE_PATH);
        $output->writeln(sprintf("Your profile type chosen is %s", $profileType));
        $output->writeln(sprintf("Your file path is %s", $filePath));

        if ($profileType === 'sample-csv') {
            $importData = $this->csvImporter->getImportData($input);
        } elseif ($profileType === 'sample-json') {
            $importData = $this->jsonImporter->getImportData($input);
        } else {
            $output->writeln("Invalid profile type specified");
            return Cli::RETURN_FAILURE;
        }

        if (!isset($importData)) {
            $output->writeln("No import data found");
            return Cli::RETURN_FAILURE;
        }

        $this->saveCustomers($importData);
        $output->writeln(sprintf("All customers are imported (%d)", count($importData)));
        return Cli::RETURN_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName("customer:import")
            ->setDescription("Customer Import")
            ->setDefinition([
                new InputArgument(ImportInterface::PROFILE_NAME, InputArgument::REQUIRED, "Profile name example: sample-csv"),
                new InputArgument(ImportInterface::FILE_PATH, InputArgument::REQUIRED, "File Path example: sample.csv")
            ]);
        parent::configure();
    }

    /**
     * Save customers to the database.
     *
     * @param array $customers
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveCustomers(array $customers): void
    {
        $storeId = null;
        $store = $this->storeManager->getStore();
        if ($store !== null) {
            $storeId = $store->getId();
        }

        $websiteId = null;
        if ($storeId !== null) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        foreach ($customers as $data) {
            try {
                $customer = $this->customerInterfaceFactory->create();
                if ($customer === null) {
                    throw new \Exception('Failed to create customer object');
                }
                $customer->setFirstname($data['fname'] ?? '');
                $customer->setLastname($data['fname'] ?? '');
                $customer->setEmail($data['emailaddress'] ?? '');

                if ($websiteId !== null) {
                    $customer->setWebsiteId($websiteId);
                }
                $this->customerRepository->save($customer);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}

