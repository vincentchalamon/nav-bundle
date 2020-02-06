<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Event;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EasyAdminEvents
{
    public const POST_LIST_REQUEST_BUILDER = 'easy_admin.post_list_request_builder';
    public const POST_SEARCH_REQUEST_BUILDER = 'easy_admin.post_search_request_builder';
}
