<?php

namespace Vml\Import\Api\Data;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Interface for importing data.
 */
interface ImportInterface
{
    public const PROFILE_NAME = "profile";
    public const FILE_PATH = "filepath";

    /**
     * Get import data from input.
     *
     * @param InputInterface $input
     * @return array
     */
    public function getImportData(InputInterface $input): array;

    /**
     * Read and process imported data.
     *
     * @param string $data
     * @return array
     */
    public function readData(string $data): array;

    /**
     * Format imported data.
     *
     * @param mixed $data
     * @return array
     */
    public function formatData($data): array;
}
