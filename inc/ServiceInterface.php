<?php
/**
 * @package mam-amber-util
 */

namespace Mam\AmberUtil;


interface ServiceInterface {

    /**
     * Register.
     * This function will run on every init.
     *
     */
    public function register();
}