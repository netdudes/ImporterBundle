<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Netdudes\ImporterBundle\Importer\Event\Deprecated\ImportEvents as Deprecated;

final class ImportEvents
{
    const POST_BIND_DATA = 'EVENT_POST_BIND_DATA';

    /**
     * @deprecated Deprecated since version 1.0-dev, to be removed in 1.0. Use XXX instead.
     */
    const POST_INTERPRET = Deprecated::POST_INTERPRET;

    const POST_FIELD_INTERPRET = 'EVENT_POST_FIELD_INTERPRET';

    const PRE_BIND_DATA = 'EVENT_PRE_BIND_DATA';
}