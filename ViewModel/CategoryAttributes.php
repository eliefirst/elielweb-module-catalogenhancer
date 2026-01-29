<?php
declare(strict_types=1);

/**
 * CategoryAttributes ViewModel
 *
 * Provides category custom attributes (pictogram, mobile image, landing URL)
 * to frontend templates. Designed for Hyva theme compatibility.
 *
 * @category  ElielWeb
 * @package   ElielWeb_CatalogEnhancer
 * @author    ElielWeb Team
 * @copyright Copyright (c) 2025 ElielWeb
 */

namespace ElielWeb\CatalogEnhancer\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CategoryAttributes implements ArgumentInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly CollectionFactory $categoryCollectionFactory
    ) {}

    /**
     * Get pictogram image URL for a category
     */
    public function getPictogramUrl(int $categoryId): ?string
    {
        return $this->getCategoryMediaUrl($categoryId, 'menu_pictogram_image');
    }

    /**
     * Get mobile image URL for a category
     */
    public function getMobileImageUrl(int $categoryId): ?string
    {
        return $this->getCategoryMediaUrl($categoryId, 'image_mobile');
    }

    /**
     * Get landing URL for a category
     */
    public function getLandingUrl(int $categoryId): ?string
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $url = $category->getData('landing_url');
            return is_string($url) && $url !== '' ? $url : null;
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    /**
     * Get all menu pictograms as [categoryId => pictogramUrl]
     *
     * @return array<int, string>
     */
    public function getMenuPictograms(): array
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('menu_pictogram_image')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1)
            ->addAttributeToFilter('menu_pictogram_image', ['notnull' => true]);

        $baseUrl = $this->getCategoryMediaBaseUrl();
        $pictograms = [];

        foreach ($collection as $category) {
            $filename = $this->resolveFilename($category->getData('menu_pictogram_image'));
            if ($filename !== null) {
                $pictograms[(int) $category->getId()] = $baseUrl . $filename;
            }
        }

        return $pictograms;
    }

    /**
     * Get all pictograms as associative array [categoryId => url]
     *
     * @param int[] $categoryIds
     * @return array<int, string>
     */
    public function getPictogramMap(array $categoryIds): array
    {
        $map = [];
        foreach ($categoryIds as $id) {
            $url = $this->getPictogramUrl((int) $id);
            if ($url !== null) {
                $map[(int) $id] = $url;
            }
        }
        return $map;
    }

    /**
     * Get media base URL for catalog/category
     */
    public function getCategoryMediaBaseUrl(): string
    {
        try {
            return $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        } catch (NoSuchEntityException) {
            return '';
        }
    }

    /**
     * Resolve media URL for a category image attribute
     */
    private function getCategoryMediaUrl(int $categoryId, string $attribute): ?string
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $value = $category->getData($attribute);
            $filename = $this->resolveFilename($value);

            if ($filename === null) {
                return null;
            }

            return $this->getCategoryMediaBaseUrl() . $filename;
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    /**
     * Resolve filename from attribute value (string or array from image uploader)
     */
    private function resolveFilename(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value) && isset($value[0]['name']) && $value[0]['name'] !== '') {
            return (string) $value[0]['name'];
        }

        return null;
    }
}
