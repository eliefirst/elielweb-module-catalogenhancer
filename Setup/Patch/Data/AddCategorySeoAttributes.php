<?php
declare(strict_types=1);

namespace ElielWeb\CatalogEnhancer\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;

class AddCategorySeoAttributes implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entity = Category::ENTITY;

        // Header black text toggle (from Clrz_Page)
        $eavSetup->addAttribute($entity, 'header_black_text', [
            'type'             => 'int',
            'label'            => 'Texte header noir',
            'input'            => 'select',
            'source'           => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default'          => '0',
            'group'            => 'General Information',
            'global'           => ScopedAttributeInterface::SCOPE_STORE,
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'sort_order'       => 60,
        ]);

        // SEO Name (from Rl_Catalog)
        $eavSetup->addAttribute($entity, 'seo_name', [
            'type'             => 'varchar',
            'label'            => '[SEO] Nom optimisé',
            'input'            => 'text',
            'group'            => 'General Information',
            'global'           => ScopedAttributeInterface::SCOPE_STORE,
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'sort_order'       => 31,
        ]);

        // SEO Blocks #1 and #2
        $sortOrder = 170;
        for ($i = 1; $i <= 2; $i++) {
            $eavSetup->addAttribute($entity, "seo_uptitle_{$i}", [
                'type'             => 'varchar',
                'label'            => "[SEO #{$i}] Surtitre",
                'input'            => 'text',
                'group'            => 'Search Engine Optimization',
                'global'           => ScopedAttributeInterface::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'sort_order'       => $sortOrder,
            ]);

            $eavSetup->addAttribute($entity, "seo_title_{$i}", [
                'type'             => 'varchar',
                'label'            => "[SEO #{$i}] Titre",
                'input'            => 'text',
                'group'            => 'Search Engine Optimization',
                'global'           => ScopedAttributeInterface::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'sort_order'       => $sortOrder + 10,
            ]);

            $eavSetup->addAttribute($entity, "seo_content_{$i}", [
                'type'             => 'text',
                'label'            => "[SEO #{$i}] Texte",
                'input'            => 'textarea',
                'group'            => 'Search Engine Optimization',
                'global'           => ScopedAttributeInterface::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'wysiwyg_enabled'  => true,
                'sort_order'       => $sortOrder + 20,
            ]);

            $sortOrder += 30;
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [AddCategoryAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
