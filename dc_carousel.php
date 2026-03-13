<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Dc_Carousel extends Module
{
    public function __construct()
    {
        $this->name = 'dc_carousel';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Design Cart';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Design Cart Carousel');
        $this->description = $this->l('Responsive image carousel with intro text and lightbox.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayHeader')
            && $this->installDatabase()
            && $this->installConfiguration();
    }

    public function uninstall()
    {
        return $this->uninstallDatabase()
            && $this->uninstallConfiguration()
            && parent::uninstall();
    }

    protected function installDatabase()
    {
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dc_carousel_slide` (
            `id_dc_carousel_slide` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `image` VARCHAR(255) NOT NULL,
            `link` VARCHAR(255) NULL,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id_dc_carousel_slide`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dc_carousel_slide_lang` (
            `id_dc_carousel_slide` INT UNSIGNED NOT NULL,
            `id_lang` INT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NULL,
            `description` TEXT NULL,
            PRIMARY KEY (`id_dc_carousel_slide`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallDatabase()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dc_carousel_slide_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dc_carousel_slide`';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    protected function installConfiguration()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $idLang = (int) $lang['id_lang'];
            Configuration::updateValue('DC_CAROUSEL_INTRO_TITLE_' . $idLang, '');
            Configuration::updateValue('DC_CAROUSEL_INTRO_DESC_' . $idLang, '');
            // Default link label from translation file
            Configuration::updateValue(
                'DC_CAROUSEL_LINK_LABEL_' . $idLang,
                $this->l('Go to product', $idLang)
            );
        }

        Configuration::updateValue('DC_CAROUSEL_INTRO_TITLE_FONT_SIZE', '32');
        Configuration::updateValue('DC_CAROUSEL_INTRO_TITLE_COLOR', '#000000');
        Configuration::updateValue('DC_CAROUSEL_INTRO_TITLE_WEIGHT', '700');
        Configuration::updateValue('DC_CAROUSEL_INTRO_TITLE_UPPERCASE', 0);

        Configuration::updateValue('DC_CAROUSEL_INTRO_DESC_FONT_SIZE', '16');
        Configuration::updateValue('DC_CAROUSEL_INTRO_DESC_COLOR', '#555555');
        Configuration::updateValue('DC_CAROUSEL_INTRO_DESC_WEIGHT', '400');
        Configuration::updateValue('DC_CAROUSEL_INTRO_DESC_UPPERCASE', 0);

        Configuration::updateValue('DC_CAROUSEL_ITEMS_DESKTOP', 4);
        Configuration::updateValue('DC_CAROUSEL_ITEMS_TABLET', 2);
        Configuration::updateValue('DC_CAROUSEL_ITEMS_MOBILE', 1);
        Configuration::updateValue('DC_CAROUSEL_SPEED', 4000);
        Configuration::updateValue('DC_CAROUSEL_AUTOPLAY', 1);

        Configuration::updateValue('DC_CAROUSEL_BG_COLOR', '#ffffff');
        Configuration::updateValue('DC_CAROUSEL_NAV_BG', '#ffffff');
        Configuration::updateValue('DC_CAROUSEL_NAV_COLOR', '#000000');
        Configuration::updateValue('DC_CAROUSEL_NAV_BG_HOVER', '#000000');
        Configuration::updateValue('DC_CAROUSEL_NAV_COLOR_HOVER', '#ffffff');

        Configuration::updateValue('DC_CAROUSEL_IMG_RATIO', '3:1');
        Configuration::updateValue('DC_CAROUSEL_IMG_BORDER_COLOR', '#e0e0e0');
        Configuration::updateValue('DC_CAROUSEL_IMG_BORDER_WIDTH', 2);

        // Opcjonalne ładowanie Owl Carousel (CSS + JS) z CDN – domyślnie WŁĄCZONE
        Configuration::updateValue('DC_CAROUSEL_LOAD_OWL', 1);

        return true;
    }

    protected function uninstallConfiguration()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            Configuration::deleteByName('DC_CAROUSEL_INTRO_TITLE_' . (int) $lang['id_lang']);
            Configuration::deleteByName('DC_CAROUSEL_INTRO_DESC_' . (int) $lang['id_lang']);
            Configuration::deleteByName('DC_CAROUSEL_LINK_LABEL_' . (int) $lang['id_lang']);
        }

        $keys = [
            'DC_CAROUSEL_INTRO_TITLE_FONT_SIZE',
            'DC_CAROUSEL_INTRO_TITLE_COLOR',
            'DC_CAROUSEL_INTRO_TITLE_WEIGHT',
            'DC_CAROUSEL_INTRO_TITLE_UPPERCASE',
            'DC_CAROUSEL_INTRO_DESC_FONT_SIZE',
            'DC_CAROUSEL_INTRO_DESC_COLOR',
            'DC_CAROUSEL_INTRO_DESC_WEIGHT',
            'DC_CAROUSEL_INTRO_DESC_UPPERCASE',
            'DC_CAROUSEL_ITEMS_DESKTOP',
            'DC_CAROUSEL_ITEMS_TABLET',
            'DC_CAROUSEL_ITEMS_MOBILE',
            'DC_CAROUSEL_SPEED',
            'DC_CAROUSEL_AUTOPLAY',
            'DC_CAROUSEL_BG_COLOR',
            'DC_CAROUSEL_NAV_BG',
            'DC_CAROUSEL_NAV_COLOR',
            'DC_CAROUSEL_NAV_BG_HOVER',
            'DC_CAROUSEL_NAV_COLOR_HOVER',
            'DC_CAROUSEL_IMG_RATIO',
            'DC_CAROUSEL_IMG_BORDER_COLOR',
            'DC_CAROUSEL_IMG_BORDER_WIDTH',
            'DC_CAROUSEL_LOAD_OWL',
        ];

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::getValue('filemanager')) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            include _PS_MODULE_DIR_ . $this->name . '/filemanager/api.php';
            exit;
        }

        if (Tools::getValue('ajax') && Tools::getValue('action') === 'getSlide' && ($id = (int) Tools::getValue('id_dc_carousel_slide'))) {
            $slide = $this->getSlideById($id);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode($slide ? $slide : ['error' => 1]);
            exit;
        }

        if (Tools::isSubmit('submitDcCarouselIntro')) {
            $this->saveIntroSettings();
        }

        if (Tools::isSubmit('submitDcCarouselAppearance')) {
            $this->saveAppearanceSettings();
        }

        if (Tools::isSubmit('submitDcCarouselTranslations')) {
            $this->saveTranslationSettings();
        }

        $baseLink = $this->context->link->getBaseLink();
        $configureUrl = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'module_name' => $this->name,
        ]);

        if (Tools::isSubmit('submitDcCarouselImage')) {
            if ($this->saveImage()) {
                Tools::redirectAdmin($configureUrl . (strpos($configureUrl, '?') !== false ? '&' : '?') . 'tab=images&conf=4');
                return;
            } else {
                $output .= $this->displayError($this->l('Could not save image.'));
            }
        }

        if (Tools::isSubmit('deleteDcCarouselImage') && $id = (int) Tools::getValue('id_dc_carousel_slide')) {
            if ($this->deleteImage($id)) {
                Tools::redirectAdmin($configureUrl . (strpos($configureUrl, '?') !== false ? '&' : '?') . 'tab=images&conf=1');
                return;
            } else {
                $output .= $this->displayError($this->l('Could not remove image.'));
            }
        }

        if (Tools::isSubmit('updatePositionsDcCarousel') && is_array(Tools::getValue('positions'))) {
            $this->updatePositions(Tools::getValue('positions'));
            $output .= $this->displayConfirmation($this->l('Order updated.'));
        }

        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        $this->context->controller->addCSS($this->_path . 'filemanager/css/dc-filemanager.css');
        $this->context->controller->addJS($this->_path . 'filemanager/js/dc-filemanager.js');
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');

        $dcFilemanagerApi = $configureUrl . (strpos($configureUrl, '?') !== false ? '&' : '?') . 'filemanager=1';
        $dcFilemanagerBase = rtrim($baseLink, '/') . '/img/dc_filemenager/';

        $this->context->smarty->assign([
            'module' => $this,
            'module_dir' => $this->_path,
            'languages' => Language::getLanguages(false),
            'default_lang' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'intro' => $this->getIntroSettings(),
            'appearance' => $this->getAppearanceSettings(),
            'translation_labels' => $this->getTranslationSettings(),
            'slides' => $this->getSlidesWithLang(),
            'dc_filemanager_api_url' => $dcFilemanagerApi,
            'dc_filemanager_base_url' => $dcFilemanagerBase,
            'current' => $configureUrl,
            'configure_url_ajax' => $configureUrl . (strpos($configureUrl, '?') !== false ? '&' : '?') . 'ajax=1&action=getSlide&id_dc_carousel_slide=',
            'token' => Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    protected function saveIntroSettings()
    {
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            Configuration::updateValue(
                'DC_CAROUSEL_INTRO_TITLE_' . $idLang,
                Tools::getValue('DC_CAROUSEL_INTRO_TITLE_' . $idLang)
            );
            Configuration::updateValue(
                'DC_CAROUSEL_INTRO_DESC_' . $idLang,
                Tools::getValue('DC_CAROUSEL_INTRO_DESC_' . $idLang)
            );
        }
    }

    protected function saveAppearanceSettings()
    {
        $keys = [
            'DC_CAROUSEL_INTRO_TITLE_FONT_SIZE',
            'DC_CAROUSEL_INTRO_TITLE_COLOR',
            'DC_CAROUSEL_INTRO_TITLE_WEIGHT',
            'DC_CAROUSEL_INTRO_TITLE_UPPERCASE',
            'DC_CAROUSEL_INTRO_DESC_FONT_SIZE',
            'DC_CAROUSEL_INTRO_DESC_COLOR',
            'DC_CAROUSEL_INTRO_DESC_WEIGHT',
            'DC_CAROUSEL_INTRO_DESC_UPPERCASE',
            'DC_CAROUSEL_ITEMS_DESKTOP',
            'DC_CAROUSEL_ITEMS_TABLET',
            'DC_CAROUSEL_ITEMS_MOBILE',
            'DC_CAROUSEL_SPEED',
            'DC_CAROUSEL_AUTOPLAY',
            'DC_CAROUSEL_BG_COLOR',
            'DC_CAROUSEL_NAV_BG',
            'DC_CAROUSEL_NAV_COLOR',
            'DC_CAROUSEL_NAV_BG_HOVER',
            'DC_CAROUSEL_NAV_COLOR_HOVER',
            'DC_CAROUSEL_IMG_RATIO',
            'DC_CAROUSEL_IMG_BORDER_COLOR',
            'DC_CAROUSEL_IMG_BORDER_WIDTH',
            'DC_CAROUSEL_LOAD_OWL',
        ];

        foreach ($keys as $key) {
            $value = $key === 'DC_CAROUSEL_IMG_BORDER_WIDTH'
                ? (int) Tools::getValue($key)
                : Tools::getValue($key);
            Configuration::updateValue($key, $value);
        }
    }

    protected function saveTranslationSettings()
    {
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            Configuration::updateValue(
                'DC_CAROUSEL_LINK_LABEL_' . $idLang,
                Tools::getValue('DC_CAROUSEL_LINK_LABEL_' . $idLang)
            );
        }
    }

    protected function saveImage()
    {
        $idSlide = (int) Tools::getValue('id_dc_carousel_slide');
        $image = Tools::getValue('dc_carousel_image');
        $link = Tools::getValue('dc_carousel_link');

        if (!$image) {
            return false;
        }

        if ($idSlide) {
            $result = Db::getInstance()->update(
                'dc_carousel_slide',
                [
                    'image' => pSQL($image),
                    'link' => pSQL($link),
                ],
                'id_dc_carousel_slide = ' . (int) $idSlide
            );
        } else {
            $position = (int) Db::getInstance()->getValue('SELECT IFNULL(MAX(position), 0) + 1 FROM `' . _DB_PREFIX_ . 'dc_carousel_slide`');
            $result = Db::getInstance()->insert('dc_carousel_slide', [
                'image' => pSQL($image),
                'link' => pSQL($link),
                'position' => $position,
                'active' => 1,
            ]);

            if ($result) {
                $idSlide = (int) Db::getInstance()->Insert_ID();
            }
        }

        if (!$result || !$idSlide) {
            return false;
        }

        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];

            $data = [
                'id_dc_carousel_slide' => $idSlide,
                'id_lang' => $idLang,
                'title' => pSQL(Tools::getValue('dc_carousel_title_' . $idLang)),
                'description' => Tools::getValue('dc_carousel_description_' . $idLang),
            ];

            $exists = (bool) Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'dc_carousel_slide_lang`
                 WHERE id_dc_carousel_slide = ' . (int) $idSlide . ' AND id_lang = ' . (int) $idLang
            );

            if ($exists) {
                Db::getInstance()->update(
                    'dc_carousel_slide_lang',
                    [
                        'title' => $data['title'],
                        'description' => $data['description'],
                    ],
                    'id_dc_carousel_slide = ' . (int) $idSlide . ' AND id_lang = ' . (int) $idLang
                );
            } else {
                Db::getInstance()->insert('dc_carousel_slide_lang', $data);
            }
        }

        return true;
    }

    protected function deleteImage($idSlide)
    {
        Db::getInstance()->delete('dc_carousel_slide_lang', 'id_dc_carousel_slide = ' . (int) $idSlide);

        return Db::getInstance()->delete('dc_carousel_slide', 'id_dc_carousel_slide = ' . (int) $idSlide);
    }

    protected function updatePositions(array $positions)
    {
        $position = 0;
        foreach ($positions as $idSlide) {
            Db::getInstance()->update(
                'dc_carousel_slide',
                ['position' => (int) $position],
                'id_dc_carousel_slide = ' . (int) $idSlide
            );
            $position++;
        }
    }

    protected function getIntroSettings()
    {
        $intro = [
            'titles' => [],
            'descriptions' => [],
        ];

        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $intro['titles'][$idLang] = Configuration::get('DC_CAROUSEL_INTRO_TITLE_' . $idLang);
            $intro['descriptions'][$idLang] = Configuration::get('DC_CAROUSEL_INTRO_DESC_' . $idLang);
        }

        return $intro;
    }

    protected function getAppearanceSettings()
    {
        $keys = [
            'DC_CAROUSEL_INTRO_TITLE_FONT_SIZE',
            'DC_CAROUSEL_INTRO_TITLE_COLOR',
            'DC_CAROUSEL_INTRO_TITLE_WEIGHT',
            'DC_CAROUSEL_INTRO_TITLE_UPPERCASE',
            'DC_CAROUSEL_INTRO_DESC_FONT_SIZE',
            'DC_CAROUSEL_INTRO_DESC_COLOR',
            'DC_CAROUSEL_INTRO_DESC_WEIGHT',
            'DC_CAROUSEL_INTRO_DESC_UPPERCASE',
            'DC_CAROUSEL_ITEMS_DESKTOP',
            'DC_CAROUSEL_ITEMS_TABLET',
            'DC_CAROUSEL_ITEMS_MOBILE',
            'DC_CAROUSEL_SPEED',
            'DC_CAROUSEL_AUTOPLAY',
            'DC_CAROUSEL_BG_COLOR',
            'DC_CAROUSEL_NAV_BG',
            'DC_CAROUSEL_NAV_COLOR',
            'DC_CAROUSEL_NAV_BG_HOVER',
            'DC_CAROUSEL_NAV_COLOR_HOVER',
            'DC_CAROUSEL_IMG_RATIO',
            'DC_CAROUSEL_IMG_BORDER_COLOR',
            'DC_CAROUSEL_IMG_BORDER_WIDTH',
        ];

        $appearance = [];
        foreach ($keys as $key) {
            $appearance[$key] = Configuration::get($key);
        }

        return $appearance;
    }

    protected function getTranslationSettings()
    {
        $labels = [];
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $labels[$idLang] = Configuration::get('DC_CAROUSEL_LINK_LABEL_' . $idLang);
        }

        return $labels;
    }

    protected function getSlidesWithLang()
    {
        $idLang = (int) $this->context->language->id;

        $sql = 'SELECT s.id_dc_carousel_slide, s.image, s.link, s.position, s.active,
                       sl.title, sl.description
                FROM `' . _DB_PREFIX_ . 'dc_carousel_slide` s
                LEFT JOIN `' . _DB_PREFIX_ . 'dc_carousel_slide_lang` sl
                    ON (s.id_dc_carousel_slide = sl.id_dc_carousel_slide AND sl.id_lang = ' . (int) $idLang . ')
                ORDER BY s.position ASC';

        return Db::getInstance()->executeS($sql);
    }

    protected function getSlideById($idSlide)
    {
        $idSlide = (int) $idSlide;
        if (!$idSlide) {
            return null;
        }

        $slide = Db::getInstance()->getRow(
            'SELECT id_dc_carousel_slide, image, link, position, active
             FROM `' . _DB_PREFIX_ . 'dc_carousel_slide`
             WHERE id_dc_carousel_slide = ' . $idSlide
        );

        if (!$slide) {
            return null;
        }

        $langs = Language::getLanguages(false);
        $slide['titles'] = [];
        $slide['descriptions'] = [];

        foreach ($langs as $lang) {
            $idLang = (int) $lang['id_lang'];
            $row = Db::getInstance()->getRow(
                'SELECT title, description
                 FROM `' . _DB_PREFIX_ . 'dc_carousel_slide_lang`
                 WHERE id_dc_carousel_slide = ' . $idSlide . ' AND id_lang = ' . $idLang
            );
            $slide['titles'][$idLang] = $row ? $row['title'] : '';
            $slide['descriptions'][$idLang] = $row ? $row['description'] : '';
        }

        return $slide;
    }

    public function hookDisplayHeader($params)
    {
        if ('index' !== $this->context->controller->php_self && !$this->isCached('views/templates/hook/dc_carousel.tpl', $this->getCacheId())) {
            // Allow usage on any page where hooked.
        }

        $this->context->controller->registerStylesheet(
            'dc_carousel_front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 50]
        );
        // GLightbox – lightbox dla obrazów w karuzeli
        $this->context->controller->registerStylesheet(
            'dc_carousel_glightbox',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
            ['server' => 'remote', 'priority' => 55]
        );
        $this->context->controller->registerJavascript(
            'dc_carousel_glightbox',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
            ['server' => 'remote', 'position' => 'bottom', 'priority' => 55]
        );
        // Opcjonalne ładowanie Owl Carousel z lokalnych plików modułu.
        // Używamy OGÓLNEGO identyfikatora 'owl-carousel' dla JS:
        // jeśli inny moduł również zarejestruje skrypt jako 'owl-carousel',
        // PrestaShop wygeneruje tylko jedno <script src="...">.
        if (Configuration::get('DC_CAROUSEL_LOAD_OWL')) {
            $this->context->controller->registerStylesheet(
                'owl-carousel-css',
                'modules/' . $this->name . '/views/css/owl.carousel.min.css',
                ['media' => 'all', 'priority' => 56]
            );
            $this->context->controller->registerJavascript(
                'owl-carousel',
                'modules/' . $this->name . '/views/js/owl.carousel.min.js',
                ['position' => 'bottom', 'priority' => 56]
            );
        }
        $this->context->controller->registerJavascript(
            'dc_carousel_front',
            'modules/' . $this->name . '/views/js/front.js',
            ['position' => 'bottom', 'priority' => 65]
        );
    }

    public function hookDisplayHome($params)
    {
        if (!$this->isCached('views/templates/hook/dc_carousel.tpl', $this->getCacheId())) {
            $slides = $this->getSlidesWithLang();

            $this->context->smarty->assign([
                'dc_carousel_intro' => $this->getIntroSettings(),
                'dc_carousel_appearance' => $this->getAppearanceSettings(),
                'dc_carousel_link_label' => Configuration::get('DC_CAROUSEL_LINK_LABEL_' . (int) $this->context->language->id),
                'dc_carousel_slides' => $slides,
                'dc_carousel_lang_id' => (int) $this->context->language->id,
                'dc_carousel_module_dir' => $this->_path,
            ]);
        }

        return $this->display(__FILE__, 'views/templates/hook/dc_carousel.tpl', $this->getCacheId());
    }
}

