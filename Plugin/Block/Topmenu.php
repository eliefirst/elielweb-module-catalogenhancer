<?php
declare(strict_types=1);

/**
 * ElielWeb CatalogEnhancer Topmenu Plugin
 *
 * @category  ElielWeb
 * @package   ElielWeb_CatalogEnhancer
 * @author    ElielWeb Team
 * @copyright Copyright (c) 2025 ElielWeb
 */

namespace ElielWeb\CatalogEnhancer\Plugin\Block;

use Magento\Theme\Block\Html\Topmenu as MagentoTopmenu;
use Magento\Framework\Data\Tree\Node;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class Topmenu
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly Escaper $escaper
    ) {}

    /**
     * Inject pictogram image into menu items
     */
    public function beforeGetHtml(
        MagentoTopmenu $subject,
        string $outermostClass = '',
        string $childrenWrapClass = '',
        int $limit = 0
    ): array {
        $menuTree = $subject->getMenu()->getChildren();
        foreach ($menuTree as $node) {
            $this->attachCategoryPictogram($node);
        }
        return [$outermostClass, $childrenWrapClass, $limit];
    }

    /**
     * Attach pictogram to category nodes
     */
    private function attachCategoryPictogram(Node $node): void
    {
        $categoryId = $node->getData('entity_id');
        if (!$categoryId) {
            return;
        }

        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $category = $this->categoryRepository->get((int) $categoryId, $storeId);
            $pictogram = $this->resolvePictogramFilename($category->getData('menu_pictogram_image'));

            if ($pictogram !== null) {
                $baseMediaUrl = $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $imgUrl = $baseMediaUrl . 'catalog/category/' . $pictogram;
                $escapedUrl = $this->escaper->escapeUrl($imgUrl);

                $node->setName(
                    '<img class="menu-picto" src="' . $escapedUrl . '" alt="" loading="lazy" width="20" height="20" /> '
                    . $node->getName()
                );
            }
        } catch (\Exception $e) {
            // Ignore missing categories or media
        }

        foreach ($node->getChildren() as $childNode) {
            $this->attachCategoryPictogram($childNode);
        }
    }

    /**
     * Resolve pictogram filename from attribute value (string or array from image uploader)
     */
    private function resolvePictogramFilename(mixed $value): ?string
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
