<?php

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Event;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class EasyAdminEvents
{
    public const POST_LIST_REQUEST_BUILDER = 'easy_admin.post_list_request_builder';
    public const POST_SEARCH_REQUEST_BUILDER = 'easy_admin.post_search_request_builder';
}
