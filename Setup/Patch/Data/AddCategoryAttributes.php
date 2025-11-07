<?php
declare(strict_types=1);

/**
 * AddCategoryAttributes
 *
 * @category  ElielWeb
 * @package   ElielWeb_CatalogEnhancer
 * @author    ElielWeb Team
 * @copyright Copyright (c) 2025 ElielWeb
 */

namespace ElielWeb\CatalogEnhancer\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;

class AddCategoryAttributes implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'menu_pictogram_image' => [
                'label'    => '[MENU] Image Pictogramme',
                'input'    => 'image',
                'type'     => 'varchar',
                'backend'  => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'group'    => 'General Information',
                'visible'  => true,
                'required' => false,
                'global'  => ScopedAttributeInterface::SCOPE_STORE,
            ],
            'image_mobile' => [
                'label'    => 'Category Image (Mobile)',
                'input'    => 'image',
                'type'     => 'varchar',
                'backend'  => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'group'    => 'General Information',
                'visible'  => true,
                'required' => false,
                'global'  => ScopedAttributeInterface::SCOPE_STORE,
            ],
            'landing_url' => [
                'label'    => '[LANDING] URL alternative',
                'input'    => 'text',
                'type'     => 'varchar',
                'group'    => 'Search Engine Optimization',
                'visible'  => true,
                'required' => false,
            ]
        ];

        foreach ($attributes as $code => $attr) {
            $eavSetup->addAttribute(Category::ENTITY, $code, array_merge([
                'global'     => ScopedAttributeInterface::SCOPE_STORE,
                'sort_order' => 200,
            ], $attr));
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array { return []; }
    public function getAliases(): array { return []; }
}
