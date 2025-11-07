<?php
/**
 * registration
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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class SaveCategoryImage implements ObserverInterface
{
    protected $mediaDirectory;
    protected $file;

    public function __construct(
        Filesystem $filesystem,
        File $file
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->file = $file;
    }

    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        foreach (['menu_pictogram_image', 'image_mobile'] as $attr) {
            $value = $category->getData($attr);
            if (is_array($value) && isset($value[0]['tmp_name'])) {
                $source = $this->mediaDirectory->getAbsolutePath('tmp/catalog/category/') . basename($value[0]['tmp_name']);
                $dest = $this->mediaDirectory->getAbsolutePath('catalog/category/') . basename($value[0]['name']);
                if ($this->file->fileExists($source)) {
                    $this->file->mv($source, $dest);
                    $category->setData($attr, basename($value[0]['name']));
                }
            }
        }
    }
}
