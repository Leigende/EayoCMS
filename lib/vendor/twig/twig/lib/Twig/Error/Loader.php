<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exception thrown when an error occurs during theme_url loading.
 *
 * Automatic theme_url information guessing is always turned off as
 * if a theme_url cannot be loaded, there is nothing to guess.
 * However, when a theme_url is loaded from another one, then, we need
 * to find the current context and this is automatically done by
 * Twig_Template::displayWithErrorHandling().
 *
 * This strategy makes Twig_Environment::resolveTemplate() much faster.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Twig_Error_Loader extends Twig_Error
{
    public function __construct($message, $lineno = -1, $filename = null, Exception $previous = null)
    {
        parent::__construct($message, false, false, $previous);
    }
}
