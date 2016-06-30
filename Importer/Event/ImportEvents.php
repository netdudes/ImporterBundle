<?php
namespace Netdudes\ImporterBundle\Importer\Event;

final class ImportEvents
{
    const POST_BIND_DATA = 'EVENT_POST_BIND_DATA';

    const POST_FIELD_INTERPRET = 'EVENT_POST_FIELD_INTERPRET';

    const PRE_BIND_DATA = 'EVENT_PRE_BIND_DATA';

    const POST_VALIDATION = 'EVENT_POST_VALIDATION';
}