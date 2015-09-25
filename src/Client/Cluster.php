<?php
namespace Phloppy\Client;

use Phloppy\Exception;
use Phloppy\Exception\CommandException;
use Phloppy\Stream\StreamException;

/**
 * Disque cluster commands.
 *
 * @see http://redis.io/commands#cluster
 */
class Cluster extends AbstractClient {


    /**
     * Introduce the provided nodes to the connected Disque instance.
     *
     * @param string[] $streamUrls
     *
     * @return string[]
     * @see http://redis.io/commands/cluster-meet
     */
    public function meet(array $streamUrls)
    {
        $current = $this->stream->getStreamUrl();

        return array_filter($streamUrls, function($url) use ($current) {
            if ($url === $current) {
                return true;
            }

            $parts = parse_url($url);

            try {
                $response = $this->send(['CLUSTER', 'MEET', $parts['host'], (int) $parts['port']]);
                $this->log->debug('CLUSTER MEET', ['host' => $url, 'response' => $response]);
                return 'OK' === $response;
            } catch (Exception $e) {
                $this->log->error($e->getMessage(), array('url' => $url, 'exception' => $e));
                return false;
            }
        });
    }
}
