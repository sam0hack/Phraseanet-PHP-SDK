<?php

/*
 * This file is part of Phraseanet SDK.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhraseanetSDK\Recorder;

use PhraseanetSDK\ClientInterface;
use PhraseanetSDK\Recorder\Storage\StorageInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Player
{
    private $client;
    private $storage;

    public function __construct(ClientInterface $client, StorageInterface $storage)
    {
        $this->client = $client;
        $this->storage = $storage;
    }

    public function play(OutputInterface $output = null)
    {
        $data = $this->storage->fetch();

        foreach ($data as $request) {
            $this->output($output, sprintf(
                "--> Executing request %s %s", $request['method'], $request['path']
            ));

            $start = microtime(true);
            $this->client->call($request['method'], $request['path'], $request['query'], $request['post-fields']);
            $duration = microtime(true) - $start;

            $this->output($output, sprintf(
                "    Query took <comment>%f</comment>.\n",
                $duration
            ));
        }
    }

    private function output(OutputInterface $output = null, $message)
    {
        if (null !== $output) {
            $output->writeln($message);
        }
    }
}
