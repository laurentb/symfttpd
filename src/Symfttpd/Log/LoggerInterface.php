<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Log;

/**
 * LoggerInterface description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface LoggerInterface
{
    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function emerg($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function alert($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function crit($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function err($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function warn($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function notice($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function info($message, array $context = array());

    /**
     * @param       $message
     * @param array $context
     *
     * @return boolean
     */
    public function debug($message, array $context = array());
}
