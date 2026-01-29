# ElielWeb_CatalogEnhancer

### Description
Module Magento 2.4.8 pour enrichir les catégories :
- Ajout de champs `[MENU] Image Pictogramme`, `Image mobile`, `Landing URL`.
- Plugin Topmenu pour affichage des pictogrammes dans le menu frontend.
- Traductions multi-langues (FR, EN, ES, JA, RU, ZH).

### Compatibilité
- Magento ≥ 2.4.8-p3 
- PHP ≥ 8.3 / 8.4

### Installation
```bash
bin/magento module:enable ElielWeb_CatalogEnhancer
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento c:f
