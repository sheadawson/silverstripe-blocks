<?php

class ContentBlock extends Block
{
    /**
     * If the singular name is set in a private static $singular_name, it cannot be changed using the translation files
     * for some reason. Fix it by defining a method that handles the translation.
     * @return string
     */
    public function singular_name()
    {
        return _t('ContentBlock.SINGULARNAME', 'Content Block');
    }

    /**
     * If the plural name is set in a private static $plural_name, it cannot be changed using the translation files
     * for some reason. Fix it by defining a method that handles the translation.
     * @return string
     */
    public function plural_name()
    {
        return _t('ContentBlock.PLURALNAME', 'Content Blocks');
    }

    private static $db = array(
        'Content' => 'HTMLText',
    );
}
