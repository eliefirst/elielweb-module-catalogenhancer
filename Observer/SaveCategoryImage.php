<?php
declare(strict_types=1);

/**
 * SaveCategoryImage Observer
 *
 * @category  ElielWeb
 * @package   ElielWeb_CatalogEnhancer
 * @author    ElielWeb Team
 * @copyright Copyright (c) 2025 ElielWeb
 */

namespace ElielWeb\CatalogEnhancer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class SaveCategoryImage implements ObserverInterface
{
    private WriteInterface $mediaDirectory;

    public function __construct(
        Filesystem $filesystem,
        private readonly File $file
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function execute(Observer $observer): void
    {
        $category = $observer->getEvent()->getCategory();

        foreach (['menu_pictogram_image', 'image_mobile'] as $attr) {
            $value = $category->getData($attr);
            if (is_array($value) && isset($value[0]['tmp_name'])) {
                $source = $this->mediaDirectory->getAbsolutePath('tmp/catalog/category/')
                    . basename((string) $value[0]['tmp_name']);
                $dest = $this->mediaDirectory->getAbsolutePath('catalog/category/')
                    . basename((string) $value[0]['name']);
                if ($this->file->fileExists($source)) {
                    $this->file->mv($source, $dest);
                    $category->setData($attr, basename((string) $value[0]['name']));
                }
            }
        }
    }
}
