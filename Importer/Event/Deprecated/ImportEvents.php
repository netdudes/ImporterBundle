<?php
namespace Netdudes\ImporterBundle\Importer\Event\Deprecated;

use Netdudes\ImporterBundle\Importer\Event\ImportEvents as OneDotZeroImportEvents;

@trigger_error('Constant POST_INTERPRET in class Netdudes\ImporterBundle\Importer\Event\ImportEvents is deprecated since 1.0-dev and will be removed in 1.0. Use POST_BIND_DATA instead.', E_USER_DEPRECATED);

/**
 * @deprecated POST_INTERPRET is deprecated since version 1.0-dev, to be removed in 1.0. Use POST_BIND_DATA instead.
 */
final class ImportEvents
{
    const POST_INTERPRET = OneDotZeroImportEvents::POST_BIND_DATA;
}