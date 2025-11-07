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

class Topmenu
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private StoreManagerInterface $storeManager,
        private UrlInterface $urlBuilder
    ) {}

    /**
     * Inject pictogram image into menu items
     */
    public function beforeGetHtml(
        MagentoTopmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
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
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
            $pictogram = $category->getData('menu_pictogram_image');
            if ($pictogram) {
                $imgUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
                    . 'catalog/category/' . $pictogram;

                $node->setName(
                    '<img class="menu-picto" src="' . $imgUrl . '" alt="" /> ' . $node->getName()
                );
            }
        } catch (\Exception $e) {
            // Ignore missing categories or media
        }

        foreach ($node->getChildren() as $childNode) {
            $this->attachCategoryPictogram($childNode);
        }
    }
}
